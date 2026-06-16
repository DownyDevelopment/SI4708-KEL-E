<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\Insentif;
use App\Models\Reward;
use App\Models\Logbook;
use App\Models\ScheduleAssignment;
use App\Models\SystemSetting;

class EkonomiController extends Controller
{
    public function index()
    {
        $workers = Worker::orderBy('nama')->get();
        $pendingLogbooks = collect();
        $now = now();
        $reports = collect();

        if (auth()->user()->role === 'admin') {
            $pendingLogbooks = Logbook::with(['worker', 'schedule.program'])
                ->where('progres_persentase', '>=', 100)
                ->where('status_validasi', 'menunggu')
                ->orderByDesc('created_at')
                ->get();
        } else {
            $reports = \App\Models\FieldProblem::join('users', 'field_problems.pengawas_id', '=', 'users.id')
                ->select('field_problems.*', 'users.nama as nama_pengawas')
                ->orderBy('field_problems.created_at', 'desc')
                ->get();
        }

        $totalInsentifBulan = (float) Insentif::whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->sum('jumlah_upah');

        $entriBulan = Insentif::whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->count();

        $pekerjaDibayarBulan = Insentif::whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->distinct('worker_id')
            ->count('worker_id');

        $totalRewards = Reward::count();
        $totalInsentifAll = (float) Insentif::sum('jumlah_upah');

        $jenisStats = Insentif::whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->selectRaw('jenis_insentif, COUNT(*) as jumlah, SUM(jumlah_upah) as total')
            ->groupBy('jenis_insentif')
            ->orderByDesc('total')
            ->get();

        $monthLabels = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $trenBulanan = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $trenBulanan->push([
                'bulan' => $monthLabels[$date->month] . ' ' . $date->format('y'),
                'total' => (float) Insentif::whereYear('tanggal', $date->year)
                    ->whereMonth('tanggal', $date->month)
                    ->sum('jumlah_upah'),
            ]);
        }

        $recentInsentifs = Insentif::with('worker')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $allInsentifs = Insentif::with(['worker', 'logbook.schedule.program'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $allRewards = Reward::with('worker')
            ->orderByDesc('tanggal_pemberian')
            ->orderByDesc('id')
            ->get();

        // 1. Hitung total penjualan dari inventaris_histories
        $totalSales = 0;
        $histories = \App\Models\InventarisHistory::where('tipe_perubahan', 'kurang')
            ->where('keterangan', 'like', '%Penjualan%')
            ->get();

        foreach ($histories as $h) {
            if (preg_match('/Total:\s*Rp[^\d]*([\d\.,]+)/u', $h->keterangan, $matches)) {
                $cleaned = str_replace('.', '', $matches[1]);
                $cleaned = str_replace(',', '.', $cleaned);
                $totalSales += (float)$cleaned;
            }
        }

        // 2. Hitung total yang sudah dicairkan ke PADes
        $totalDisbursed = \App\Models\PadesPencairan::sum('nominal');

        // 3. Saldo siap cair
        $availableBalance = max(0, $totalSales - $totalDisbursed);

        // 4. Riwayat pencairan PADes terurut dari yang terbaru
        $pencairans = \App\Models\PadesPencairan::orderByDesc('tanggal_pencairan')
            ->orderByDesc('id')
            ->get();

        return view('admin.ekonomi', [
            'workers' => $workers,
            'pendingLogbooks' => $pendingLogbooks,
            'defaultUpah' => (int) SystemSetting::get('upah_default_logbook', 50000),
            'totalInsentifBulan' => $totalInsentifBulan,
            'entriBulan' => $entriBulan,
            'pekerjaDibayarBulan' => $pekerjaDibayarBulan,
            'totalRewards' => $totalRewards,
            'totalInsentifAll' => $totalInsentifAll,
            'jenisStats' => $jenisStats,
            'trenBulanan' => $trenBulanan,
            'recentInsentifs' => $recentInsentifs,
            'allInsentifs' => $allInsentifs,
            'allRewards' => $allRewards,
            'bulanLabel' => $monthLabels[$now->month] . ' ' . $now->year,
            'totalSales' => $totalSales,
            'totalDisbursed' => $totalDisbursed,
            'availableBalance' => $availableBalance,
            'pencairans' => $pencairans,
            'reports' => $reports,
        ]);
    }

    public function detail(Request $request, $workerId)
    {
        $tahun = (int) $request->query('tahun', date('Y'));
        $bulan = (int) $request->query('bulan', date('n'));

        $worker = Worker::with('household.workers')->findOrFail($workerId);

        $insentif = Insentif::with(['logbook.schedule.program'])
            ->where('worker_id', $workerId)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get();

        $totalUpah = $insentif->sum('jumlah_upah');

        $perJenis = $insentif->groupBy('jenis_insentif')->map(function ($group) {
            return [
                'jenis_insentif' => $group->first()->jenis_insentif,
                'subtotal' => $group->sum('jumlah_upah'),
            ];
        })->values();

        $householdWorkerIds = $worker->household
            ? $worker->household->workers->pluck('id')
            : collect([$worker->id]);

        $insentifKeluarga = Insentif::with(['worker', 'logbook.schedule.program'])
            ->whereIn('worker_id', $householdWorkerIds)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get();

        $totalInsentifKeluarga = $insentifKeluarga->sum('jumlah_upah');
        $pendapatanKeluargaDasar = (float) ($worker->household?->pendapatan_per_bulan ?? 0);

        $perProgram = $insentifKeluarga
            ->groupBy(function (Insentif $item) {
                return $item->logbook?->schedule?->program?->nama_program ?? 'Lainnya / Manual';
            })
            ->map(function ($group, $programName) {
                return [
                    'program' => $programName,
                    'subtotal' => $group->sum('jumlah_upah'),
                    'jumlah_entri' => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('subtotal')
            ->values();

        $akumulasi = [
            'total_upah' => $totalUpah,
            'jumlah_entri' => $insentif->count(),
            'periode' => ['label' => "$bulan/$tahun"],
            'per_jenis' => $perJenis,
            'pendapatan_keluarga_dasar' => $pendapatanKeluargaDasar,
            'total_insentif_keluarga' => $totalInsentifKeluarga,
            'total_keluarga_lintas_program' => $pendapatanKeluargaDasar + $totalInsentifKeluarga,
            'per_program' => $perProgram,
            'anggota_keluarga' => $worker->household?->workers->pluck('nama')->values() ?? collect(),
        ];

        $riwayat = Insentif::where('worker_id', $workerId)->orderBy('tanggal', 'desc')->get();
        $rewards = Reward::where('worker_id', $workerId)->orderBy('tanggal_pemberian', 'desc')->get();

        return response()->json([
            'akumulasi' => $akumulasi,
            'riwayat' => $riwayat,
            'rewards' => $rewards,
        ]);
    }

    public function storeInsentif(Request $request)
    {
        $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'tanggal' => 'required|date',
            'jumlah_upah' => 'required|numeric',
            'jenis_insentif' => 'required|string',
        ]);

        Insentif::create($request->only([
            'worker_id', 'tanggal', 'jumlah_upah', 'jenis_insentif', 'keterangan',
        ]));

        Worker::where('id', $request->worker_id)->increment('total_pendapatan', (float) $request->jumlah_upah);

        return redirect()->back()->with('success', 'Insentif / upah berhasil dicatat.');
    }

    public function storeReward(Request $request)
    {
        $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'nama_penghargaan' => 'required|string',
            'tanggal_pemberian' => 'required|date',
        ]);

        Reward::create($request->only([
            'worker_id', 'nama_penghargaan', 'tanggal_pemberian',
        ]));
        return redirect()->back()->with('success', 'Penghargaan berhasil dicatat.');
    }

    public function validateLogbook(Request $request, int $id)
    {
        $request->validate([
            'action' => 'required|in:disetujui,ditolak',
            'jumlah_upah' => 'nullable|numeric|min:0',
        ]);

        $logbook = Logbook::with(['worker', 'workerGroup.workers', 'schedule.program'])->findOrFail($id);

        if ($logbook->status_validasi !== 'menunggu') {
            return redirect()->back()->with('error', 'Logbook ini sudah divalidasi sebelumnya.');
        }

        if ($request->action === 'ditolak') {
            $logbook->update(['status_validasi' => 'ditolak']);
            return redirect()->back()->with('success', 'Hasil kerja ditolak. Upah tidak dicairkan.');
        }

        $fotoSebelum = $logbook->foto_sebelum;
        $fotoSesudah = $logbook->foto_sesudah ?? $logbook->foto_bukti;
        if (!$fotoSebelum || !$fotoSesudah) {
            return redirect()->back()->with('error', 'Validasi membutuhkan foto sebelum dan sesudah pekerjaan.');
        }

        if (Insentif::where('logbook_id', $logbook->id)->exists()) {
            $logbook->update(['status_validasi' => 'disetujui']);
            return redirect()->back()->with('success', 'Logbook sudah memiliki pencairan upah.');
        }

        $workerIds = collect();

        if ($logbook->worker_id) {
            $workerIds->push($logbook->worker_id);
        } elseif ($logbook->worker_group_id) {
            $workerIds = $logbook->workerGroup?->workers->pluck('id') ?? collect();
        } elseif ($logbook->schedule_id) {
            $assignment = ScheduleAssignment::where('schedule_id', $logbook->schedule_id)->first();
            if ($assignment?->worker_id) {
                $workerIds->push($assignment->worker_id);
            }
        }

        if ($workerIds->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada pekerja terkait untuk pencairan upah.');
        }

        $programName = $logbook->schedule?->program?->nama_program ?? 'Program';
        $jumlahUpah = (float) ($request->jumlah_upah ?? (int) SystemSetting::get('upah_default_logbook', 50000));
        $jumlahPerPekerja = round($jumlahUpah / $workerIds->count(), 2);

        foreach ($workerIds as $workerId) {
            Insentif::create([
                'worker_id' => $workerId,
                'logbook_id' => $logbook->id,
                'tanggal' => now()->toDateString(),
                'jumlah_upah' => $jumlahPerPekerja,
                'jenis_insentif' => 'Upah Harian',
                'keterangan' => "Pencairan otomatis setelah validasi logbook #{$logbook->id} — {$programName}",
            ]);

            Worker::where('id', $workerId)->increment('total_pendapatan', $jumlahPerPekerja);
        }

        $logbook->update(['status_validasi' => 'disetujui']);

        $pesanPencairan = $workerIds->count() > 1
            ? "Validasi disetujui. Upah dibagi ke {$workerIds->count()} anggota kelompok."
            : 'Validasi disetujui. Upah pekerja berhasil dicatat.';

        return redirect()->back()->with('success', $pesanPencairan);
    }
}
