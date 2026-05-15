<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('nama_penghargaan');
            $table->date('tanggal_pemberian');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rewards');
    }
};
