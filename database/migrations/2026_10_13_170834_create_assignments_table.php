<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignUuid('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignUuid('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->string('title');
            $table->text('instructions');
            $table->string('file_path')->nullable();
            $table->dateTime('due_date');
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('assignments');
    }
};