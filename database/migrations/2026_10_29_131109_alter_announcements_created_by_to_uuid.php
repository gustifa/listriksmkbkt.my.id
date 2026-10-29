<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mengubah tipe kolom created_by di PostgreSQL menjadi VARCHAR/UUID agar cocok dengan Auth::id()
        DB::statement('ALTER TABLE announcements ALTER COLUMN created_by TYPE VARCHAR(36) USING created_by::VARCHAR;');
    }

    public function down(): void
    {
        // Rollback kembali ke tipe bigint jika diperlukan
        DB::statement('ALTER TABLE announcements ALTER COLUMN created_by TYPE BIGINT USING (created_by::integer);');
    }
};