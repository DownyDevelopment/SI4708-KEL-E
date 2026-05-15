<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkSchedule;
use App\Models\Worker;
use App\Models\Logbook;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function index()
    {
        $schedules = WorkSchedule::leftJoin('micro_programs', 'work_schedules.program_id', '=', 'micro_programs.id')
            ->select('work_schedules.*', 'micro_programs.nama_program as tugas')
            ->orderBy('work_schedules.created_at', 'desc')
            ->get();
        $workers = Worker::all();
        return view('pengawas.logbook', compact('schedules', 'workers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:work_schedules,id',
            'progres_persentase' => 'required|numeric|min:0|max:100',
            'lokasi_pekerjaan' => 'required|string',
            'pekerja_terlibat' => 'required|string', // JSON
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $path = $request->file('foto')->store('logbooks', 'public');

        Logbook::create([
            'schedule_id' => $request->schedule_id,
            'pengawas_id' => Auth::id(),
            'progres_persentase' => $request->progres_persentase,
            'catatan' => $request->catatan,
            'lokasi_pekerjaan' => $request->lokasi_pekerjaan,
            'pekerja_terlibat' => $request->pekerja_terlibat,
            'foto_bukti_url' => '/storage/' . $path
        ]);

        return redirect()->back()->with('success', 'Bukti kerja berhasil disimpan!');
    }
}
