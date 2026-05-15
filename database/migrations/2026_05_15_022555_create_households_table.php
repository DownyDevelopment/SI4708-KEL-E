<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('kepala_keluarga');
            $table->text('alamat');
            $table->string('rt_rw', 50);
            $table->integer('jumlah_anggota');
            $table->integer('pendapatan_per_bulan');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('households');
    }
};
