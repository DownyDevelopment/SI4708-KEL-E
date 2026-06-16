<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_groups', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelompok');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('worker_group_worker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_group_id')->constrained('worker_groups')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['worker_group_id', 'worker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_group_worker');
        Schema::dropIfExists('worker_groups');
    }
};
