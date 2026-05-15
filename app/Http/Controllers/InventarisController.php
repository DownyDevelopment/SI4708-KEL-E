<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventaris;
use App\Models\InventarisHistory;
use Illuminate\Support\Facades\DB;

class InventarisController extends Controller
{
    public function index()
    {
        $items = Inventaris::all();
        return view('admin.inventaris', compact('items'));
    }

    public function trackingIndex()
    {
        $items = Inventaris::whereIn('kategori', ['Kompos', 'Kerajinan'])->get();
        $households = \App\Models\Household::all();
        return view('admin.tracking_reducing', compact('items', 'households'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'kuantitas' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $item = Inventaris::create($request->all());

            InventarisHistory::create([
                'inventaris_id' => $item->id,
                'jumlah_perubahan' => $item->kuantitas,
                'tipe_perubahan' => 'tambah',
                'keterangan' => 'Stok Awal'
            ]);
        });

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan ke inventaris.');
    }

    public function adjust(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|numeric|min:0.1',
            'tipe' => 'required|in:tambah,kurang',
            'keterangan' => 'required|string'
        ]);

        DB::transaction(function () use ($request, $id) {
            $item = Inventaris::findOrFail($id);
            
            if ($request->tipe === 'tambah') {
                $item->kuantitas += $request->jumlah;
            } else {
                $item->kuantitas -= $request->jumlah;
                if ($item->kuantitas < 0) $item->kuantitas = 0;
            }
            
            $item->save();

            InventarisHistory::create([
                'inventaris_id' => $item->id,
                'jumlah_perubahan' => $request->jumlah,
                'tipe_perubahan' => $request->tipe,
                'keterangan' => $request->keterangan
            ]);
        });

        return redirect()->back()->with('success', 'Stok berhasil diperbarui.');
    }

    public function history($id)
    {
        $histories = InventarisHistory::where('inventaris_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($histories);
    }
}
