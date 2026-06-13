<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\Insentif;
use App\Models\Reward;
use App\Models\Logbook;
use App\Models\ScheduleAssignment;

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

        return view('admin.ekonomi', compact('workers', 'pendingLogbooks'));
    }

    public function detail(Request $request, $workerId)
    {
        $tahun = $request->query('tahun', date('Y'));
        $bulan = $request->query('bulan', date('n'));

        $insentif = Insentif::where('worker_id', $workerId)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get();

        $totalUpah = $insentif->sum('jumlah_upah');
        
        $perJenis = $insentif->groupBy('jenis_insentif')->map(function ($group) {
            return [
                'jenis_insentif' => $group->first()->jenis_insentif,
                'subtotal' => $group->sum('jumlah_upah')
            ];
        })->values();

        $akumulasi = [
            'total_upah' => $totalUpah,
            'jumlah_entri' => $insentif->count(),
            'periode' => ['label' => "$bulan/$tahun"],
            'per_jenis' => $perJenis
        ];

        $riwayat = Insentif::where('worker_id', $workerId)->orderBy('tanggal', 'desc')->get();
        $rewards = Reward::where('worker_id', $workerId)->orderBy('tanggal_pemberian', 'desc')->get();

        return response()->json([
            'akumulasi' => $akumulasi,
            'riwayat' => $riwayat,
            'rewards' => $rewards
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

        $logbook = Logbook::with(['worker', 'schedule.program'])->findOrFail($id);

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

        $workerId = $logbook->worker_id;
        if (!$workerId && $logbook->schedule_id) {
            $assignment = ScheduleAssignment::where('schedule_id', $logbook->schedule_id)->first();
            $workerId = $assignment?->worker_id;
        }

        if (!$workerId) {
            return redirect()->back()->with('error', 'Tidak ada pekerja terkait untuk pencairan upah.');
        }

        $programName = $logbook->schedule?->program?->nama_program ?? 'Program';
        $jumlahUpah = $request->jumlah_upah ?? 50000;

        Insentif::create([
            'worker_id' => $workerId,
            'logbook_id' => $logbook->id,
            'tanggal' => now()->toDateString(),
            'jumlah_upah' => $jumlahUpah,
            'jenis_insentif' => 'Upah Harian',
            'keterangan' => "Pencairan otomatis setelah validasi logbook #{$logbook->id} — {$programName}",
        ]);

        $logbook->update(['status_validasi' => 'disetujui']);

        return redirect()->back()->with('success', 'Validasi disetujui. Upah pekerja berhasil dicatat.');
    }
}
