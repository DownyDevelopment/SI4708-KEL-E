<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            [
                'key' => 'min_poin_reward',
                'value' => '100',
                'label' => 'Batas Minimum Poin untuk Reward',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'kuota_jadwal_harian',
                'value' => '10',
                'label' => 'Kuota Jadwal Harian per Program',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'upah_default_logbook',
                'value' => '50000',
                'label' => 'Upah Default Validasi Logbook (Rp)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
