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
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
           // Foreign Key ke Users (Guru) juga harus UUID
            // Gunakan foreignUuid(), BUKAN foreignId()
            $table->foreignUuid('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignUuid('classroom_id')->constrained('classrooms')->onDelete('cascade');
            // $table->string('subject_name');
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('day'); // Senin, Selasa, dst
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
