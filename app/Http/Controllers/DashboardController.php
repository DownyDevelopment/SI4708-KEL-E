<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\WorkSchedule;
use App\Models\Inventaris;
use App\Models\MicroProgram;
use App\Models\Household;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        // Profiling stats
        $totalProfiling = Worker::count();
        $petani = Worker::where('kemampuan_utama', 'Bertani')->count();
        $pembersih = Worker::where('kemampuan_utama', 'Membersihkan')->count();
        $pengrajin = Worker::where('kemampuan_utama', 'Kerajinan')->count();

        // Tugas Mingguan (using work_schedules)
        // Assume weekly is all time for now to match old code
        $totalTugas = WorkSchedule::count();
        $aktif = WorkSchedule::whereIn('status', ['active', 'in_progress'])->count();
        $terjadwal = WorkSchedule::where('status', 'scheduled')->count();
        $selesai = WorkSchedule::whereIn('status', ['completed', 'selesai'])->count();

        // Dampak Produksi
        $dampak = Inventaris::all();

        // Area Kerja
        $area = MicroProgram::all();

        $data = [
            'profiling' => [
                'total' => $totalProfiling,
                'petani' => $petani,
                'pembersih' => $pembersih,
                'pengrajin' => $pengrajin,
            ],
            'tugas' => [
                'total' => $totalTugas,
                'aktif' => $aktif,
                'terjadwal' => $terjadwal,
                'selesai' => $selesai,
            ],
            'dampak' => $dampak,
            'area' => $area,
        ];

        return view('admin.dashboard', compact('data'));
    }

    public function pengawasDashboard()
    {
        $today = \Carbon\Carbon::today();

        $todaySchedules = \App\Models\WorkSchedule::whereDate('tanggal', $today)->count();
        $pendingLogbooks = \App\Models\WorkSchedule::whereDate('tanggal', $today)
            ->whereNotIn('id', function($query) {
                $query->select('schedule_id')->from('logbooks');
            })->count();
        $reportedProblems = \App\Models\FieldProblem::whereDate('tanggal', $today)->count();

        $schedules = \App\Models\WorkSchedule::leftJoin('logbooks', 'work_schedules.id', '=', 'logbooks.schedule_id')
            ->leftJoin('micro_programs', 'work_schedules.program_id', '=', 'micro_programs.id')
            ->select('work_schedules.*', 'logbooks.id as logbook_id', 'logbooks.progres_persentase', 'micro_programs.nama_program as tugas')
            ->whereDate('work_schedules.tanggal', $today)
            ->get();

        $stats = [
            'todaySchedules' => $todaySchedules,
            'pendingLogbooks' => $pendingLogbooks,
            'reportedProblems' => $reportedProblems,
        ];

        return view('pengawas.dashboard', compact('stats', 'schedules'));
    }
}
