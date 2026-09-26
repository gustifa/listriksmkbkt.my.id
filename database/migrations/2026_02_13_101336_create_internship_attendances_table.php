<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('internship_id')->constrained('internships')->onDelete('cascade');
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');

            $table->date('date'); // Tanggal Absen
            $table->time('time'); // Jam Absen

            $table->enum('status', ['present', 'sick', 'permit', 'alpha'])->default('present'); // Hadir, Sakit, Izin
            $table->text('activity_log')->nullable(); // Jurnal Kegiatan Harian

            // Bukti Kehadiran
            $table->string('photo_path')->nullable(); // Foto Selfie di Lokasi
            $table->string('latitude')->nullable();   // Koordinat Lokasi
            $table->string('longitude')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_attendances');
    }
};
