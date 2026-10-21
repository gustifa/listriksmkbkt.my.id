<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan kolom penanda sync ke tabel transaksi lokal
        $tables = ['daily_attendances', 'attendances', 'prayer_attendances', 'student_violations', 'activity_log'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'is_synced')) {
                        $table->boolean('is_synced')->default(false)->index();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['daily_attendances', 'attendances', 'prayer_attendances', 'student_violations', 'activity_log'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn(['is_synced']);
                });
            }
        }
    }
};