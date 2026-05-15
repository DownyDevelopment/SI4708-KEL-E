<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('micro_programs')->cascadeOnDelete();
            $table->date('tanggal')->nullable();
            $table->string('jam_mulai', 50)->nullable();
            $table->string('jam_selesai', 50)->nullable();
            $table->string('shift_label', 100)->nullable();
            $table->string('status', 50)->default('scheduled');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('work_schedules');
    }
};
