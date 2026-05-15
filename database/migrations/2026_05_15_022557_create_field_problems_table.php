<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('field_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengawas_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('waktu');
            $table->text('masalah');
            $table->enum('tingkatan_masalah', ['low', 'mediate', 'high']);
            $table->string('lokasi_masalah');
            $table->string('kordinat')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('field_problems');
    }
};
