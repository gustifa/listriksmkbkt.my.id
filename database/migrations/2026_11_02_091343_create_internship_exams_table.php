<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // UBAH BARIS INI: Gunakan foreignUuid() bukan foreignId()
            $table->foreignUuid('internship_id')->constrained('internships')->onDelete('cascade');
            // $table->foreignId('internship_id')->constrained('internships')->onDelete('cascade');
            $table->foreignUuid('examiner_id')->constrained('teachers')->onDelete('cascade');
            $table->date('exam_date');

            // Aspek Laporan (Sub Total: 60)
            $table->decimal('score_materi', 5, 2)->default(0);     // Maks 35
            $table->decimal('score_gambar', 5, 2)->default(0);     // Maks 10
            $table->decimal('score_sistematika', 5, 2)->default(0); // Maks 10
            $table->decimal('score_tampilan', 5, 2)->default(0);   // Maks 5
            $table->decimal('subtotal_laporan', 5, 2)->default(0);

            // Aspek Presentasi / Wawancara (Sub Total: 40)
            $table->decimal('score_pakaian', 5, 2)->default(0);    // Maks 5
            $table->decimal('score_bahasa', 5, 2)->default(0);     // Maks 10
            $table->decimal('score_penguasaan', 5, 2)->default(0); // Maks 15
            $table->decimal('score_jawaban', 5, 2)->default(0);    // Maks 10
            $table->decimal('subtotal_presentasi', 5, 2)->default(0);

            // Total Akhir (Maks 100)
            $table->decimal('total_score', 5, 2)->default(0);
            
            $table->enum('status', ['scheduled', 'passed', 'revision', 'failed'])->default('scheduled');
            $table->text('revision_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_exams');
    }
};