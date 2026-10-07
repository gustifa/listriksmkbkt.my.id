<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom ke tabel exams
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'show_correct_answer')) {
                $table->boolean('show_correct_answer')->default(false)->after('allow_review');
            }
        });

        // 2. Bypass auto-increment sequence yang bermasalah dengan mengambil ID tertinggi + 1
        $maxId = DB::table('migrations')->max('id') ?? 0;
        $nextId = $maxId + 1;

        // 3. Catat manual ke tabel migrations dengan ID yang pasti unik
        DB::table('migrations')->insert([
            'id'        => $nextId,
            'migration' => '2026_10_07_225104_add_show_correct_answer_to_exams_table',
            'batch'     => (DB::table('migrations')->max('batch') ?? 0) + 1,
        ]);

        // 4. Perbarui sequence PostgreSQL ke ID baru agar migration berikutnya lancar
        DB::statement("SELECT setval('migrations_id_seq', {$nextId})");
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('show_correct_answer');
        });
    }
};
