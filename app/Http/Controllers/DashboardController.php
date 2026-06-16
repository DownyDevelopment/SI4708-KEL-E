<?php

namespace App\Http\Controllers;

use App\Models\FieldProblem;
use App\Models\Household;
use App\Models\Inventaris;
use App\Models\MicroProgram;
use App\Models\Worker;
use App\Models\WorkSchedule;
use App\Support\BidangKerjaHelper;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        $countBySkill = function (array $keywords): int {
            $query = Worker::query();
            foreach ($keywords as $index => $keyword) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $query->{$method}('kemampuan_utama', 'like', '%' . $keyword . '%');
            }

            return $query->count();
        };

        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weeklySchedules = WorkSchedule::whereBetween('tanggal', [$weekStart, $weekEnd]);

        $data = [
            'profiling' => [
                'total' => Worker::count(),
                'petani' => $countBySkill(['Bertani', 'Petani', 'Kebun', 'Tani']),
                'pembersih' => $countBySkill(['Membersihkan', 'Pembersih', 'Sampah', 'Lingkungan']),
                'pengrajin' => $countBySkill(['Kerajinan', 'Pengrajin', 'Rajut']),
            ],
            'tugas' => [
                'total' => (clone $weeklySchedules)->count(),
                'aktif' => (clone $weeklySchedules)->whereIn('status', ['active', 'in_progress'])->count(),
                'terjadwal' => (clone $weeklySchedules)->where('status', 'scheduled')->count(),
                'selesai' => (clone $weeklySchedules)->whereIn('status', ['completed', 'selesai'])->count(),
                'periode_label' => $weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y'),
            ],
            'dampak' => Inventaris::all(),
            'area' => MicroProgram::all(),
            'bidang_kerja' => BidangKerjaHelper::chartData(),
        ];

        return view('admin.dashboard', compact('data'));
    }

    public function pengawasDashboard()
    {
        $today = Carbon::today();

        $todaySchedules = WorkSchedule::whereDate('tanggal', $today)->count();
        $pendingLogbooks = WorkSchedule::whereDate('tanggal', $today)
            ->whereNotIn('id', function ($query) {
                $query->select('schedule_id')->from('logbooks');
            })->count();
        $reportedProblems = FieldProblem::whereDate('tanggal', $today)->count();

        $schedules = WorkSchedule::leftJoin('logbooks', 'work_schedules.id', '=', 'logbooks.schedule_id')
            ->leftJoin('micro_programs', 'work_schedules.program_id', '=', 'micro_programs.id')
            ->select(
                'work_schedules.*',
                'logbooks.id as logbook_id',
                'logbooks.progres_persentase',
                'micro_programs.nama_program as tugas'
            )
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
