<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // 1. Perbaiki Sequence PostgreSQL secara kompatibel untuk semua versi
        DB::unprepared("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relname = 'migrations_id_seq') THEN
                    CREATE SEQUENCE migrations_id_seq;
                END IF;
            END $$;

            ALTER TABLE migrations ALTER COLUMN id SET DEFAULT nextval('migrations_id_seq');
            SELECT setval('migrations_id_seq', COALESCE((SELECT MAX(id) FROM migrations), 0) + 1, false);
        ");

        // 2. Buat Tabel Master Tugas Tambahan
        if (!Schema::hasTable('additional_tasks')) {
            Schema::create('additional_tasks', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->integer('hours')->default(0);
                $table->string('type', 10)->default('TTU');
                $table->timestamps();
            });
        }

        // 3. Tambahkan Foreign Key di Tabel Teachers
        Schema::table('teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('teachers', 'additional_task_id')) {
                $table->foreignUuid('additional_task_id')
                      ->nullable()
                      ->after('tugas_tambahan')
                      ->constrained('additional_tasks')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'additional_task_id')) {
                $table->dropForeign(['additional_task_id']);
                $table->dropColumn('additional_task_id');
            }
        });

        Schema::dropIfExists('additional_tasks');
    }
};