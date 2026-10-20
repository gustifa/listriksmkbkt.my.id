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
        Schema::create('ukk_units', function (Blueprint $table) {
            // $table->uuid('id')->primary();
            // $table->foreignUuid('ukk_package_id')->constrained('ukk_packages')->onDelete('cascade');
            // $table->string('unit_code'); // KODE UNIT
            // $table->string('unit_title'); // JUDUL UNIT
            // $table->text('element_name'); // ELEMEN
            // $table->timestamps();

            $table->uuid('id')->primary();
            $table->foreignUuid('ukk_package_id')->constrained('ukk_packages')->onDelete('cascade');
            $table->string('unit_code'); // KODE UNIT
            $table->string('unit_title'); // JUDUL UNIT
            $table->string('job_position')->nullable(); // JABATAN KERJA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ukk_units');
    }
};
