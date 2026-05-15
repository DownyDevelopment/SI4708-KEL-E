<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MicroProgram;
use Illuminate\Support\Facades\DB;

class ProduktivitasController extends Controller
{
    public function index()
    {
        // Get trend data per month
        $trends = MicroProgram::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as periode'),
            DB::raw('SUM(CASE WHEN status = "planned" THEN 1 ELSE 0 END) as rencana'),
            DB::raw('SUM(CASE WHEN status IN ("active", "ongoing", "in_progress") THEN 1 ELSE 0 END) as berjalan'),
            DB::raw('SUM(CASE WHEN status IN ("completed", "selesai") THEN 1 ELSE 0 END) as selesai')
        )
        ->groupBy('periode')
        ->orderBy('periode', 'asc')
        ->get();

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
