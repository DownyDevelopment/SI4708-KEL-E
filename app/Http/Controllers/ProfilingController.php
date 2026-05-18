<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;

class ProfilingController extends Controller
{
    public function index()
    {
        // Get all workers with their households, sum of insentifs, and count of insentifs
        $workers = Worker::with('household')
            ->withSum('insentifs', 'jumlah_upah')
            ->withCount('insentifs')
            ->get();

        $totalWorkers = $workers->count();
        $miskinCount = 0;
        $pekerjaanMakroStats = [];
        $kesejahteraanStats = [];

        foreach ($workers as $worker) {
            // Count Miskin
            if ($worker->is_miskin) {
                $miskinCount++;
            }

            // Aggregate Pekerjaan Makro
            $makro = $worker->pekerjaan_makro;
            if (!isset($pekerjaanMakroStats[$makro])) {
                $pekerjaanMakroStats[$makro] = 0;
            }
            $pekerjaanMakroStats[$makro]++;

            // Aggregate Kesejahteraan
            $kesejahteraan = $worker->klasifikasi_kesejahteraan;
            if (!isset($kesejahteraanStats[$kesejahteraan])) {
                $kesejahteraanStats[$kesejahteraan] = 0;
            }
            $kesejahteraanStats[$kesejahteraan]++;
        }

        $persentaseMiskin = $totalWorkers > 0 ? round(($miskinCount / $totalWorkers) * 100, 1) : 0;

        return view('pengawas.profiling.index', compact(
            'workers',
            'totalWorkers',
            'miskinCount',
            'persentaseMiskin',
            'pekerjaanMakroStats',
            'kesejahteraanStats'
        ));
    }
}
