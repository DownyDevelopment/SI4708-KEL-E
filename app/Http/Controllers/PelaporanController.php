<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FieldProblem;
use Illuminate\Support\Facades\Auth;

class PelaporanController extends Controller
{
    public function index()
    {
        $reports = FieldProblem::join('users', 'field_problems.pengawas_id', '=', 'users.id')
            ->select('field_problems.*', 'users.nama as nama_pengawas')
            ->orderBy('field_problems.created_at', 'desc')
            ->get();

        return view('pengawas.pelaporan', [
            'reports' => $reports,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'masalah' => 'required|string',
            'tingkatan_masalah' => 'required|in:low,mediate,high',
            'lokasi_masalah' => 'required|string',
            'kordinat' => 'nullable|string'
        ]);

        FieldProblem::create(array_merge($request->all(), ['pengawas_id' => Auth::id()]));

        return redirect()->back()->with('success', 'Laporan berhasil dikirim.');
    }
}
