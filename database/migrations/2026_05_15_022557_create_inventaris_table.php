<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('inventaris', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->string('kategori', 100);
            $table->decimal('kuantitas', 10, 2)->default(0);
            $table->string('satuan', 50);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventaris');
    }
};
