<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkSchedule;

class JadwalController extends Controller
{
    public function index()
    {
        // For admin /tugas
        $jadwal = WorkSchedule::orderBy('created_at', 'desc')->get();
        return view('admin.tugas', compact('jadwal'));
    }

    public function pengawasIndex()
    {
        // For pengawas /jadwal
        $jadwal = WorkSchedule::orderBy('created_at', 'desc')->get();
        return view('pengawas.jadwal', compact('jadwal'));
    }
}
