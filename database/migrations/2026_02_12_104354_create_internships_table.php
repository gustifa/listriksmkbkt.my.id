<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignUuid('industry_id')->constrained('industries')->onDelete('cascade');
            
            // Guru Pembimbing Sekolah (Opsional)
            $table->foreignUuid('advisor_id')->nullable()->constrained('teachers')->onDelete('set null');

            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('active');
            
            $table->timestamps();
            
            // Satu siswa hanya boleh aktif di 1 tempat PKL pada waktu bersamaan
            $table->unique(['student_id', 'industry_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};