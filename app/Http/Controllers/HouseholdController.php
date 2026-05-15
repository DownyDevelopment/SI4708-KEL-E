<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Household;

class HouseholdController extends Controller
{
    public function index()
    {
        $households = Household::all();
        return view('admin.keluarga', compact('households'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kepala_keluarga' => 'required|string|max:255',
            'rt_rw' => 'required|string|max:255',
            'jumlah_anggota' => 'required|integer|min:1',
            'pendapatan_per_bulan' => 'required|numeric|min:0',
            'alamat' => 'required|string',
        ]);

        Household::create($request->all());

        return redirect()->back()->with('success', 'Data keluarga berhasil ditambahkan.');
    }
}
