<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            
            $table->date('date');
            $table->string('prayer_name'); // subuh, zuhur, ashar, maghrib, isya, dhuha
            $table->time('check_in_time');
            
            $table->enum('status', ['hadir', 'terlambat', 'udzur'])->default('hadir');
            $table->string('photo_evidence')->nullable(); // Foto bukti (opsional)
            $table->text('notes')->nullable();
            
            $table->timestamps();

            // Mencegah duplikasi absen sholat yang sama di hari yang sama
            $table->unique(['student_id', 'date', 'prayer_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_attendances');
    }
};