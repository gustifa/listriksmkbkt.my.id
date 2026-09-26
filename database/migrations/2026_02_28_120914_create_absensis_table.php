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
        Schema::create('absensis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relasi ke tabel kegiatans
            // onDelete('cascade') memastikan jika kegiatan dihapus, absensi juga terhapus
            $table->foreignUuid('kegiatan_id')->constrained('kegiatans')->onDelete('cascade');
            
            // Relasi ke tabel users
            // onDelete('cascade') memastikan jika user dihapus, absensi juga terhapus
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            
            // Waktu saat user berhasil melakukan scan/absen
            $table->timestamp('waktu_hadir');
            
            $table->timestamps();

            // Constraint unique: Memastikan 1 user hanya bisa absen 1 kali di kegiatan yang sama
            $table->unique(['kegiatan_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};