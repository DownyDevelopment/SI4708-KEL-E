<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\Household;

class WorkerController extends Controller
{
    public function index()
    {
        $workers = Worker::all();
        $households = Household::all();
        return view('admin.pekerja', compact('workers', 'households'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kemampuan_utama' => 'required|string|max:255',
        ]);

        Worker::create($request->all());

        return redirect()->back()->with('success', 'Pekerja berhasil ditambahkan.');
    }

    public function show($id)
    {
        $worker = Worker::with('schedules.program')->findOrFail($id);
        
        // Format the response for the frontend JS modal
        $formatted = $worker->toArray();
        $formatted['schedules'] = $worker->schedules->map(function ($s) {
            return [
                'nama_program' => $s->program ? $s->program->nama_program : 'Program Dihapus',
                'jam_mulai' => $s->jam_mulai,
                'jam_selesai' => $s->jam_selesai,
            ];
        });

        return response()->json($formatted);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kemampuan_utama' => 'required|string|max:255',
        ]);

        $worker = Worker::findOrFail($id);
        $worker->update($request->all());

        return redirect()->back()->with('success', 'Data Pekerja berhasil diperbarui.');
    }

    public function profile(int $id)
    {
        $worker = Worker::with([
            'household',
            'schedules.program',
            'insentifs' => fn ($q) => $q->orderByDesc('created_at')->limit(5),
        ])
            ->withSum('insentifs', 'jumlah_upah')
            ->withCount('insentifs')
            ->findOrFail($id);

        $programs = $worker->schedules
            ->pluck('program')
            ->filter()
            ->unique('id')
            ->values();

        $schedules = $worker->schedules
            ->sortByDesc('tanggal')
            ->values();

        $usia = $worker->tanggal_lahir
            ? Carbon::parse($worker->tanggal_lahir)->age
            : null;

        return view('admin.pekerja-profil', compact('worker', 'programs', 'schedules', 'usia'));
    }
}
