<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom is_team_teaching ke tabel exams jika belum ada
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'is_team_teaching')) {
                $table->boolean('is_team_teaching')->default(false)->after('teacher_id');
            }
        });

        // 2. Bypass sequence auto-increment PostgreSQL
        $maxId = DB::table('migrations')->max('id') ?? 0;
        $nextId = $maxId + 1;

        // 3. Catat manual ke tabel migrations agar tidak bentrok
        DB::table('migrations')->updateOrInsert(
            ['migration' => '2026_10_09_000711_add_is_team_teaching_to_exams_table'],
            [
                'id'    => $nextId,
                'batch' => (DB::table('migrations')->max('batch') ?? 0) + 1,
            ]
        );

        // 4. Perbarui sequence PostgreSQL
        try {
            DB::statement("SELECT setval('migrations_id_seq', {$nextId})");
        } catch (\Exception $e) {
            // Abaikan jika bukan PostgreSQL
        }
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'is_team_teaching')) {
                $table->dropColumn('is_team_teaching');
            }
        });
    }
};
