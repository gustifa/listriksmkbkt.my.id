<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_timelines', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul Kegiatan (Misal: Pendaftaran)
            $table->text('description')->nullable(); // Detail
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming'); // Status manual atau otomatis
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_timelines');
    }
};