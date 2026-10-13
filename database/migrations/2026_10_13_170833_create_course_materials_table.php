<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('course_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignUuid('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignUuid('classroom_id')->nullable()->constrained('classrooms')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('course_materials');
    }
};