<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pastikan ekstensi UUID aktif di PostgreSQL
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        // 2. Lepas Foreign Key Constraints yang terikat ke majors_pkey
        DB::statement('ALTER TABLE majors DROP CONSTRAINT IF EXISTS majors_pkey CASCADE;');

        // 3. Ubah tipe kolom id pada majors menjadi UUID (Generate UUID baru untuk data yang ada)
        DB::statement('ALTER TABLE majors ALTER COLUMN id DROP DEFAULT;');
        DB::statement('ALTER TABLE majors ALTER COLUMN id TYPE uuid USING uuid_generate_v4();');
        DB::statement('ALTER TABLE majors ADD CONSTRAINT majors_pkey PRIMARY KEY (id);');

        // 4. Ubah tipe kolom major_id di tabel terkait menjadi UUID tanpa menghapus kolom
        $relatedTables = ['classrooms', 'teachers', 'subjects', 'users'];

        foreach ($relatedTables as $table) {
            DB::statement("
                DO $$ 
                BEGIN 
                    IF EXISTS (
                        SELECT 1 FROM information_schema.columns 
                        WHERE table_name = '{$table}' AND column_name = 'major_id'
                    ) THEN 
                        ALTER TABLE {$table} ALTER COLUMN major_id TYPE uuid USING NULL;
                    END IF;
                END $$;
            ");
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE majors DROP CONSTRAINT IF EXISTS majors_pkey CASCADE;');
        DB::statement('ALTER TABLE majors ALTER COLUMN id TYPE int8 USING NULL;');
        DB::statement('ALTER TABLE majors ADD CONSTRAINT majors_pkey PRIMARY KEY (id);');
    }
};