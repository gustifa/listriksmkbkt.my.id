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
        Schema::create('ukk_unit_elements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ukk_unit_id')->constrained('ukk_units')->onDelete('cascade');
            $table->text('element_name'); // ELEMEN
            $table->enum('category_type', ['UTAMA', 'PENDUKUNG'])->default('UTAMA'); // UTAMA / PENDUKUNG
            $table->text('work_step')->nullable(); // LANGKAH KERJA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ukk_unit_elements');
    }
};
