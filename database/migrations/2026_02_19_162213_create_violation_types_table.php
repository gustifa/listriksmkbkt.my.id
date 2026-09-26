<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. MASTER JENIS PELANGGARAN
        Schema::create('violation_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Contoh: "Terlambat > 15 Menit", "Atribut Tidak Lengkap"
            $table->integer('points')->default(5); // Bobot poin
            $table->enum('category', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->timestamps();
        });

        // 2. RIWAYAT PELANGGARAN SISWA
        Schema::create('student_violations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignUuid('violation_type_id')->constrained('violation_types');
            
            $table->date('date');
            $table->text('note')->nullable(); // Kronologi singkat
            $table->foreignUuid('reported_by')->nullable()->constrained('users'); // Siapa yang lapor
            $table->timestamps();
        });

        // 3. JURNAL PEMBINAAN / KONSELING
        Schema::create('student_guidances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            
            // Guru yang membina (Walas/BK/Kaprog)
            $table->foreignUuid('teacher_id')->constrained('teachers'); 
            
            $table->date('date');
            $table->text('problem_summary'); // Rangkuman masalah
            $table->text('advice'); // Nasihat / Solusi yang diberikan
            $table->text('student_commitment')->nullable(); // Janji siswa
            $table->enum('status', ['open', 'monitoring', 'resolved', 'escalated'])->default('open');
            $table->string('role_context'); // 'wali_kelas', 'bk', 'kaprog'
            
            $table->string('photo_evidence')->nullable(); // Foto saat pembinaan (opsional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guidances');
        Schema::dropIfExists('student_violations');
        Schema::dropIfExists('violation_types');
    }
};