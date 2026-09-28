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
        Schema::create('permit_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama alasan (misal: Toilet, UKS)
            $table->string('icon')->default('fas fa-door-open'); // Icon FontAwesome
            $table->string('color')->default('primary'); // Warna tombol Bootstrap (primary, danger, warning, dll)
            $table->boolean('is_active')->default(true); // Status aktif/non-aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_reasons');
    }
};
