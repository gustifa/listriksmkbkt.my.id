<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah tipe data notifiable_id menjadi string/text agar kompatibel dengan UUID
        DB::statement('ALTER TABLE notifications ALTER COLUMN notifiable_id TYPE VARCHAR(255);');
    }

    public function down(): void
    {
        // Kembalikan ke integer jika di-rollback
        DB::statement('ALTER TABLE notifications ALTER COLUMN notifiable_id TYPE BIGINT USING notifiable_id::bigint;');
    }
};