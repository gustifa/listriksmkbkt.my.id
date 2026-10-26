<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sinkronkan sequence ke nilai ID tertinggi saat ini
        DB::statement("SELECT setval(pg_get_serial_sequence('jobs', 'id'), coalesce(max(id), 0) + 1, false) FROM jobs;");

        // 2. Ubah default column agar selalu mengambil nilai dari sequence secara otomatis
        DB::statement("ALTER TABLE jobs ALTER COLUMN id SET DEFAULT nextval(pg_get_serial_sequence('jobs', 'id'));");
    }

    public function down(): void
    {
        // Rollback opsional
    }
};