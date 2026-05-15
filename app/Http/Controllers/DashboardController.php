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
        // To be implemented later, for now just render view
        return view('pengawas.dashboard');
    }
}
