<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->date('date');
            $table->time('arrival_time')->nullable();   // Jam Datang
            $table->time('departure_time')->nullable(); // Jam Pulang
            // Status kehadiran harian
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])->default('alpa');
            $table->string('recorded_by')->nullable()->comment('Nama User/Device yang melakukan input');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_attendances');
    }
};
