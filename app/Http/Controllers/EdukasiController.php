<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Edukasi;

class EdukasiController extends Controller
{
    public function index()
    {
        $contents = Edukasi::all();
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

        Edukasi::create($request->all());

        return redirect()->back()->with('success', 'Materi edukasi berhasil ditambahkan.');
    }
}
