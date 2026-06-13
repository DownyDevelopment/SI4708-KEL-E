<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\Worker;
use App\Models\WorkSchedule;
use App\Models\ScheduleAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LogbookController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('pengawas.operasional', ['tab' => 'logbook']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:work_schedules,id',
            'worker_id' => 'nullable|exists:workers,id',
            'tanggal' => 'nullable|date',
            'progres_persentase' => 'required|numeric|min:0|max:100',
            'catatan_progres' => 'nullable|string',
            'catatan' => 'nullable|string',
            'lokasi_pekerjaan' => 'nullable|string',
            'pekerja_terlibat' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $schedule = WorkSchedule::findOrFail($validated['schedule_id']);
        $catatan = $validated['catatan_progres'] ?? $validated['catatan'] ?? null;

        $fotoPath = null;
        $uploadedFile = $request->file('foto_bukti') ?? $request->file('foto');

        if ($uploadedFile) {
            $stored = $uploadedFile->store('logbooks', 'public');
            $fotoPath = '/storage/' . $stored;
        }

        Logbook::create([
            'schedule_id' => $validated['schedule_id'],
            'worker_id' => $validated['worker_id'] ?? null,
            'pengawas_id' => Auth::id(),
            'tanggal' => $validated['tanggal'] ?? now()->toDateString(),
            'catatan_progres' => $catatan,
            'catatan' => $catatan,
            'progres_persentase' => (int) $validated['progres_persentase'],
            'status_validasi' => (int) $validated['progres_persentase'] >= 100 ? 'menunggu' : null,
            'foto_bukti' => $fotoPath,
            'foto_bukti_url' => $fotoPath,
            'lokasi_pekerjaan' => $validated['lokasi_pekerjaan'] ?? null,
            'pekerja_terlibat' => $validated['pekerja_terlibat'] ?? null,
        ]);

        $progres = (int) $validated['progres_persentase'];

        if ($progres >= 100) {
            $schedule->update(['status' => 'completed']);
        } elseif ($progres > 0 && $schedule->status === 'scheduled') {
            $schedule->update(['status' => 'in_progress']);
        }

        return redirect()
            ->route('pengawas.operasional', ['tab' => 'logbook'])
            ->with('success', 'Logbook harian berhasil disimpan.');
    }

    public function update(Request $request, int $id)
    {
        $logbook = Logbook::findOrFail($id);

        $validated = $request->validate([
            'progres_persentase' => 'required|numeric|min:0|max:100',
            'catatan_progres' => 'nullable|string',
            'catatan' => 'nullable|string',
            'worker_id' => 'nullable|exists:workers,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $catatan = $validated['catatan_progres'] ?? $validated['catatan'] ?? $logbook->catatan;
        $uploadedFile = $request->file('foto_bukti') ?? $request->file('foto');
        $fotoPath = $logbook->foto_bukti ?? $logbook->foto_bukti_url;

        if ($uploadedFile) {
            if ($logbook->foto_bukti) {
                $old = str_replace('/storage/', '', $logbook->foto_bukti);
                Storage::disk('public')->delete($old);
            }
            $stored = $uploadedFile->store('logbooks', 'public');
            $fotoPath = '/storage/' . $stored;
        }

        $logbook->update([
            'worker_id' => $validated['worker_id'] ?? $logbook->worker_id,
            'catatan_progres' => $catatan,
            'catatan' => $catatan,
            'progres_persentase' => (int) $validated['progres_persentase'],
            'status_validasi' => (int) $validated['progres_persentase'] >= 100 && $logbook->status_validasi !== 'disetujui'
                ? 'menunggu'
                : $logbook->status_validasi,
            'foto_bukti' => $fotoPath,
            'foto_bukti_url' => $fotoPath,
        ]);

        $schedule = $logbook->schedule;
        $progres = (int) $validated['progres_persentase'];

        if ($schedule) {
            if ($progres >= 100) {
                $schedule->update(['status' => 'completed']);
            } elseif ($progres > 0 && $schedule->status === 'scheduled') {
                $schedule->update(['status' => 'in_progress']);
            }
        }

        return redirect()
            ->route('pengawas.operasional', ['tab' => 'logbook'])
            ->with('success', 'Progres logbook berhasil diperbarui.');
    }
}
