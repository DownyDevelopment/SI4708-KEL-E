<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\EnvironmentalTracking;
use App\Models\Insentif;
use App\Models\MicroProgram;
use Illuminate\Support\Facades\DB;

class AnalisisController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'bulanan');

        // Total Pekerja Aktif
        $totalWargaBekerja = Worker::where('status', 'aktif')->count();

        // Dampak Lingkungan
        $dampakLingkungan = EnvironmentalTracking::select(DB::raw('SUM(volume) as total'))
            ->first();
        $totalDampak = $dampakLingkungan->total ?? 0;

        // Total Insentif
        $totalInsentif = Insentif::select(DB::raw('SUM(jumlah_insentif) as total'))
            ->first();
        $totalInsentif = $totalInsentif->total ?? 0;

        // Tren Partisipasi (Bulanan)
        $trenPartisipasi = Worker::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as bulan'), DB::raw('COUNT(id) as partisipasi'))
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();
            
        $formattedTren = $trenPartisipasi->map(function ($item) {
            $parts = explode('-', $item->bulan);
            $monthNum = (int)$parts[1];
            $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return [
                'bulan' => $months[$monthNum] . ' ' . $parts[0],
                'partisipasi' => (int)$item->partisipasi
            ];
        });

        // Sebaran Program
        $sebaranProgram = MicroProgram::select('jenis_program as name', DB::raw('COUNT(id) as value'))
            ->groupBy('jenis_program')
            ->get();

        // Rincian Capaian
        $rincianCapaian = MicroProgram::orderBy('created_at', 'desc')->get();

        $data = [
            'total_warga_bekerja' => $totalWargaBekerja,
            'dampak_lingkungan' => [
                'value' => $totalDampak,
                'unit' => 'Kg Kompos'
            ],
            'total_insentif' => $totalInsentif,
            'tren_partisipasi' => $formattedTren,
            'sebaran_program' => $sebaranProgram,
            'rincian_capaian' => $rincianCapaian
        ];

        return view('admin.analisis', compact('data', 'period'));
    }
}
