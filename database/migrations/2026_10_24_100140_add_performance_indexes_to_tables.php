<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Indeks pada Presensi Harian
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->index(['student_id', 'date'], 'idx_daily_att_student_date');
            $table->index('status', 'idx_daily_att_status');
        });

        // 2. Indeks pada Presensi Mata Pelajaran
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['student_id', 'date'], 'idx_att_student_date');
            $table->index(['schedule_id', 'date'], 'idx_att_schedule_date');
        });

        // 3. Indeks pada Sesi Ujian (CBT)
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->index(['exam_id', 'student_id', 'status'], 'idx_exam_sess_exam_student_status');
        });

        // 4. Indeks pada Jawaban Ujian
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->index(['exam_session_id', 'question_id'], 'idx_exam_ans_session_question');
        });

        // 5. Indeks pada Jadwal Pelajaran
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['classroom_id', 'day'], 'idx_schedules_classroom_day');
            $table->index(['teacher_id', 'day'], 'idx_schedules_teacher_day');
        });

        // 6. Indeks pada Log Aktivitas
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index(['causer_type', 'causer_id', 'created_at'], 'idx_act_log_causer_created');
            $table->index(['subject_type', 'subject_id'], 'idx_act_log_subject');
        });

        // 7. Indeks pada Log WhatsApp
       // UBAH BAGIAN INI:
        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_wa_logs_status_created');
            $table->index('recipient_number', 'idx_wa_logs_phone'); // ganti 'phone' dengan 'recipient_number'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropIndex('idx_daily_att_student_date');
            $table->dropIndex('idx_daily_att_status');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_att_student_date');
            $table->dropIndex('idx_att_schedule_date');
        });

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_exam_sess_exam_student_status');
        });

        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropIndex('idx_exam_ans_session_question');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_classroom_day');
            $table->dropIndex('idx_schedules_teacher_day');
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('idx_act_log_causer_created');
            $table->dropIndex('idx_act_log_subject');
        });

        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->dropIndex('idx_wa_logs_status_created');
            $table->dropIndex('idx_wa_logs_phone');
        });
    }
};
