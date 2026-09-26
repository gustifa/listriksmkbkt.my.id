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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Relasi ke Siswa & Jadwal (Keduanya sudah UUID)
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignUuid('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->date('date');
            $table->time('check_in_time');
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa']);
            $table->string('recorded_by')->nullable()->comment('Nama User/Device yang melakukan input');
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
