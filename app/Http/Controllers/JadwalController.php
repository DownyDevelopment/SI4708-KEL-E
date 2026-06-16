<?php

namespace App\Http\Controllers;

use App\Models\MicroProgram;
use App\Models\ScheduleAssignment;
use App\Models\Worker;
use App\Models\WorkerGroup;
use App\Models\WorkSchedule;
use App\Support\OperationalNotifier;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use App\Support\ProfilingScorer;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.perencanaan', ['tab' => 'tugas']);
    }

    public function pengawasIndex(Request $request)
    {
        $data = $this->operasionalData();
        $data['activeTab'] = $request->query('tab', 'jadwal');
        $data['items'] = \App\Models\Inventaris::where('kuantitas', '>', 0)->get();
        $data['households'] = \App\Models\Household::all();

        return view('pengawas.operasional', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:micro_programs,id',
            'worker_group_id' => 'required|exists:worker_groups,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:scheduled,in_progress,completed,delayed',
            'deskripsi' => 'nullable|string',
            'jam_mulai' => 'nullable|string|max:50',
            'jam_selesai' => 'nullable|string|max:50',
            'shift_label' => 'nullable|string|max:100',
        ]);

        $program = MicroProgram::findOrFail($validated['program_id']);

        $kuota = (int) SystemSetting::get('kuota_jadwal_harian', 10);
        $existingCount = WorkSchedule::where('program_id', $validated['program_id'])
            ->whereDate('tanggal', $validated['tanggal'])
            ->count();

        if ($existingCount >= $kuota) {
            return redirect()->back()
                ->with('error', "Kuota jadwal harian untuk program ini sudah penuh (maks. {$kuota} per hari).")
                ->withInput();
        }

        $groupQuota = (int) SystemSetting::get('kuota_kelompok_kerja', 5);
        $groupCountToday = WorkSchedule::where('worker_group_id', $validated['worker_group_id'])
            ->whereDate('tanggal', $validated['tanggal'])
            ->count();

        if ($groupCountToday >= $groupQuota) {
            return redirect()->back()
                ->with('error', "Kelompok ini sudah mencapai kuota kerja harian (maks. {$groupQuota} per hari).")
                ->withInput();
        }

        DB::transaction(function () use ($validated, $program) {
            WorkSchedule::create([
                'program_id' => $validated['program_id'],
                'worker_group_id' => $validated['worker_group_id'],
                'tanggal' => $validated['tanggal'],
                'status' => $validated['status'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'jam_mulai' => $validated['jam_mulai'] ?? null,
                'jam_selesai' => $validated['jam_selesai'] ?? null,
                'shift_label' => $validated['shift_label'] ?? null,
            ]);

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
            'worker_group_id' => 'required|exists:worker_groups,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:scheduled,in_progress,completed,delayed',
            'deskripsi' => 'nullable|string',
            'jam_mulai' => 'nullable|string|max:50',
            'jam_selesai' => 'nullable|string|max:50',
            'shift_label' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($schedule, $validated) {
            $schedule->update([
                'program_id' => $validated['program_id'],
                'worker_group_id' => $validated['worker_group_id'],
                'tanggal' => $validated['tanggal'],
                'status' => $validated['status'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'jam_mulai' => $validated['jam_mulai'] ?? null,
                'jam_selesai' => $validated['jam_selesai'] ?? null,
                'shift_label' => $validated['shift_label'] ?? null,
            ]);

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

    public function operasionalData(): array
    {
        $jadwal = WorkSchedule::with(['program', 'workerGroup.workers', 'logbooks'])
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (WorkSchedule $item) {
                $latestLogbook = $item->logbooks->sortByDesc('created_at')->first();
                $item->tugas = $item->program?->nama_program;
                $item->jenis_program = $item->program?->jenis_program;
                $item->progres_terakhir = $latestLogbook?->progres_persentase ?? 0;
                $item->kelompok_nama = $item->workerGroup?->nama_kelompok;
                $item->pekerja_nama = $item->workerGroup?->workers
                    ->pluck('nama')
                    ->values()
                    ->all() ?? [];

                $item->desa_lokasi = $item->program?->desa_lokasi ?? $item->program?->lokasi;
                $item->pekerja_desa = $item->workerGroup?->workers
                    ->map(fn ($w) => $w->desa_asal)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all() ?? [];
                $item->lintas_desa = collect($item->pekerja_desa)->contains(fn ($d) => $d && $item->desa_lokasi && $d !== $item->desa_lokasi);

                return $item;
            });

        return [
            'jadwal' => $jadwal,
            'programs' => MicroProgram::orderBy('nama_program')->get(),
            'workerGroups' => WorkerGroup::withCount('workers')->orderBy('nama_kelompok')->get(),
            'workers' => Worker::with('household')
                ->where('status_program', 'aktif')
                ->where('prioritas', '!=', 'tidak_layak')
                ->orderByDesc('skor_vulnerabilitas')
                ->get(),
            'workerMatches' => $this->buildWorkerMatches(MicroProgram::all(), Worker::with('household')->where('status_program', 'aktif')->get()),
            'logbooks' => \App\Models\Logbook::with(['schedule.program', 'workerGroup.workers', 'pengawas'])
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ];
    }
}
