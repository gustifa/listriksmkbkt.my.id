<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixMigrationsSequence extends Command
{
    /**
     * Nama command yang dipanggil via terminal.
     */
    protected $signature = 'db:fix-migrations-sequence';

    /**
     * Deskripsi command.
     */
    protected $description = 'Memperbaiki sequence auto-increment id pada tabel migrations di PostgreSQL';

    /**
     * Eksekusi logika perintah.
     */
    public function handle()
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->error('Command ini hanya berlaku untuk PostgreSQL.');
            return 1;
        }

        try {
            DB::transaction(function () {
                // 1. Cek apakah sequence sudah ada, jika belum buat sequence baru
                $sequenceExists = DB::select("SELECT 1 FROM pg_class WHERE relname = 'migrations_id_seq'");
                if (empty($sequenceExists)) {
                    DB::statement("CREATE SEQUENCE migrations_id_seq;");
                }

                // 2. Set default value kolom id ke sequence
                DB::statement("ALTER TABLE public.migrations ALTER COLUMN id SET DEFAULT nextval('migrations_id_seq');");

                // 3. Sinkronkan nilai sequence dengan ID tertinggi saat ini
                DB::statement("SELECT setval('migrations_id_seq', COALESCE((SELECT MAX(id) FROM public.migrations), 0) + 1, false);");
            });

            $this->info('Berhasil! Sequence tabel migrations telah diperbaiki secara permanen.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Gagal memperbaikinya: ' . $e->getMessage());
            return 1;
        }
    }
}