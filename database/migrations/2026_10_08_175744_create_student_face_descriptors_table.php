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
        Schema::create('student_face_descriptors', function (Blueprint $table) {
            // ID tabel berbasis UUID
            $table->uuid('id')->primary();

            // Relasi UUID ke tabel students (students.id)
            $table->foreignUuid('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');

            // Menyimpan array float descriptor (JSON) & label sampel
            $table->json('descriptor');
            $table->string('label', 50)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_face_descriptors');
    }
};
