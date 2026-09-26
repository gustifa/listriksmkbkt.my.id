<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            // Terhubung dengan jadwal pelajaran yang sedang berlangsung
            // $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->uuid('schedule_id');

            // 2. Definisikan foreign key. (Opsional: gunakan constrained jika nama tabelnya sesuai)
            $table->foreign('schedule_id')
                ->references('id')->on('schedules')
                ->onDelete('cascade');

            $table->string('topic')->nullable(); // Materi / Topik
            $table->text('activity')->nullable(); // Kegiatan Pembelajaran
            $table->text('notes')->nullable(); // Catatan Guru (misal: PR, kendala)
            $table->string('photo_evidence')->nullable(); // Foto bukti (opsional)

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teaching_journals');
    }
};
