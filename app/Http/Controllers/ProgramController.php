<?php

namespace App\Http\Controllers;

use App\Models\MicroProgram;
use App\Support\OperationalNotifier;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function perencanaanIndex()
    {
        $programs = MicroProgram::all();
        return view('admin.perencanaan', compact('programs'));
    }

    public function programIndex()
    {
        return redirect()->route('admin.perencanaan');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_program' => 'required|string|max:255',
            'jenis_program' => 'required|string|max:255',
        ]);

        MicroProgram::create($request->all());

        $this->notifyStakeholders($request->input('stakeholders'), $request->input('nama_program'));

        return redirect()->back()->with('success', 'Program berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $program = MicroProgram::findOrFail($id);
        $program->update($request->all());

        $this->notifyStakeholders($request->input('stakeholders'), $program->nama_program);

        return redirect()->back()->with('success', 'Program berhasil diupdate.');
    }

    public function destroy($id)
    {
        MicroProgram::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Program berhasil dihapus.');
    }

    private function notifyStakeholders(?string $stakeholdersJson, ?string $programName): void
    {
        $stakeholders = json_decode($stakeholdersJson ?? '[]', true);
        if (!is_array($stakeholders) || empty($stakeholders)) {
            return;
        }

        $labels = collect($stakeholders)
            ->map(fn ($item) => trim(($item['nama'] ?? '') . ($item['peran'] ? ' (' . $item['peran'] . ')' : '')))
            ->filter()
            ->take(4)
            ->implode(', ');

        if ($labels === '') {
            return;
        }

        OperationalNotifier::notify(
            'Koordinasi Stakeholder',
            "Program {$programName} melibatkan stakeholder: {$labels}",
            '/pengawas/operasional'
        );
    }
}
