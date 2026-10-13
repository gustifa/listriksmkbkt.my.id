<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perbaiki Sequence Primary Key PostgreSQL untuk Tabel Migrations
        DB::transaction(function () {
            DB::statement("
                DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relname = 'migrations_id_seq') THEN
                        CREATE SEQUENCE migrations_id_seq;
                    END IF;
                END $$;
            ");
            DB::statement("ALTER TABLE migrations ALTER COLUMN id SET DEFAULT nextval('migrations_id_seq')");
            DB::statement("SELECT setval('migrations_id_seq', COALESCE((SELECT MAX(id) FROM migrations), 0) + 1, false)");
        });

        // 2. Tambah Kolom is_geofence_active ke attendance_settings
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_settings', 'is_geofence_active')) {
                $table->boolean('is_geofence_active')->default(true)->after('radius_meters');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_settings', 'is_geofence_active')) {
                $table->dropColumn('is_geofence_active');
            }
        });
    }
};