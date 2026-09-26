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
        Schema::create('teaching_assignments', function (Blueprint $table) {
            // $table->id();
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_id')->constrained('teachers')->onDelete('cascade');
            // $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignUuid('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->string('academic_year')->nullable(); // Tahun Ajaran (opsional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
