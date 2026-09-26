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
        Schema::create('izin_siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->date('date');
            $table->text('reason'); // Alasan spesifik dari WA
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('wa_number')->nullable(); // Nomor WA pengirim untuk auto-reply
            $table->timestamps();

            // Relasi ke tabel students yang ada di listrik_1.sql
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_siswas');
    }
};
