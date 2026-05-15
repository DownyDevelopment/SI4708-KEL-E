<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('micro_programs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_program');
            $table->string('jenis_program', 100);
            $table->text('deskripsi')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('kordinat')->nullable();
            $table->text('stakeholders')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('status', 50)->default('planned');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('micro_programs');
    }
};
