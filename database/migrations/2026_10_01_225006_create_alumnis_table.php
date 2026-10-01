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
        Schema::create('alumnis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->unique();
            $table->string('graduation_year'); // Contoh: "2025/2026"
            $table->date('graduation_date');
            $table->string('current_status')->nullable(); // Bekerja, Kuliah, Wirausaha, Belum Bekerja
            $table->string('company_or_campus')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnis');
    }
};
