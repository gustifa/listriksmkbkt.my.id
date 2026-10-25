<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan pembersihan data orphan dan perbarui foreign key constraint ke CASCADE.
     */
    public function up(): void
    {
        // 1. Bersihkan data orphan (yang terlanjur NULL)
        DB::table('daily_attendances')->whereNull('student_id')->delete();
        DB::table('attendances')->whereNull('student_id')->delete();
        DB::table('student_violations')->whereNull('student_id')->delete();
        DB::table('student_guidances')->whereNull('student_id')->delete();
        DB::table('tahfiz_records')->whereNull('student_id')->delete();
        DB::table('ramadan_journals')->whereNull('student_id')->delete();
        DB::table('exam_sessions')->whereNull('student_id')->delete();

        // 2. Perbarui Foreign Key pada daily_attendances
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
        });

        // 3. Perbarui Foreign Key pada attendances (Mapel)
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
        });

        // 4. Perbarui Foreign Key pada student_violations
        Schema::table('student_violations', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
        });

        // 5. Perbarui Foreign Key pada student_guidances
        Schema::table('student_guidances', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
        });

        // 6. Perbarui Foreign Key pada tahfiz_records
        Schema::table('tahfiz_records', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
        });

        // 7. Perbarui Foreign Key pada ramadan_journals
        Schema::table('ramadan_journals', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
        });

        // 8. Perbarui Foreign Key pada exam_sessions
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
        });
    }

    /**
     * Kembalikan perubahan skema ke SET NULL (rollback).
     */
    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('set null');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('set null');
        });

        Schema::table('student_violations', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('set null');
        });

        Schema::table('student_guidances', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('set null');
        });

        Schema::table('tahfiz_records', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('set null');
        });

        Schema::table('ramadan_journals', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('set null');
        });

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('set null');
        });
    }
};