<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel pivot exam_teacher jika belum ada
        if (!Schema::hasTable('exam_teacher')) {
            Schema::create('exam_teacher', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
                $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['exam_id', 'teacher_id']);
            });
        }

        // 2. Bypass sequence auto-increment PostgreSQL
        $maxId = DB::table('migrations')->max('id') ?? 0;
        $nextId = $maxId + 1;

        DB::table('migrations')->updateOrInsert(
            ['migration' => '2026_10_09_000620_create_exam_teacher_table'],
            [
                'id'    => $nextId,
                'batch' => (DB::table('migrations')->max('batch') ?? 0) + 1,
            ]
        );

        try {
            DB::statement("SELECT setval('migrations_id_seq', {$nextId})");
        } catch (\Exception $e) {
            // Abaikan jika bukan PostgreSQL
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_teacher');
    }
};