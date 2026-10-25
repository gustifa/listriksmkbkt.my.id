<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. PEMBERSIHAN DATA ORPHAN
        DB::statement("DELETE FROM attendances WHERE student_id NOT IN (SELECT id FROM students)");
        
        if (Schema::hasColumn('attendances', 'schedule_id')) {
            DB::statement("DELETE FROM attendances WHERE schedule_id IS NOT NULL AND schedule_id NOT IN (SELECT id FROM schedules)");
        }
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'teacher_id')) {
            DB::statement("DELETE FROM assignments WHERE teacher_id IS NOT NULL AND teacher_id NOT IN (SELECT id FROM teachers)");
        }
        if (Schema::hasTable('student_guidances') && Schema::hasColumn('student_guidances', 'student_id')) {
            DB::statement("DELETE FROM student_guidances WHERE student_id IS NOT NULL AND student_id NOT IN (SELECT id FROM students)");
        }
        if (Schema::hasTable('exams') && Schema::hasColumn('exams', 'subject_id')) {
            DB::statement("DELETE FROM exams WHERE subject_id IS NOT NULL AND subject_id NOT IN (SELECT id FROM subjects)");
        }

        // 2. AMAN DARI CONSTRAINT DROP (MENGGUNAKAN IF EXISTS POSTGRESQL)
        DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_student_id_foreign");
        DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_schedule_id_foreign");

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');

            if (Schema::hasColumn('attendances', 'schedule_id')) {
                $table->foreign('schedule_id')
                      ->references('id')
                      ->on('schedules')
                      ->onDelete('cascade');
            }
        });

        if (Schema::hasTable('assignments')) {
            DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS assignments_teacher_id_foreign");
            DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS assignments_classroom_id_foreign");
            
            Schema::table('assignments', function (Blueprint $table) {
                if (Schema::hasColumn('assignments', 'teacher_id')) {
                    $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
                }
                if (Schema::hasColumn('assignments', 'classroom_id')) {
                    $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('cascade');
                }
            });
        }

        if (Schema::hasTable('student_guidances')) {
            DB::statement("ALTER TABLE student_guidances DROP CONSTRAINT IF EXISTS student_guidances_student_id_foreign");
            DB::statement("ALTER TABLE student_guidances DROP CONSTRAINT IF EXISTS student_guidances_teacher_id_foreign");

            Schema::table('student_guidances', function (Blueprint $table) {
                if (Schema::hasColumn('student_guidances', 'student_id')) {
                    $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                }
                if (Schema::hasColumn('student_guidances', 'teacher_id')) {
                    $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
                }
            });
        }

        if (Schema::hasTable('exams')) {
            DB::statement("ALTER TABLE exams DROP CONSTRAINT IF EXISTS exams_subject_id_foreign");

            Schema::table('exams', function (Blueprint $table) {
                if (Schema::hasColumn('exams', 'subject_id')) {
                    $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
                }
            });
        }

        // 3. JADWAL (SCHEDULES) CONSTRAINTS
        if (Schema::hasTable('schedules')) {
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS schedules_teacher_id_foreign");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS schedules_classroom_id_foreign");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS schedules_room_id_foreign");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS unique_teacher_schedule_conflict");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS unique_classroom_schedule_conflict");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS unique_room_schedule_conflict");

            Schema::table('schedules', function (Blueprint $table) {
                if (Schema::hasColumn('schedules', 'teacher_id')) {
                    $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
                }
                if (Schema::hasColumn('schedules', 'classroom_id')) {
                    $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('cascade');
                }

                $table->unique(['teacher_id', 'day', 'start_time'], 'unique_teacher_schedule_conflict');
                $table->unique(['classroom_id', 'day', 'start_time'], 'unique_classroom_schedule_conflict');

                if (Schema::hasColumn('schedules', 'room_id')) {
                    $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
                    $table->unique(['room_id', 'day', 'start_time'], 'unique_room_schedule_conflict');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('schedules')) {
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS unique_teacher_schedule_conflict");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS unique_classroom_schedule_conflict");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS unique_room_schedule_conflict");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS schedules_room_id_foreign");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS schedules_teacher_id_foreign");
            DB::statement("ALTER TABLE schedules DROP CONSTRAINT IF EXISTS schedules_classroom_id_foreign");
        }

        if (Schema::hasTable('exams')) {
            DB::statement("ALTER TABLE exams DROP CONSTRAINT IF EXISTS exams_subject_id_foreign");
        }

        if (Schema::hasTable('student_guidances')) {
            DB::statement("ALTER TABLE student_guidances DROP CONSTRAINT IF EXISTS student_guidances_student_id_foreign");
            DB::statement("ALTER TABLE student_guidances DROP CONSTRAINT IF EXISTS student_guidances_teacher_id_foreign");
        }

        if (Schema::hasTable('assignments')) {
            DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS assignments_teacher_id_foreign");
            DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS assignments_classroom_id_foreign");
        }

        if (Schema::hasTable('attendances')) {
            DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_student_id_foreign");
            DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_schedule_id_foreign");
        }
    }
};