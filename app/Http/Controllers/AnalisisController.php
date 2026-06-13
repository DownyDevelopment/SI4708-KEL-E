<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\EnvironmentalTracking;
use App\Models\Insentif;
use App\Models\MicroProgram;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;

class AnalisisController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'bulanan');
        $data = $this->buildReportData($period);
        $environmentalRecords = EnvironmentalTracking::orderByDesc('tanggal')->limit(10)->get();

        return view('admin.analisis', compact('data', 'period', 'environmentalRecords'));
    }

    public function storeEnvironmental(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenis_limbah' => 'required|string|max:255',
            'volume_kg' => 'required|numeric|min:0',
            'estimasi_emisi_berkurang_kg' => 'nullable|numeric|min:0',
        ]);

        EnvironmentalTracking::create([
            ...$validated,
            'estimasi_emisi_berkurang_kg' => $validated['estimasi_emisi_berkurang_kg'] ?? 0,
        ]);

        return redirect()
            ->route('admin.analisis')
            ->with('success', 'Data dampak lingkungan berhasil dicatat.');
    }

    public function exportPdf(Request $request)
    {
        $period = $request->query('period', 'bulanan');
        $data = $this->buildReportData($period);

        $html = view('admin.analisis-pdf', compact('data', 'period'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'laporan-dampak-program-' . now()->format('Y-m-d') . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildReportData(string $period): array
    {
        $totalWargaBekerja = Worker::count();

        $envQuery = EnvironmentalTracking::query();
        $this->applyPeriodFilter($envQuery, 'tanggal', $period);
        $totalDampak = (float) ($envQuery->sum('volume_kg') ?? 0);

        $insentifQuery = Insentif::query();
        $this->applyPeriodFilter($insentifQuery, 'created_at', $period);
        $totalInsentif = (float) ($insentifQuery->sum('jumlah_upah') ?? 0);

        $workers = Worker::query()->whereNotNull('created_at')->get();
        $trenPartisipasi = $this->groupByPeriod(
            $workers,
            fn ($w) => $w->created_at,
            $period
        )->map(fn ($group, $key) => (object) [
            'bulan' => $key,
            'partisipasi' => $group->count(),
        ])->sortBy('bulan')->values();

        $formattedTren = $trenPartisipasi->map(fn ($item) => [
            'bulan' => $this->formatPeriodLabel($item->bulan, $period),
            'partisipasi' => (int) $item->partisipasi,
        ]);

        $sebaranProgram = MicroProgram::select('jenis_program as name', DB::raw('COUNT(id) as value'))
            ->groupBy('jenis_program')
            ->get();

        $rincianCapaian = MicroProgram::orderBy('created_at', 'desc')->get();

        return [
            'total_warga_bekerja' => $totalWargaBekerja,
            'dampak_lingkungan' => [
                'value' => $totalDampak,
                'unit' => 'Kg Kompos',
            ],
            'total_insentif' => $totalInsentif,
            'tren_partisipasi' => $formattedTren,
            'sebaran_program' => $sebaranProgram,
            'rincian_capaian' => $rincianCapaian,
        ];
    }

    private function applyPeriodFilter($query, string $column, string $period): void
    {
        if ($period === 'mingguan') {
            $query->where($column, '>=', now()->subWeeks(8)->startOfWeek());
        } elseif ($period === 'tahunan') {
            $query->where($column, '>=', now()->subYears(3)->startOfYear());
        } else {
            $query->where($column, '>=', now()->subMonths(12)->startOfMonth());
        }
    }

    private function groupByPeriod($collection, callable $dateResolver, string $period)
    {
        return $collection->groupBy(function ($item) use ($dateResolver, $period) {
            $date = $dateResolver($item);
            if (!$date) {
                return 'unknown';
            }
            return match ($period) {
                'mingguan' => $date->format('Y-\\WW'),
                'tahunan' => $date->format('Y'),
                default => $date->format('Y-m'),
            };
        })->forget('unknown');
    }

    private function formatPeriodLabel(string $key, string $period): string
    {
        if ($period === 'tahunan') {
            return $key;
        }
        if ($period === 'mingguan') {
            return str_replace(['Y-', 'W'], ['', ' Minggu '], $key);
        }
        $parts = explode('-', $key);
        if (count($parts) !== 2) {
            return $key;
        }
        $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return ($months[(int) $parts[1]] ?? $parts[1]) . ' ' . $parts[0];
    }
}
