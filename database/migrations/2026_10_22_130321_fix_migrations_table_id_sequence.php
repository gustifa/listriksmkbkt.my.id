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
        if (DB::getDriverName() === 'pgsql') {
            // 1. Buat sequence jika belum ada (tanpa klausa IF NOT EXISTS)
            try {
                DB::statement("CREATE SEQUENCE migrations_id_seq;");
            } catch (\Throwable $e) {
                // Abaikan error jika sequence sudah ada
            }

            // 2. Pasang sequence sebagai default value kolom id
            try {
                DB::statement("ALTER TABLE public.migrations ALTER COLUMN id SET DEFAULT nextval('migrations_id_seq');");
            } catch (\Throwable $e) {
                // Abaikan jika default sudah terpasang
            }

            // 3. Sinkronkan nilai sequence dengan ID tertinggi saat ini
            try {
                DB::statement("SELECT setval('migrations_id_seq', COALESCE((SELECT MAX(id) FROM public.migrations), 0) + 1, false);");
            } catch (\Throwable $e) {
                // Abaikan jika terjadi kendala penyesuaian nilai
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            try {
                DB::statement("ALTER TABLE public.migrations ALTER COLUMN id DROP DEFAULT;");
                DB::statement("DROP SEQUENCE migrations_id_seq;");
            } catch (\Throwable $e) {
                // Abaikan jika tidak ada
            }
        }
    }
};