<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('edukasi_contents', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('kategori', 100);
            $table->string('tipe_konten', 50);
            $table->text('url_konten')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('edukasi_contents');
    }
};
