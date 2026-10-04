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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // Contoh: "Jam 1", "Jam 2", "Istirahat I"
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['lesson', 'break'])->default('lesson');
            $table->integer('sort_order')->default(0); // Urutan jam
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
