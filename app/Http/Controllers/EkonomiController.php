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
        $workers = Worker::all();
        $pendingLogbooks = collect();

        if (auth()->user()->role === 'admin') {
            $pendingLogbooks = Logbook::with(['worker', 'schedule.program'])
                ->where('progres_persentase', '>=', 100)
                ->where('status_validasi', 'menunggu')
                ->orderByDesc('created_at')
                ->get();
        }

        return view('admin.ekonomi', [
            'workers' => $workers,
            'pendingLogbooks' => $pendingLogbooks,
            'defaultUpah' => (int) SystemSetting::get('upah_default_logbook', 50000),
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
