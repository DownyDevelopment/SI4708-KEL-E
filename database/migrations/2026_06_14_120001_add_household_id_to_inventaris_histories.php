<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventaris_histories', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->after('keterangan')->constrained('households')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventaris_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('household_id');
        });
    }
};
