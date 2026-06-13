<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\EnvironmentalTracking;
use App\Models\Insentif;
use App\Models\MicroProgram;
use Illuminate\Support\Facades\DB;

class AnalisisController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'bulanan');

        // Total Pekerja Aktif
        $totalWargaBekerja = Worker::count();

        // Dampak Lingkungan
        $dampakLingkungan = EnvironmentalTracking::select(DB::raw('SUM(volume_kg) as total'))
            ->first();
        $totalDampak = $dampakLingkungan->total ?? 0;

        // Total Insentif
        $totalInsentif = Insentif::select(DB::raw('SUM(jumlah_upah) as total'))
            ->first();
        $totalInsentif = $totalInsentif->total ?? 0;

        // Tren Partisipasi (Bulanan) — grouping di PHP agar kompatibel SQLite & MySQL
        $trenPartisipasi = Worker::query()
            ->whereNotNull('created_at')
            ->get()
            ->groupBy(fn ($worker) => $worker->created_at->format('Y-m'))
            ->map(fn ($group, $bulan) => (object) [
                'bulan' => $bulan,
                'partisipasi' => $group->count(),
            ])
            ->sortBy('bulan')
            ->values();
            
        $formattedTren = $trenPartisipasi->map(function ($item) {
            $parts = explode('-', $item->bulan);
            $monthNum = (int)$parts[1];
            $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return [
                'bulan' => $months[$monthNum] . ' ' . $parts[0],
                'partisipasi' => (int)$item->partisipasi
            ];
        });

        // Sebaran Program
        $sebaranProgram = MicroProgram::select('jenis_program as name', DB::raw('COUNT(id) as value'))
            ->groupBy('jenis_program')
            ->get();

        // Rincian Capaian
        $rincianCapaian = MicroProgram::orderBy('created_at', 'desc')->get();

        $data = [
            'total_warga_bekerja' => $totalWargaBekerja,
            'dampak_lingkungan' => [
                'value' => $totalDampak,
                'unit' => 'Kg Kompos'
            ],
            'total_insentif' => $totalInsentif,
            'tren_partisipasi' => $formattedTren,
            'sebaran_program' => $sebaranProgram,
            'rincian_capaian' => $rincianCapaian
        ];

        return view('admin.analisis', compact('data', 'period'));
    }
}
