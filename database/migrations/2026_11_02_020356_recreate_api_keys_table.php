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
        // Hapus tabel lama jika ada agar bersih dari struktur UUID
        Schema::dropIfExists('api_keys');

        // Buat ulang tabel dengan Auto-Increment ID (Integer)
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id(); // Membuat kolom 'id' integer auto-increment (1, 2, 3...)
            $table->string('provider');
            $table->string('label_name')->nullable(); // Diperbolehkan null atau diisi default
            $table->text('api_key');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};