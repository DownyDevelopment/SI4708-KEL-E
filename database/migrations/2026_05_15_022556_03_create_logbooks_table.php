<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->nullable()->constrained('work_schedules')->cascadeOnDelete();
            $table->foreignId('pengawas_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->integer('progres_persentase')->default(0);
            $table->text('catatan')->nullable();
            $table->text('foto_bukti_url')->nullable();
            $table->string('lokasi_pekerjaan')->nullable();
            $table->text('pekerja_terlibat')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('logbooks');
    }
};
