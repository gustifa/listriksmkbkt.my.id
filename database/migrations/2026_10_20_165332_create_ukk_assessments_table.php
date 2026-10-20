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
        // 3. Tabel Penjadwalan & Penilaian UKK Siswa
        Schema::create('ukk_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Sesuaikan dengan tipe data id pada tabel terkait di database Anda
            // Jika students dan users memakai UUID:
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignUuid('ukk_package_id')->constrained('ukk_packages')->onDelete('cascade');
            $table->foreignUuid('internal_examiner_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->foreignUuid('external_examiner_id')->nullable()->constrained('external_examiners')->onDelete('set null');
            
            /* Catatan: Jika tabel students/users Anda masih memakai ID auto-increment (bigint),
               gunakan foreignId() untuk student_id dan examiner_id:
               $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
               $table->foreignId('internal_examiner_id')->nullable()->constrained('users')->onDelete('set null');
               $table->foreignId('external_examiner_id')->nullable()->constrained('users')->onDelete('set null');
            */

            $table->date('exam_date');
            $table->decimal('score_internal', 5, 2)->nullable();
            $table->decimal('score_external', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('status', ['Belum Diuji', 'Kompeten', 'Belum Kompeten'])->default('Belum Diuji');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ukk_assessments');
    }
};
