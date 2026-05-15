<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('inventaris_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnDelete();
            $table->decimal('jumlah_perubahan', 10, 2);
            $table->enum('tipe_perubahan', ['tambah', 'kurang']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventaris_histories');
    }
};
