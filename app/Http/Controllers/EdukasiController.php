<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EdukasiContent;

class EdukasiController extends Controller
{
    public function index()
    {
        $contents = EdukasiContent::all();
        return view('admin.edukasi', compact('contents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kategori' => 'required|string|max:255',
            'tipe_konten' => 'required|string|max:255',
            'url_konten' => 'nullable|string',
        ]);

        EdukasiContent::create($request->all());

        return redirect()->back()->with('success', 'Materi edukasi berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kategori' => 'required|string|max:255',
            'tipe_konten' => 'required|string|max:255',
            'url_konten' => 'nullable|string',
        ]);

        EdukasiContent::findOrFail($id)->update($request->all());

        return redirect()->back()->with('success', 'Materi edukasi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        EdukasiContent::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Materi edukasi berhasil dihapus.');
    }
}
