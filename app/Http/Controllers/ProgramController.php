<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MicroProgram;

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

        return redirect()->back()->with('success', 'Program berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $program = MicroProgram::findOrFail($id);
        $program->update($request->all());

        return redirect()->back()->with('success', 'Program berhasil diupdate.');
    }

    public function destroy($id)
    {
        MicroProgram::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Program berhasil dihapus.');
    }
}
