<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider'); // gemini, groq, openai, mistral, dll
            $table->string('label_name'); // Nama pembeda, misal: "Gemini Key Akun 1"
            $table->text('api_key'); // Tersimpan terenkripsi
            $table->integer('priority')->default(1); // Urutan pemanggilan (1, 2, 3...)
            $table->boolean('is_active')->default(true); // Status Aktif/Nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};