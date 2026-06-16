<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventaris;
use App\Models\Household;

class DistribusiController extends Controller
{
    public function index()
    {
        return redirect()->route('pengawas.operasional', ['tab' => 'distribusi']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:inventaris,id',
            'jumlah' => 'required|numeric|min:0.1',
            'tipe' => 'required|string',
            'keterangan' => 'required|string',
            'household_id' => 'nullable|exists:households,id',
        ]);

        $item = Inventaris::findOrFail($request->item_id);

        if ($request->jumlah > $item->kuantitas) {
            return redirect()->back()->withErrors(['jumlah' => 'Stok tidak mencukupi']);
        }

        $item->kuantitas -= $request->jumlah;
        $item->save();

        \App\Models\InventarisHistory::create([
            'inventaris_id' => $item->id,
            'tipe_perubahan' => 'kurang',
            'jumlah_perubahan' => $request->jumlah,
            'keterangan' => $request->keterangan,
            'household_id' => $request->household_id,
        ]);

        return redirect()->back()->with('success', 'Distribusi/Penjualan berhasil dicatat.');
    }
}
