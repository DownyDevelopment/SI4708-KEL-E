<?php

namespace App\Http\Controllers;

use App\Models\ProfilingSnapshot;
use App\Models\Worker;
use App\Support\ProfilingScorer;
use Illuminate\Http\Request;
use App\Models\Household;

class WorkerController extends Controller
{
    public function index()
    {
        $workers = Worker::with('household')
            ->withSum('insentifs', 'jumlah_upah')
            ->withCount('insentifs')
            ->orderByDesc('total_skor')
            ->orderByDesc('skor_vulnerabilitas')
            ->get();
        $households = Household::all();

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

        return view('admin.pekerja', compact(
            'workers', 
            'households',
            'totalWorkers',
            'miskinCount',
            'layakCount',
            'tidakLayakCount',
            'lulusCount',
            'persentaseMiskin',
            'persentaseLayak',
            'pekerjaanMakroStats',
            'kesejahteraanStats',
            'prioritasStats'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kemampuan_utama' => 'required|string|max:255',
            'frekuensi_makan' => 'required|string|max:30',
            'kondisi_sanitasi' => 'required|string|max:80',
            'pendidikan_terakhir' => 'required|string|max:50',
            'akses_air_bersih' => 'nullable|string|max:50',
            'status_gizi' => 'nullable|string|max:30',
            'bukti_foto_kondisi' => 'nullable|image|max:5120',
            'total_pendapatan' => 'nullable|integer|min:0',
        ]);

        $worker = new Worker($request->except('bukti_foto_kondisi'));
        $worker->status_program = 'aktif';
        ProfilingScorer::applyToWorker($worker, saveInitial: true);

        if ($worker->prioritas === 'tidak_layak' || $worker->total_skor < 7) {
            $worker->status_program = 'tidak_layak';
            $worker->status_kesejahteraan = 'Lulus/Tidak Layak';
        }

        $worker->save();

        $photoPath = $this->storeProfilingPhoto($request->file('bukti_foto_kondisi'));
        ProfilingScorer::createHistory($worker, $photoPath);

        ProfilingSnapshot::create([
            'worker_id' => $worker->id,
            'recorded_by' => auth()->id(),
            'skor_vulnerabilitas' => $worker->skor_vulnerabilitas,
            'frekuensi_makan' => $worker->frekuensi_makan,
            'kondisi_sanitasi' => $worker->kondisi_sanitasi,
            'pendidikan_terakhir' => $worker->pendidikan_terakhir,
            'pendapatan_per_kapita' => $worker->household
                ? (int) round($worker->household->pendapatan_per_kapita)
                : null,
            'status_gizi' => $worker->status_gizi,
            'catatan' => 'Survei profiling awal saat pendaftaran.',
            'recorded_at' => now()->toDateString(),
        ]);

        $message = $worker->status_program === 'tidak_layak'
            ? 'Data tersimpan. Total skor ' . $worker->total_skor . ' — di bawah threshold, tidak layak program.'
            : 'Survei profiling berhasil. Total skor: ' . $worker->total_skor . ' (' . $worker->status_kesejahteraan . ').';

        return redirect()->back()->with('success', $message);
    }

    public function show($id)
    {
        $worker = Worker::with('schedules.program')->findOrFail($id);

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
            'total_pendapatan' => 'nullable|integer|min:0',
        ]);

        $worker = Worker::findOrFail($id);
        $worker->fill($request->all());
        ProfilingScorer::applyToWorker($worker);
        $worker->save();

        return redirect()->back()->with('success', 'Data pekerja berhasil diperbarui. Skor: ' . $worker->total_skor);
    }

    public function profile(int $id)
    {
        $worker = Worker::with([
            'household',
            'schedules.program',
            'profilingHistories' => fn ($q) => $q->orderByDesc('created_at'),
            'profilingSnapshots' => fn ($q) => $q->orderByDesc('recorded_at'),
        ])->findOrFail($id);

        $programs = $worker->schedules
            ->pluck('program')
            ->filter()
            ->unique('id')
            ->values();

        $schedules = $worker->schedules
            ->sortByDesc('tanggal')
            ->values();

        $usia = $worker->tanggal_lahir
            ? \Carbon\Carbon::parse($worker->tanggal_lahir)->age
            : null;

        return view('admin.pekerja-profil', compact('worker', 'programs', 'schedules', 'usia'));
    }

    private function storeProfilingPhoto(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return '/storage/' . $file->store('profiling', 'public');
    }
}
