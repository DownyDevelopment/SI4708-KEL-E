<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('logbooks', 'status_validasi')) {
                $table->string('status_validasi', 20)->nullable()->after('progres_persentase');
            }
        });

        Schema::table('insentifs', function (Blueprint $table) {
            if (!Schema::hasColumn('insentifs', 'logbook_id')) {
                $table->foreignId('logbook_id')->nullable()->after('worker_id')->constrained('logbooks')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('insentifs', function (Blueprint $table) {
            if (Schema::hasColumn('insentifs', 'logbook_id')) {
                $table->dropForeign(['logbook_id']);
                $table->dropColumn('logbook_id');
            }
        });

        Schema::table('logbooks', function (Blueprint $table) {
            if (Schema::hasColumn('logbooks', 'status_validasi')) {
                $table->dropColumn('status_validasi');
            }
        });
    }
};
