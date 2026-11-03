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
        Schema::create('learning_objectives', function (Blueprint $table) {
            // Primary Key UUID
            $table->uuid('id')->primary();

            // Relasi ke Mata Pelajaran (subjects) & Tahun Ajaran (academic_years)
            $table->uuid('subject_id');
            $table->uuid('academic_year_id')->nullable();

            // Data TP
            $table->string('code', 50);          // Contoh: "TP 1.1", "TP 2.3"
            $table->text('description');        // Judul / Uraian Tujuan Pembelajaran
            $table->string('phase', 10)->nullable(); // Fase Kurikulum (Misal: "Fase F")
            $table->integer('semester')->nullable(); // Semester (1 / 2)

            $table->timestamps();

            // Foreign Key Constraints (Sesuai Struktur PostgreSQL Anda)
            $table->foreign('subject_id')
                  ->references('id')
                  ->on('subjects')
                  ->onDelete('cascade');

            $table->foreign('academic_year_id')
                  ->references('id')
                  ->on('academic_years')
                  ->onDelete('cascade');

            // Indexing agar pencarian data saat rekap sangat cepat
            $table->index(['subject_id', 'academic_year_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_objectives');
    }
};