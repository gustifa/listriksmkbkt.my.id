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
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exam_id');
            $table->enum('question_type', ['single', 'multiple', 'essay']); // single = Pilihan Ganda, multiple = Centang Banyak
            $table->text('question_text');
            $table->json('options')->nullable(); // format JSON: [{"key": "A", "text": "..."}, ...]
            $table->json('correct_answer')->nullable(); // Pilihan ganda: ["A"], Multiple: ["A", "C"], Essay: null
            $table->integer('score_weight')->default(1);
            $table->timestamps();

            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
