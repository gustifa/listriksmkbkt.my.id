<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Membuat tabel programs
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Nama Program Keahlian
            $table->string('code')->unique(); // Kode Program (misal: TE)

            // PERBAIKAN: nullable() diletakkan SEBELUM constrained()
            $table->foreignUuid('program_teacher_id')
                  ->nullable()
                  ->constrained('teachers')
                  ->onDelete('set null'); // Set null jika data teacher dihapus

            $table->timestamps();
        });

        // 2. Menambahkan foreign key ke tabel majors
        Schema::table('majors', function (Blueprint $table) {
            // PERBAIKAN: Dibuat dalam satu baris, nullable() diletakkan sebelum constrained()
            $table->foreignUuid('program_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('programs')
                  ->onDelete('set null');

            // PERBAIKAN: nullable() diletakkan SEBELUM constrained()
            $table->foreignUuid('workshop_teacher_id')
                  ->nullable()
                  ->constrained('teachers')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // PERBAIKAN: Menghapus foreign key dan kolom di tabel majors saat rollback
        Schema::table('majors', function (Blueprint $table) {
            // Drop foreign key terlebih dahulu (Format: namaTabel_namaKolom_foreign)
            $table->dropForeign(['program_id']);
            $table->dropForeign(['workshop_teacher_id']);

            // Baru kemudian drop kolomnya
            $table->dropColumn(['program_id', 'workshop_teacher_id']);
        });

        // Hapus tabel programs
        Schema::dropIfExists('programs');
    }
};
