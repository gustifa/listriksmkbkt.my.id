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
        Schema::create('ukk_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('major_code'); // Contoh: TITL, TPTUP
            $table->string('package_name'); // Contoh: Paket 1 - Instalasi Tenaga Listrik
            $table->string('academic_year'); // Contoh: 2026/2027
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ukk_packages');
    }
};
