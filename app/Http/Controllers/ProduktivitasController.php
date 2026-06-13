<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MicroProgram;

class ProduktivitasController extends Controller
{
    public function index()
    {
        // Tren per bulan — grouping di PHP agar kompatibel SQLite & MySQL
        $statusRencana = ['planned'];
        $statusBerjalan = ['active', 'ongoing', 'in_progress'];
        $statusSelesai = ['completed', 'selesai'];

        $trends = MicroProgram::query()
            ->whereNotNull('created_at')
            ->get()
            ->groupBy(fn ($program) => $program->created_at->format('Y-m'))
            ->map(function ($group, $periode) use ($statusRencana, $statusBerjalan, $statusSelesai) {
                return (object) [
                    'periode' => $periode,
                    'rencana' => $group->whereIn('status', $statusRencana)->count(),
                    'berjalan' => $group->whereIn('status', $statusBerjalan)->count(),
                    'selesai' => $group->whereIn('status', $statusSelesai)->count(),
                ];
            })
            ->sortBy('periode')
            ->values();

        // Ensure we format it correctly for the frontend
        $formattedData = $trends->map(function ($item) {
            $parts = explode('-', $item->periode);
            $monthNum = (int)$parts[1];
            $year = $parts[0];
            
            $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $monthName = $months[$monthNum];
            
            return [
                'name' => "$monthName $year",
                'PekerjaanRencana' => (int)$item->rencana,
                'PekerjaanBerjalan' => (int)$item->berjalan,
                'PekerjaanSelesai' => (int)$item->selesai
            ];
        });

        return view('admin.produktivitas', ['data' => $formattedData]);
    }
}
