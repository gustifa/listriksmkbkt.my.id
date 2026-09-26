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
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Menggunakan UUID agar konsisten dengan tabel lain

            $table->string('name'); // Contoh: Bengkel Listrik 1
            $table->string('code')->unique(); // Contoh: BL-01
            $table->enum('type', ['teori', 'labor', 'bengkel', 'lapangan', 'lainnya'])->default('teori');
            $table->string('location')->nullable(); // Contoh: Gedung A Lt. 2
            $table->integer('capacity')->nullable(); // Kapasitas siswa
            $table->text('description')->nullable(); // Keterangan tambahan

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
