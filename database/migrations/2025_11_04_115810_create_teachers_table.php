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
        Schema::create('teachers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Relasi ke Users (Untuk Login)
            $table->string('name');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            // Data Profil Guru
            $table->string('nip')->unique()->nullable(); // Nomor Induk Pegawai
            $table->string('gender')->nullable(); // L = Laki-laki, P = Perempuan
            $table->string('phone')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->string('education_level')->nullable(); // S1, S2, dll
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
