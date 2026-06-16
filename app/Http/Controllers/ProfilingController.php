<?php

namespace App\Http\Controllers;

use App\Models\ProfilingSnapshot;
use App\Models\Worker;
use App\Support\ProfilingScorer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilingController extends Controller
{
    public function index()
    {
        if (request()->is('admin/*')) {
            return redirect()->route('admin.pekerja', ['tab' => 'profiling']);
        }

        $workers = Worker::with('household')
            ->withSum('insentifs', 'jumlah_upah')
            ->withCount('insentifs')
            ->orderByDesc('total_skor')
            ->orderByDesc('skor_vulnerabilitas')
            ->get();

        $totalWorkers = $workers->count();
        $miskinCount = 0;
        $layakCount = 0;
        $tidakLayakCount = 0;
        $lulusCount = 0;
        $pekerjaanMakroStats = [];
        $kesejahteraanStats = [];
        $prioritasStats = [];

        foreach ($workers as $worker) {
            if ($worker->is_miskin) {
                $miskinCount++;
            }

            if ($worker->status_program === 'lulus') {
                $lulusCount++;
            } elseif (ProfilingScorer::layakProgram($worker)) {
                $layakCount++;
            } else {
                $tidakLayakCount++;
            }

            $makro = $worker->pekerjaan_makro;
            $pekerjaanMakroStats[$makro] = ($pekerjaanMakroStats[$makro] ?? 0) + 1;

            $kesejahteraan = $worker->status_kesejahteraan ?? $worker->klasifikasi_kesejahteraan;
            $kesejahteraanStats[$kesejahteraan] = ($kesejahteraanStats[$kesejahteraan] ?? 0) + 1;

            $prioritas = $worker->prioritas ?? 'sedang';
            $prioritasStats[$prioritas] = ($prioritasStats[$prioritas] ?? 0) + 1;
        }

        $persentaseMiskin = $totalWorkers > 0 ? round(($miskinCount / $totalWorkers) * 100, 1) : 0;
        $persentaseLayak = $totalWorkers > 0 ? round(($layakCount / $totalWorkers) * 100, 1) : 0;

        $groups = \App\Models\WorkerGroup::withCount('workers')
            ->with('workers')
            ->orderBy('nama_kelompok')
            ->get();

        $availableWorkers = Worker::with('workerGroups')
            ->orderBy('nama')
            ->get();

        return view('pengawas.profiling.index', compact(
            'workers',
            'totalWorkers',
            'miskinCount',
            'layakCount',
            'tidakLayakCount',
            'lulusCount',
            'persentaseMiskin',
            'persentaseLayak',
            'pekerjaanMakroStats',
            'kesejahteraanStats',
            'prioritasStats',
            'groups',
            'availableWorkers'
        ));
    }

    public function updateProfiling(Request $request, int $workerId)
    {
        $worker = Worker::with('household')->findOrFail($workerId);

        $validated = $request->validate([
            'frekuensi_makan' => 'required|string|max:30',
            'kondisi_sanitasi' => 'required|string|max:80',
            'pendidikan_terakhir' => 'required|string|max:50',
            'status_gizi' => 'nullable|string|max:30',
            'bukti_foto_kondisi' => 'nullable|image|max:5120',
            'catatan' => 'nullable|string',
        ]);

        $worker->frekuensi_makan = $validated['frekuensi_makan'];
        $worker->kondisi_sanitasi = $validated['kondisi_sanitasi'];
        $worker->pendidikan_terakhir = $validated['pendidikan_terakhir'];
        if ($validated['status_gizi'] ?? null) {
            $worker->status_gizi = $validated['status_gizi'];
        }

        ProfilingScorer::applyToWorker($worker);
        $worker->save();

        $photoPath = $this->storeProfilingPhoto($request->file('bukti_foto_kondisi'));
        ProfilingScorer::createHistory($worker, $photoPath);

        ProfilingSnapshot::create([
            'worker_id' => $worker->id,
            'recorded_by' => Auth::id(),
            'skor_vulnerabilitas' => $worker->skor_vulnerabilitas,
            'frekuensi_makan' => $worker->frekuensi_makan,
            'kondisi_sanitasi' => $worker->kondisi_sanitasi,
            'pendidikan_terakhir' => $worker->pendidikan_terakhir,
            'pendapatan_per_kapita' => $worker->household
                ? (int) round($worker->household->pendapatan_per_kapita)
                : null,
            'status_gizi' => $worker->status_gizi,
            'catatan' => $validated['catatan'] ?? 'Survei ulang profiling kesejahteraan.',
            'recorded_at' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Profiling diperbarui. Total skor: ' . $worker->total_skor . ' (' . $worker->status_kesejahteraan . ').');
    }

    public function markLulus(int $workerId)
    {
        $worker = Worker::findOrFail($workerId);
        $worker->update([
            'status_program' => 'lulus',
            'status_kesejahteraan' => 'Lulus/Tidak Layak',
        ]);

        ProfilingScorer::createHistory($worker);

        ProfilingSnapshot::create([
            'worker_id' => $worker->id,
            'recorded_by' => Auth::id(),
            'skor_vulnerabilitas' => $worker->skor_vulnerabilitas ?? 0,
            'frekuensi_makan' => $worker->frekuensi_makan,
            'kondisi_sanitasi' => $worker->kondisi_sanitasi,
            'pendidikan_terakhir' => $worker->pendidikan_terakhir,
            'pendapatan_per_kapita' => $worker->household
                ? (int) round($worker->household->pendapatan_per_kapita)
                : null,
            'status_gizi' => $worker->status_gizi,
            'catatan' => 'Status ditutup — peserta lulus program (kesejahteraan membaik).',
            'recorded_at' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', "{$worker->nama} ditandai lulus program. Slot dapat dialihkan ke calon baru.");
    }

    private function storeProfilingPhoto(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return '/storage/' . $file->store('profiling', 'public');
    }
}
