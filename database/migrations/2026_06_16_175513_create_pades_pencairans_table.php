<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pades_pencairans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nominal');
            $table->date('tanggal_pencairan');
            $table->text('keterangan');
            $table->string('bukti_foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pades_pencairans');
    }
};
