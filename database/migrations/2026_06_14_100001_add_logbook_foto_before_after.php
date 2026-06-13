<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('logbooks', 'foto_sebelum')) {
                $table->string('foto_sebelum')->nullable()->after('foto_bukti_url');
            }
            if (!Schema::hasColumn('logbooks', 'foto_sesudah')) {
                $table->string('foto_sesudah')->nullable()->after('foto_sebelum');
            }
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            if (Schema::hasColumn('logbooks', 'foto_sesudah')) {
                $table->dropColumn('foto_sesudah');
            }
            if (Schema::hasColumn('logbooks', 'foto_sebelum')) {
                $table->dropColumn('foto_sebelum');
            }
        });
    }
};
