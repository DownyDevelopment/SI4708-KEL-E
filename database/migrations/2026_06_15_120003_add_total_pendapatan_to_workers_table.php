<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            if (!Schema::hasColumn('workers', 'total_pendapatan')) {
                $table->unsignedBigInteger('total_pendapatan')->default(0)->after('nomor_rekening');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            if (Schema::hasColumn('workers', 'total_pendapatan')) {
                $table->dropColumn('total_pendapatan');
            }
        });
    }
};
