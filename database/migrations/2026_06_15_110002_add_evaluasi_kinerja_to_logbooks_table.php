<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating_kinerja')->nullable()->after('status_validasi');
            $table->text('catatan_evaluasi')->nullable()->after('rating_kinerja');
            $table->foreignId('evaluated_by')->nullable()->after('catatan_evaluasi')->constrained('users')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable()->after('evaluated_by');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropForeign(['evaluated_by']);
            $table->dropColumn(['rating_kinerja', 'catatan_evaluasi', 'evaluated_by', 'evaluated_at']);
        });
    }
};
