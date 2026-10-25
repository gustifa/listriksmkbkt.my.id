<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $relatedTables = ['classrooms', 'teachers', 'subjects', 'users'];

        // 1. Lepas semua Foreign Key constraint lama di tabel terikat yang mengarah ke majors
        foreach ($relatedTables as $table) {
            DB::statement("
                DO $$ 
                DECLARE r RECORD;
                BEGIN 
                    FOR r IN (
                        SELECT tc.constraint_name 
                        FROM information_schema.table_constraints tc
                        JOIN information_schema.key_column_usage kcu 
                          ON tc.constraint_name = kcu.constraint_name
                         AND tc.table_schema = kcu.table_schema
                        WHERE tc.constraint_type = 'FOREIGN KEY'
                          AND tc.table_name = '{$table}' 
                          AND kcu.column_name = 'major_id'
                    ) LOOP
                        EXECUTE 'ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS ' || quote_ident(r.constraint_name);
                    END LOOP;
                END $$;
            ");
        }

        // 2. Buat kolom temp_id (UUID) di tabel majors
        DB::statement('ALTER TABLE majors ADD COLUMN temp_id uuid DEFAULT gen_random_uuid();');

        // 3. Buat kolom temp_major_id (UUID) & salin data relasi HANYA JIKA major_id ada
        foreach ($relatedTables as $table) {
            DB::statement("
                DO $$ 
                BEGIN 
                    IF EXISTS (
                        SELECT 1 FROM information_schema.columns 
                        WHERE table_name = '{$table}' AND column_name = 'major_id'
                    ) THEN 
                        ALTER TABLE {$table} ADD COLUMN temp_major_id uuid;
                        
                        -- UPDATE hanya dieksekusi jika major_id benar-benar ada
                        EXECUTE 'UPDATE {$table} SET temp_major_id = majors.temp_id FROM majors WHERE {$table}.major_id = majors.id';
                    END IF;
                END $$;
            ");
        }

        // 4. Ganti kolom id lama di tabel majors dengan UUID baru
        DB::statement('ALTER TABLE majors DROP CONSTRAINT IF EXISTS majors_pkey CASCADE;');
        DB::statement('ALTER TABLE majors DROP COLUMN id;');
        DB::statement('ALTER TABLE majors RENAME COLUMN temp_id TO id;');
        DB::statement('ALTER TABLE majors ADD CONSTRAINT majors_pkey PRIMARY KEY (id);');

        // 5. Ganti kolom major_id lama di tabel terikat dengan UUID baru
        foreach ($relatedTables as $table) {
            DB::statement("
                DO $$ 
                BEGIN 
                    IF EXISTS (
                        SELECT 1 FROM information_schema.columns 
                        WHERE table_name = '{$table}' AND column_name = 'major_id'
                    ) THEN 
                        ALTER TABLE {$table} DROP COLUMN major_id;
                        ALTER TABLE {$table} RENAME COLUMN temp_major_id TO major_id;
                    END IF;
                END $$;
            ");
        }
    }

    public function down(): void
    {
        // Rollback tidak disarankan untuk migrasi ini.
    }
};