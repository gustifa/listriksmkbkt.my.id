<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID
            $table->string('code')->unique(); // Contoh: RPL, TKJ, AKL
            $table->string('name')->unique(); // Contoh: Matematika, Bahasa Inggris
            // Relasi ke Jurusan (Nullable: Jika null berarti Mapel Umum / Muatan Nasional)
            $table->foreignId('major_id')
                  ->nullable()
                  ->constrained('majors')
                  ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
};
