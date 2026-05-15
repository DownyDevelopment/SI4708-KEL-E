<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkSchedule;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwal = WorkSchedule::leftJoin('micro_programs', 'work_schedules.program_id', '=', 'micro_programs.id')
            ->select('work_schedules.*', 'micro_programs.nama_program as tugas')
            ->orderBy('work_schedules.created_at', 'desc')
            ->get();
        return view('admin.tugas', compact('jadwal'));
    }

    public function pengawasIndex()
    {
        $jadwal = WorkSchedule::leftJoin('micro_programs', 'work_schedules.program_id', '=', 'micro_programs.id')
            ->select('work_schedules.*', 'micro_programs.nama_program as tugas')
            ->orderBy('work_schedules.created_at', 'desc')
            ->get();
        return view('pengawas.jadwal', compact('jadwal'));
    }
}
