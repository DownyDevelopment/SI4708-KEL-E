<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\WorkerGroup;
use Illuminate\Http\Request;

class WorkerGroupController extends Controller
{
    public function index()
    {
        return redirect()->route('pengawas.profiling', ['tab' => 'kelompok']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelompok' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'worker_ids' => 'nullable|array',
            'worker_ids.*' => 'exists:workers,id',
        ]);

        $group = WorkerGroup::create([
            'nama_kelompok' => $validated['nama_kelompok'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        if (!empty($validated['worker_ids'])) {
            $group->workers()->sync($validated['worker_ids']);
        }

        return redirect()->back()->with('success', 'Kelompok kerja berhasil dibuat.');
    }

    public function update(Request $request, int $id)
    {
        $group = WorkerGroup::findOrFail($id);

        $validated = $request->validate([
            'nama_kelompok' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'worker_ids' => 'nullable|array',
            'worker_ids.*' => 'exists:workers,id',
        ]);

        $group->update([
            'nama_kelompok' => $validated['nama_kelompok'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        $group->workers()->sync($validated['worker_ids'] ?? []);

        return redirect()->back()->with('success', 'Kelompok kerja berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        WorkerGroup::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Kelompok kerja berhasil dihapus.');
    }
}
