<?php

namespace App\Http\Controllers;

use App\Models\Insentif;
use App\Models\Reward;
use Illuminate\Http\Request;

class InsentifController extends Controller
{
    public function index()
    {
        $insentifs = Insentif::with(['worker', 'logbook.schedule.program'])
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $rewards = Reward::with('worker')
            ->orderByDesc('tanggal_pemberian')
            ->orderByDesc('created_at')
            ->get();

        $totalUpah = $insentifs->sum('jumlah_upah');

        return view('admin.insentif', compact('insentifs', 'rewards', 'totalUpah'));
    }
}
