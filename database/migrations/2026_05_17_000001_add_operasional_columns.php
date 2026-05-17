<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('work_schedules', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('status');
            }
        });

        Schema::table('logbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('logbooks', 'worker_id')) {
                $table->foreignId('worker_id')->nullable()->after('schedule_id')->constrained('workers')->nullOnDelete();
            }
            if (!Schema::hasColumn('logbooks', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('worker_id');
            }
            if (!Schema::hasColumn('logbooks', 'catatan_progres')) {
                $table->text('catatan_progres')->nullable()->after('tanggal');
            }
            if (!Schema::hasColumn('logbooks', 'foto_bukti')) {
                $table->string('foto_bukti')->nullable()->after('foto_bukti_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            if (Schema::hasColumn('logbooks', 'worker_id')) {
                $table->dropForeign(['worker_id']);
                $table->dropColumn('worker_id');
            }
            if (Schema::hasColumn('logbooks', 'tanggal')) {
                $table->dropColumn('tanggal');
            }
            if (Schema::hasColumn('logbooks', 'catatan_progres')) {
                $table->dropColumn('catatan_progres');
            }
            if (Schema::hasColumn('logbooks', 'foto_bukti')) {
                $table->dropColumn('foto_bukti');
            }
        });

        Schema::table('work_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('work_schedules', 'deskripsi')) {
                $table->dropColumn('deskripsi');
            }
        });
    }
};
