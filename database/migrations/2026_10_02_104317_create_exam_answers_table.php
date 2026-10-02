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
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exam_session_id');
            $table->uuid('question_id');
            $table->json('answer')->nullable(); // Pilihan: ["A"] atau ["A", "B"], Essay: ["Jawaban teks..."]
            $table->boolean('is_correct')->nullable();
            $table->decimal('score_given', 5, 2)->default(0); // ✅ Gunakan 'decimal'
            $table->timestamps();

            $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
