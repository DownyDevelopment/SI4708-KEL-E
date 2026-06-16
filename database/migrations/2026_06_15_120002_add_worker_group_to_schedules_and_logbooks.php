<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('work_schedules', 'worker_group_id')) {
                $table->foreignId('worker_group_id')->nullable()->after('program_id')->constrained('worker_groups')->nullOnDelete();
            }
        });

        Schema::table('logbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('logbooks', 'worker_group_id')) {
                $table->foreignId('worker_group_id')->nullable()->after('schedule_id')->constrained('worker_groups')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            if (Schema::hasColumn('logbooks', 'worker_group_id')) {
                $table->dropForeign(['worker_group_id']);
                $table->dropColumn('worker_group_id');
            }
        });

        Schema::table('work_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('work_schedules', 'worker_group_id')) {
                $table->dropForeign(['worker_group_id']);
                $table->dropColumn('worker_group_id');
            }
        });
    }
};
