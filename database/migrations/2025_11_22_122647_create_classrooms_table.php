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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID
            $table->string('name')->unique(); // Contoh: XII RPL 1
            // Wali Kelas (Relasi ke tabel teachers)
            $table->foreignUuid('homeroom_teacher_id')
                  ->nullable()
                  ->constrained('teachers')
                  ->onDelete('set null');

            // Guru BK (Relasi ke tabel teachers)
            $table->foreignUuid('counseling_teacher_id')
                  ->nullable()
                  ->constrained('teachers')
                  ->onDelete('set null');

            // Ketua Kelas (Relasi ke tabel students)
            // Menggunakan foreignUuid karena tabel students Anda menggunakan UUID
            $table->foreignUuid('class_leader_id')
                  ->nullable()
                  ->constrained('students')
                  ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
