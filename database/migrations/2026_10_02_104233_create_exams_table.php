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
        Schema::create('exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->enum('type', ['pilihan_ganda', 'multiple_choice', 'essay', 'campuran']);
            $table->uuid('subject_id');
            $table->uuid('teacher_id');
            $table->uuid('academic_year_id')->nullable();
            $table->integer('duration_minutes');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');


            // Tabel Pivot Ujian Ke Kelas
            Schema::create('classroom_exam', function (Blueprint $table) {
                $table->uuid('exam_id');
                $table->uuid('classroom_id');

                $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
                $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('cascade');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_exam');
        Schema::dropIfExists('exams');
    }
};
