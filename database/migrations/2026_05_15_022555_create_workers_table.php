<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon', 50)->nullable();
            $table->string('kontak_darurat')->nullable();
            $table->string('status_keluarga', 50)->nullable();
            $table->string('status_rumah', 50)->nullable();
            $table->text('riwayat_penyakit')->nullable();
            $table->string('kemampuan_utama')->nullable();
            $table->foreignId('household_id')->nullable()->constrained('households')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('workers');
    }
};
