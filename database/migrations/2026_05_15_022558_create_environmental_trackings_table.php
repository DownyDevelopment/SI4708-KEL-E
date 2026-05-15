<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('environmental_trackings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('jenis_limbah');
            $table->decimal('volume_kg', 10, 2);
            $table->decimal('estimasi_emisi_berkurang_kg', 10, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('environmental_trackings');
    }
};
