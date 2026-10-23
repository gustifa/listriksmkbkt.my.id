<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom relasi jika belum ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'major_id')) {
                $table->foreignUuid('major_id')->nullable()->constrained('majors')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'program_id')) {
                $table->foreignUuid('program_id')->nullable()->constrained('programs')->nullOnDelete();
            }
        });

        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'program_id')) {
                $table->foreignUuid('program_id')->nullable()->constrained('programs')->nullOnDelete();
            }
        });

        // Helper closure untuk membuat indeks secara aman tanpa error jika sudah ada
        $createIndexSafely = function ($sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // Abaikan jika error disebabkan karena indeks sudah ada
            }
        };

        // 2. Buat Indeks
        $createIndexSafely('CREATE INDEX idx_users_jenis_major ON public.users(jenis_user, major_id)');
        $createIndexSafely('CREATE INDEX idx_subjects_major ON public.subjects(major_id)');
        $createIndexSafely('CREATE INDEX idx_classrooms_major ON public.classrooms(major_id)');
        $createIndexSafely('CREATE INDEX idx_students_classroom ON public.students(classroom_id)');
        $createIndexSafely('CREATE INDEX idx_teachers_major ON public.teachers(major_id)');
        $createIndexSafely('CREATE INDEX idx_schedules_classroom ON public.schedules(classroom_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropIndexSafely = function ($sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // Abaikan jika indeks tidak ditemukan
            }
        };

        // Hapus indeks
        $dropIndexSafely('DROP INDEX idx_schedules_classroom');
        $dropIndexSafely('DROP INDEX idx_teachers_major');
        $dropIndexSafely('DROP INDEX idx_students_classroom');
        $dropIndexSafely('DROP INDEX idx_classrooms_major');
        $dropIndexSafely('DROP INDEX idx_subjects_major');
        $dropIndexSafely('DROP INDEX idx_users_jenis_major');

        // Hapus kolom & foreign key
        Schema::table('subjects', function (Blueprint $table) {
            if (Schema::hasColumn('subjects', 'program_id')) {
                $table->dropForeign(['program_id']);
                $table->dropColumn('program_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'major_id')) {
                $table->dropForeign(['major_id']);
                $table->dropColumn('major_id');
            }
            if (Schema::hasColumn('users', 'program_id')) {
                $table->dropForeign(['program_id']);
                $table->dropColumn('program_id');
            }
        });
    }
};