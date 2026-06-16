<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->string('nama_bank', 100)->nullable()->after('no_telepon');
            $table->string('nomor_rekening', 50)->nullable()->after('nama_bank');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'nomor_rekening']);
        });
    }
};
