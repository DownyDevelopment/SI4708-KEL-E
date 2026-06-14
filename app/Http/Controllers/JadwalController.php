<?php

namespace App\Http\Controllers;

use App\Models\MicroProgram;
use App\Models\ScheduleAssignment;
use App\Models\Worker;
use App\Models\WorkSchedule;
use App\Support\OperationalNotifier;
use Illuminate\Http\Request;
use App\Support\ProfilingScorer;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    public function index()
    {
        return view('admin.tugas', $this->operasionalData());
    }

    public function pengawasIndex(Request $request)
    {
        $data = $this->operasionalData();
        $data['activeTab'] = $request->query('tab', 'jadwal');

        return view('pengawas.operasional', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:micro_programs,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:scheduled,in_progress,completed',
            'deskripsi' => 'nullable|string',
            'jam_mulai' => 'nullable|string|max:50',
            'jam_selesai' => 'nullable|string|max:50',
            'shift_label' => 'nullable|string|max:100',
            'worker_ids' => 'required|array|min:1',
            'worker_ids.*' => 'exists:workers,id',
        ]);

        $program = MicroProgram::findOrFail($validated['program_id']);

        DB::transaction(function () use ($validated, $program) {
            $schedule = WorkSchedule::create([
                'program_id' => $validated['program_id'],
                'tanggal' => $validated['tanggal'],
                'status' => $validated['status'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'jam_mulai' => $validated['jam_mulai'] ?? null,
                'jam_selesai' => $validated['jam_selesai'] ?? null,
                'shift_label' => $validated['shift_label'] ?? null,
            ]);

            foreach ($validated['worker_ids'] as $workerId) {
                ScheduleAssignment::create([
                    'schedule_id' => $schedule->id,
                    'worker_id' => $workerId,
                ]);
            }

            OperationalNotifier::notify(
                'Jadwal Baru',
                "Jadwal baru ditambahkan untuk program {$program->nama_program}",
                '/pengawas/operasional?tab=jadwal'
            );
        });

        return redirect()->back()->with('success', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $schedule = WorkSchedule::with('program')->findOrFail($id);

        $validated = $request->validate([
            'program_id' => 'required|exists:micro_programs,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:scheduled,in_progress,completed',
            'deskripsi' => 'nullable|string',
            'jam_mulai' => 'nullable|string|max:50',
            'jam_selesai' => 'nullable|string|max:50',
            'shift_label' => 'nullable|string|max:100',
            'worker_ids' => 'required|array|min:1',
            'worker_ids.*' => 'exists:workers,id',
        ]);

        DB::transaction(function () use ($schedule, $validated) {
            $schedule->update([
                'program_id' => $validated['program_id'],
                'tanggal' => $validated['tanggal'],
                'status' => $validated['status'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'jam_mulai' => $validated['jam_mulai'] ?? null,
                'jam_selesai' => $validated['jam_selesai'] ?? null,
                'shift_label' => $validated['shift_label'] ?? null,
            ]);

            $schedule->assignments()->delete();

            foreach ($validated['worker_ids'] as $workerId) {
                ScheduleAssignment::create([
                    'schedule_id' => $schedule->id,
                    'worker_id' => $workerId,
                ]);
            }

            $programName = MicroProgram::find($validated['program_id'])?->nama_program
                ?? $schedule->program?->nama_program
                ?? 'Program';
            OperationalNotifier::notify(
                'Jadwal Diperbarui',
                "Jadwal program {$programName} pada {$validated['tanggal']} telah diubah",
                '/pengawas/operasional?tab=jadwal'
            );
        });

        return redirect()->back()->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $schedule = WorkSchedule::with('program')->findOrFail($id);
        $programName = $schedule->program?->nama_program ?? 'Program';
        $tanggal = $schedule->tanggal;

        $schedule->delete();

        OperationalNotifier::notify(
            'Jadwal Dihapus',
            "Jadwal program {$programName} pada {$tanggal} telah dihapus",
            '/pengawas/operasional?tab=jadwal'
        );

        return redirect()->back()->with('success', 'Jadwal kerja berhasil dihapus.');
    }

    private function buildWorkerMatches($programs, $workers): array
    {
        $matches = [];

        foreach ($programs as $program) {
            $scored = $workers
                ->filter(fn (Worker $w) => ProfilingScorer::layakProgram($w))
                ->map(function (Worker $w) use ($program) {
                    return [
                        'worker_id' => $w->id,
                        'nama' => $w->nama,
                        'desa_asal' => $w->desa_asal,
                        'prioritas' => $w->prioritas,
                        'score' => ProfilingScorer::matchScore($w, $program->jenis_program, $program->sektor_keahlian),
                    ];
                })
                ->sortByDesc('score')
                ->take(5)
                ->values()
                ->all();

            $matches[$program->id] = $scored;
        }

        return $matches;
    }

    private function operasionalData(): array
    {
        $jadwal = WorkSchedule::with(['program', 'assignments.worker', 'logbooks'])
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (WorkSchedule $item) {
                $latestLogbook = $item->logbooks->sortByDesc('created_at')->first();
                $item->tugas = $item->program?->nama_program;
                $item->jenis_program = $item->program?->jenis_program;
                $item->progres_terakhir = $latestLogbook?->progres_persentase ?? 0;
                $item->pekerja_nama = $item->assignments
                    ->map(fn ($a) => $a->worker?->nama)
                    ->filter()
                    ->values()
                    ->all();

                $item->desa_lokasi = $item->program?->desa_lokasi ?? $item->program?->lokasi;
                $item->pekerja_desa = $item->assignments
                    ->map(fn ($a) => $a->worker?->desa_asal)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $item->lintas_desa = collect($item->pekerja_desa)->contains(fn ($d) => $d && $item->desa_lokasi && $d !== $item->desa_lokasi);

                return $item;
            });

        return [
            'jadwal' => $jadwal,
            'programs' => MicroProgram::orderBy('nama_program')->get(),
            'workers' => Worker::with('household')
                ->where('status_program', 'aktif')
                ->where('prioritas', '!=', 'tidak_layak')
                ->orderByDesc('skor_vulnerabilitas')
                ->get(),
            'workerMatches' => $this->buildWorkerMatches(MicroProgram::all(), Worker::with('household')->where('status_program', 'aktif')->get()),
            'logbooks' => \App\Models\Logbook::with(['schedule.program', 'worker', 'pengawas'])
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ];
    }

}
