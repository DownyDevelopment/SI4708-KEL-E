<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Household;

class HouseholdController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.pekerja', ['tab' => 'keluarga']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kepala_keluarga' => 'required|string|max:255',
            'rt_rw' => 'required|string|max:255',
            'jumlah_anggota' => 'required|integer|min:1',
            'pendapatan_per_bulan' => 'required|numeric|min:0',
            'alamat' => 'required|string',
            'nama_desa' => 'nullable|string|max:150',
        ]);

        Household::create($request->all());

        return redirect()->back()->with('success', 'Data keluarga berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kepala_keluarga' => 'required|string|max:255',
            'rt_rw' => 'required|string|max:255',
            'jumlah_anggota' => 'required|integer|min:1',
            'pendapatan_per_bulan' => 'required|numeric|min:0',
            'alamat' => 'required|string',
            'nama_desa' => 'nullable|string|max:150',
        ]);

        $household = Household::findOrFail($id);
        $household->update($request->only([
            'kepala_keluarga', 'rt_rw', 'jumlah_anggota', 'pendapatan_per_bulan', 'alamat', 'nama_desa',
        ]));

        return redirect()->back()->with('success', 'Data keluarga berhasil diperbarui.');
    }
}
