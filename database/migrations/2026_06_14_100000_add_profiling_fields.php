<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->string('nama_desa', 150)->nullable()->after('alamat');
        });

        Schema::table('workers', function (Blueprint $table) {
            $table->string('pendidikan_terakhir', 50)->nullable()->after('kemampuan_utama');
            $table->string('frekuensi_makan', 30)->nullable()->after('pendidikan_terakhir');
            $table->string('kondisi_sanitasi', 80)->nullable()->after('frekuensi_makan');
            $table->string('akses_air_bersih', 50)->nullable()->after('kondisi_sanitasi');
            $table->string('status_gizi', 30)->nullable()->after('akses_air_bersih');
            $table->text('kebiasaan')->nullable()->after('status_gizi');
            $table->unsignedTinyInteger('skor_vulnerabilitas')->nullable()->after('kebiasaan');
            $table->string('prioritas', 20)->default('sedang')->after('skor_vulnerabilitas');
            $table->string('status_program', 20)->default('aktif')->after('prioritas');
            $table->json('profiling_awal')->nullable()->after('status_program');
        });

        Schema::table('micro_programs', function (Blueprint $table) {
            $table->string('desa_lokasi', 150)->nullable()->after('lokasi');
            $table->string('sektor_keahlian', 100)->nullable()->after('jenis_program');
        });

        Schema::create('profiling_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('skor_vulnerabilitas');
            $table->string('frekuensi_makan', 30)->nullable();
            $table->string('kondisi_sanitasi', 80)->nullable();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->integer('pendapatan_per_kapita')->nullable();
            $table->string('status_gizi', 30)->nullable();
            $table->text('catatan')->nullable();
            $table->date('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiling_snapshots');

        Schema::table('micro_programs', function (Blueprint $table) {
            $table->dropColumn(['desa_lokasi', 'sektor_keahlian']);
        });

        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn([
                'pendidikan_terakhir', 'frekuensi_makan', 'kondisi_sanitasi',
                'akses_air_bersih', 'status_gizi', 'kebiasaan', 'skor_vulnerabilitas',
                'prioritas', 'status_program', 'profiling_awal',
            ]);
        });

        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn('nama_desa');
        });
    }
};
