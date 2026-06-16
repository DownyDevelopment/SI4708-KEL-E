<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('system_settings')->where('key', 'kuota_kelompok_kerja')->exists()) {
            DB::table('system_settings')->insert([
                'key' => 'kuota_kelompok_kerja',
                'value' => '5',
                'label' => 'Kuota Kerja Kelompok per Hari',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->where('key', 'kuota_kelompok_kerja')->delete();
    }
};
