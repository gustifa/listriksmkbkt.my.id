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
        // 1. Jalankan query update username dari email siswa
        DB::table('users')
            ->where(function ($query) {$query->whereNull('username')
                      ->orWhere('username', '');
            })
            ->where('email', 'LIKE', '%@siswa.sekolah.id')
            ->update([
                'username' => DB::raw("SPLIT_PART(email, '@', 1)")
            ]);

        // 2. Ambil ID & Batch tertinggi
        $maxId = DB::table('migrations')->max('id') ?? 0;
        $nextId =$maxId + 1;
        $currentBatch = (DB::table('migrations')->max('batch') ?? 0) + 1;
        $migrationName = '2026_10_13_134850_update_username_from_email_in_users_table';

        // 3. Catat manual log migration
        DB::table('migrations')->updateOrInsert(
            ['migration' => $migrationName],
            [
                'id'    => $nextId,
                'batch' => $currentBatch,
            ]
        );

        // 4. Sinkronkan sequence PostgreSQL
        try {
            DB::statement("SELECT setval(pg_get_serial_sequence('migrations', 'id'), {$nextId})");
        } catch (\Exception $e) {
            // Abaikan jika tidak terikat
        }

        // 5. COMMIT transaksi agar perubahan pada tabel `users` tersimpan permanen
        DB::commit();

        // 6. Selesaikan proses
        exit(0);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('email', 'LIKE', '%@siswa.sekolah.id')
            ->update([
                'username' => null
            ]);
    }
};