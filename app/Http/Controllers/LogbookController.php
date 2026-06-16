<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\WorkSchedule;
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
            'worker_group_id' => 'nullable|exists:worker_groups,id',
            'tanggal' => 'nullable|date',
            'progres_persentase' => 'required|numeric|min:0|max:100',
            'catatan_progres' => 'nullable|string',
            'catatan' => 'nullable|string',
            'lokasi_pekerjaan' => 'nullable|string',
            'pekerja_terlibat' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_sebelum' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'foto_sesudah' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'detail_monitoring' => 'nullable|array',
            'detail_monitoring.luas_area' => 'nullable|numeric|min:0',
            'detail_monitoring.berat_sampah' => 'nullable|numeric|min:0',
            'detail_monitoring.jenis_tanaman' => 'nullable|string|max:255',
            'detail_monitoring.luas_kebun' => 'nullable|numeric|min:0',
            'detail_monitoring.panjang_area' => 'nullable|numeric|min:0',
            'detail_monitoring.material_dipakai' => 'nullable|string|max:255',
        ]);

        $schedule = WorkSchedule::findOrFail($validated['schedule_id']);
        $groupId = $validated['worker_group_id'] ?? $schedule->worker_group_id;
        $catatan = $validated['catatan_progres'] ?? $validated['catatan'] ?? null;
        $detailMonitoring = $this->cleanDetailMonitoring($validated['detail_monitoring'] ?? null);

        $fotoSebelum = $this->storePhoto($request->file('foto_sebelum'));
        $fotoSesudah = $this->storePhoto(
            $request->file('foto_sesudah') ?? $request->file('foto_bukti') ?? $request->file('foto')
        );

        Logbook::create([
            'schedule_id' => $validated['schedule_id'],
            'worker_group_id' => $groupId,
            'pengawas_id' => Auth::id(),
            'tanggal' => $validated['tanggal'] ?? now()->toDateString(),
            'catatan_progres' => $catatan,
            'catatan' => $catatan,
            'progres_persentase' => (int) $validated['progres_persentase'],
            'status_validasi' => (int) $validated['progres_persentase'] >= 100 ? 'menunggu' : null,
            'foto_sebelum' => $fotoSebelum,
            'foto_sesudah' => $fotoSesudah,
            'foto_bukti' => $fotoSesudah,
            'foto_bukti_url' => $fotoSesudah,
            'lokasi_pekerjaan' => $validated['lokasi_pekerjaan'] ?? null,
            'pekerja_terlibat' => $validated['pekerja_terlibat'] ?? null,
            'detail_monitoring' => $detailMonitoring,
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
            'worker_group_id' => 'nullable|exists:worker_groups,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_sebelum' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_sesudah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'detail_monitoring' => 'nullable|array',
        ]);

        $catatan = $validated['catatan_progres'] ?? $validated['catatan'] ?? $logbook->catatan;
        $fotoSebelum = $logbook->foto_sebelum;
        $fotoSesudah = $logbook->foto_sesudah ?? $logbook->foto_bukti ?? $logbook->foto_bukti_url;

        if ($request->hasFile('foto_sebelum')) {
            $this->deletePhoto($logbook->foto_sebelum);
            $fotoSebelum = $this->storePhoto($request->file('foto_sebelum'));
        }

        $uploadedSesudah = $request->file('foto_sesudah') ?? $request->file('foto_bukti') ?? $request->file('foto');
        if ($uploadedSesudah) {
            $this->deletePhoto($logbook->foto_sesudah ?? $logbook->foto_bukti);
            $fotoSesudah = $this->storePhoto($uploadedSesudah);
        }

        $logbook->update([
            'worker_group_id' => $validated['worker_group_id'] ?? $logbook->worker_group_id ?? $logbook->schedule?->worker_group_id,
            'catatan_progres' => $catatan,
            'catatan' => $catatan,
            'progres_persentase' => (int) $validated['progres_persentase'],
            'status_validasi' => (int) $validated['progres_persentase'] >= 100 && $logbook->status_validasi !== 'disetujui'
                ? 'menunggu'
                : $logbook->status_validasi,
            'foto_sebelum' => $fotoSebelum,
            'foto_sesudah' => $fotoSesudah,
            'foto_bukti' => $fotoSesudah,
            'foto_bukti_url' => $fotoSesudah,
            'detail_monitoring' => $this->cleanDetailMonitoring($validated['detail_monitoring'] ?? $logbook->detail_monitoring),
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

    public function evaluate(Request $request, int $id)
    {
        $logbook = Logbook::with('schedule.program')->findOrFail($id);

        $validated = $request->validate([
            'rating_kinerja' => 'required|integer|min:1|max:5',
            'catatan_evaluasi' => 'nullable|string|max:2000',
        ]);

        if ((int) $logbook->progres_persentase < 100) {
            return redirect()->back()->with('error', 'Evaluasi hanya dapat diberikan setelah pekerjaan selesai (progres 100%).');
        }

        $logbook->update([
            'rating_kinerja' => $validated['rating_kinerja'],
            'catatan_evaluasi' => $validated['catatan_evaluasi'] ?? null,
            'evaluated_by' => Auth::id(),
            'evaluated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Evaluasi kinerja berhasil disimpan.');
    }

    private function storePhoto(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return '/storage/' . $file->store('logbooks', 'public');
    }

    private function deletePhoto(?string $path): void
    {
        if (!$path) {
            return;
        }

        $relative = str_replace('/storage/', '', $path);
        Storage::disk('public')->delete($relative);
    }

    private function cleanDetailMonitoring(?array $detail): ?array
    {
        if (!$detail) {
            return null;
        }

        $cleaned = collect($detail)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        return $cleaned === [] ? null : $cleaned;
    }
}
