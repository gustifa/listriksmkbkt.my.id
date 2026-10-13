<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->text('submission_text')->nullable();
            $table->string('file_path')->nullable();
            $table->dateTime('submitted_at');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->foreignUuid('graded_by')->nullable()->constrained('teachers')->onDelete('set null');
            $table->string('status', 50)->default('submitted');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('assignment_submissions');
    }
};