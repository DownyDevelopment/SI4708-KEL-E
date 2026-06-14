<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->string('status_kesejahteraan', 30)->default('Pending')->after('status_program');
            $table->string('keahlian_kerja', 255)->nullable()->after('kemampuan_utama');
            $table->unsignedTinyInteger('total_skor')->nullable()->after('skor_vulnerabilitas');
        });

        Schema::create('profiling_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor_makan');
            $table->unsignedTinyInteger('skor_sanitasi');
            $table->unsignedTinyInteger('skor_pendapatan');
            $table->unsignedTinyInteger('skor_pendidikan');
            $table->unsignedTinyInteger('total_skor');
            $table->string('kategori_kelayakan', 30);
            $table->string('bukti_foto_kondisi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiling_histories');

        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['status_kesejahteraan', 'keahlian_kerja', 'total_skor']);
        });
    }
};
