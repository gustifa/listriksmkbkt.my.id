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
        Schema::create('tahfiz_records', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('student_id')->constrained('users')->cascadeOnDelete(); // ID Siswa
            // Jika menggunakan sintaks yang lebih singkat (direkomendasikan):
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            // $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete(); // ID Guru Penyimak
            // Cara paling rapi dan direkomendasikan:
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('surah_name'); // Nama Surah (Juz 30)
            $table->string('ayat')->nullable()->default('Lengkap'); // Ayat berapa sampai berapa, atau 'Lengkap'
            $table->enum('predicate', ['Mumtaz (A)', 'Jayyid Jiddan (B)', 'Jayyid (C)', 'Maqbul (D)', 'Mengulang'])->default('Mumtaz (A)'); // Nilai/Predikat
            $table->date('date'); // Tanggal Setoran
            $table->text('notes')->nullable(); // Catatan tambahan dari guru
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahfiz_records');
    }
};
