<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PadesPencairan;
use App\Models\InventarisHistory;
use Illuminate\Support\Facades\Storage;

class PadesController extends Controller
{
    public function index()
    {
        // 1. Hitung total penjualan dari inventaris_histories
        $totalSales = 0;
        $histories = InventarisHistory::where('tipe_perubahan', 'kurang')
            ->where('keterangan', 'like', '%Penjualan%')
            ->get();

        foreach ($histories as $h) {
            if (preg_match('/Total:\s*Rp[^\d]*([\d\.,]+)/u', $h->keterangan, $matches)) {
                $cleaned = str_replace('.', '', $matches[1]);
                $cleaned = str_replace(',', '.', $cleaned);
                $totalSales += (float)$cleaned;
            }
        }

        // 2. Hitung total yang sudah dicairkan ke PADes
        $totalDisbursed = PadesPencairan::sum('nominal');

        // 3. Saldo siap cair
        $availableBalance = max(0, $totalSales - $totalDisbursed);

        // 4. Riwayat pencairan PADes terurut dari yang terbaru
        $pencairans = PadesPencairan::orderByDesc('tanggal_pencairan')
            ->orderByDesc('id')
            ->get();

        return view('admin.pades.index', compact('totalSales', 'totalDisbursed', 'availableBalance', 'pencairans'));
    }

    public function store(Request $request)
    {
        // 1. Hitung ulang saldo siap cair untuk validasi sisi server
        $totalSales = 0;
        $histories = InventarisHistory::where('tipe_perubahan', 'kurang')
            ->where('keterangan', 'like', '%Penjualan%')
            ->get();

        foreach ($histories as $h) {
            if (preg_match('/Total:\s*Rp[^\d]*([\d\.,]+)/u', $h->keterangan, $matches)) {
                $cleaned = str_replace('.', '', $matches[1]);
                $cleaned = str_replace(',', '.', $cleaned);
                $totalSales += (float)$cleaned;
            }
        }

        $totalDisbursed = PadesPencairan::sum('nominal');
        $availableBalance = max(0, $totalSales - $totalDisbursed);

        // 2. Validasi input
        $request->validate([
            'nominal' => 'required|integer|min:1',
            'tanggal_pencairan' => 'required|date',
            'keterangan' => 'required|string|max:1000',
            'bukti_foto' => 'nullable|image|max:5120',
        ]);

        if ($request->nominal > $availableBalance) {
            return redirect()->back()->withErrors(['nominal' => 'Nominal pencairan melebihi saldo siap cair yang tersedia (Rp ' . number_format($availableBalance, 0, ',', '.') . ').'])->withInput();
        }

        // 3. Simpan foto jika diunggah
        $photoPath = null;
        if ($request->hasFile('bukti_foto')) {
            $photoPath = '/storage/' . $request->file('bukti_foto')->store('pades', 'public');
        }

        // 4. Buat pencairan baru
        PadesPencairan::create([
            'nominal' => $request->nominal,
            'tanggal_pencairan' => $request->tanggal_pencairan,
            'keterangan' => $request->keterangan,
            'bukti_foto' => $photoPath,
        ]);

        return redirect()->route('admin.pades')->with('success', 'Dana hasil penjualan berhasil dicairkan ke PADes.');
    }
}
