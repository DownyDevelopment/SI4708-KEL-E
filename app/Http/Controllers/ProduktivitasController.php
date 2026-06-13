<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\WorkSchedule;

class ProduktivitasController extends Controller
{
    public function index()
    {
        $schedules = WorkSchedule::query()
            ->whereNotNull('tanggal')
            ->get();

        $logbooks = Logbook::with('schedule')->get();

        $resolvePeriod = function ($dateValue) {
            if (!$dateValue) {
                return null;
            }

            return \Carbon\Carbon::parse($dateValue)->format('Y-m');
        };

        $periodKeys = $schedules->pluck('tanggal')
            ->merge($logbooks->map(fn (Logbook $logbook) => $logbook->tanggal ?? $logbook->schedule?->tanggal ?? $logbook->created_at))
            ->filter()
            ->map($resolvePeriod)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $formattedData = $periodKeys->map(function ($periode) use ($schedules, $logbooks, $resolvePeriod) {
            $monthSchedules = $schedules->filter(
                fn ($s) => $s->tanggal && $resolvePeriod($s->tanggal) === $periode
            );
            $monthLogbooks = $logbooks->filter(function (Logbook $logbook) use ($periode, $resolvePeriod) {
                $date = $logbook->tanggal ?? $logbook->schedule?->tanggal ?? $logbook->created_at;

                return $date && $resolvePeriod($date) === $periode;
            });

            $rencana = $monthSchedules->count();

            $berjalan = $monthLogbooks->where('progres_persentase', '<', 100)->count()
                + $monthSchedules->whereIn('status', ['in_progress', 'active', 'ongoing'])->count();

            $selesai = $monthLogbooks->where('progres_persentase', '>=', 100)->count()
                + $monthSchedules->whereIn('status', ['completed', 'selesai'])->count();

            $parts = explode('-', $periode);
            $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            return [
                'name' => ($months[(int) $parts[1]] ?? $parts[1]) . ' ' . $parts[0],
                'PekerjaanRencana' => (int) $rencana,
                'PekerjaanBerjalan' => (int) $berjalan,
                'PekerjaanSelesai' => (int) $selesai,
            ];
        });

        if ($formattedData->isEmpty()) {
            $formattedData = collect([[
                'name' => now()->format('M Y'),
                'PekerjaanRencana' => 0,
                'PekerjaanBerjalan' => 0,
                'PekerjaanSelesai' => 0,
            ]]);
        }

        return view('admin.produktivitas', ['data' => $formattedData]);
    }
}
