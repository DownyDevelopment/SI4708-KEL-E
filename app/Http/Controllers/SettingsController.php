<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('key')->get()->keyBy('key');

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'min_poin_reward' => 'required|integer|min:0',
            'kuota_jadwal_harian' => 'required|integer|min:1|max:100',
            'kuota_kelompok_kerja' => 'required|integer|min:1|max:50',
            'upah_default_logbook' => 'required|integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        return redirect()->back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
