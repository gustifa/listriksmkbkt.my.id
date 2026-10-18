<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Hapus constraint lama dan tambahkan 'berhalangan'
        DB::statement("ALTER TABLE prayer_attendances DROP CONSTRAINT IF EXISTS prayer_attendances_status_check;");
        DB::statement("ALTER TABLE prayer_attendances ADD CONSTRAINT prayer_attendances_status_check CHECK (status IN ('hadir', 'izin', 'sakit', 'alpha', 'berhalangan'));");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE prayer_attendances DROP CONSTRAINT IF EXISTS prayer_attendances_status_check;");
        DB::statement("ALTER TABLE prayer_attendances ADD CONSTRAINT prayer_attendances_status_check CHECK (status IN ('hadir', 'izin', 'sakit', 'alpha'));");
    }
};