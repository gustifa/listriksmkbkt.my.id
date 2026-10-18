<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset sequence activity_log agar selalu di atas ID terbesar saat ini
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('activity_log', 'id'), 
                COALESCE((SELECT MAX(id) FROM activity_log), 0) + 1, 
                false
            );
        ");

        // Opsional: Reset sequence tabel migrations juga jika ada potensi out-of-sync
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('migrations', 'id'), 
                COALESCE((SELECT MAX(id) FROM migrations), 0) + 1, 
                false
            );
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak diperlukan tindakan rollback khusus
    }
};