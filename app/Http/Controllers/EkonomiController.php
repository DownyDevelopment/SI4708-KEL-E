<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\Insentif;
use App\Models\Reward;
use Illuminate\Support\Facades\DB;

class EkonomiController extends Controller
{
    public function index()
    {
        $workers = Worker::all();
        return view('admin.ekonomi', compact('workers'));
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
}
