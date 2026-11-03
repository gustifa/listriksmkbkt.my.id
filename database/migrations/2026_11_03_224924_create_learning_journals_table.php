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
        Schema::create('learning_journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignUuid('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            
            $table->date('date');
            $table->string('topic', 255); // Materi / KD
            $table->text('activities')->nullable(); // Kegiatan Pembelajaran
            $table->text('obstacles')->nullable(); // Hambatan / Catatan KBM
            
            $table->timestamps();

            // Memastikan 1 kelas & 1 mapel hanya punya 1 jurnal per tanggal
            $table->unique(['classroom_id', 'subject_id', 'date'], 'unique_journal_per_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_journals');
    }
};