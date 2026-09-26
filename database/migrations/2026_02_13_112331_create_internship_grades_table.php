<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('internship_id')->constrained('internships')->onDelete('cascade');
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignUuid('teacher_id')->constrained('teachers'); // Penilai (Guru Pembimbing)

            // ASPEK NON-TEKNIS (SOFT SKILLS)
            $table->integer('discipline')->default(0); // Disiplin
            $table->integer('teamwork')->default(0);   // Kerjasama
            $table->integer('initiative')->default(0); // Inisiatif
            $table->integer('responsibility')->default(0); // Tanggung Jawab

            // ASPEK TEKNIS (HARD SKILLS)
            $table->integer('technical_mastery')->default(0); // Penguasaan Materi
            $table->integer('work_quality')->default(0);      // Kualitas Kerja

            // NILAI AKHIR & CATATAN
            $table->decimal('final_score', 5, 2)->default(0);
            $table->text('notes')->nullable(); // Catatan Evaluasi

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_grades');
    }
};
