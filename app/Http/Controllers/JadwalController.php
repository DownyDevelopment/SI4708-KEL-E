<?php

namespace App\Http\Controllers;

use App\Models\MicroProgram;
use App\Models\Notification;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
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

            $this->notifyPengawasNewSchedule($program->nama_program);
        });

        return redirect()->back()->with('success', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $schedule = WorkSchedule::findOrFail($id);

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
        });

        return redirect()->back()->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        WorkSchedule::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Jadwal kerja berhasil dihapus.');
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
                $item->progres_terakhir = $latestLogbook?->progres_persentase ?? 0;
                $item->pekerja_nama = $item->assignments
                    ->map(fn ($a) => $a->worker?->nama)
                    ->filter()
                    ->values()
                    ->all();

                return $item;
            });

        return [
            'jadwal' => $jadwal,
            'programs' => MicroProgram::orderBy('nama_program')->get(),
            'workers' => Worker::orderBy('nama')->get(),
            'logbooks' => \App\Models\Logbook::with(['schedule.program', 'worker', 'pengawas'])
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ];
    }

    private function notifyPengawasNewSchedule(string $namaProgram): void
    {
        $pengawasUsers = User::where('role', 'pengawas')->get();

        foreach ($pengawasUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'judul' => 'Jadwal Baru',
                'pesan' => "Jadwal baru ditambahkan untuk program {$namaProgram}",
                'is_read' => false,
                'link_url' => '/pengawas/operasional',
            ]);
        }
    }
}
