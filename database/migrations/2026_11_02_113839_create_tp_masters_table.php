<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Master TP & Indikator Penilaian
        Schema::create('tp_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('major_id')->nullable()->constrained('majors')->cascadeOnDelete();
            $table->string('code'); // Contoh: TP1, TP2, TP3, TP4
            $table->string('title'); // Contoh: Soft Skills, POS & K3LH, Hard Skills
            $table->integer('max_score_per_item')->default(4); // Skala maks per item (misal: 4 atau 5)
            $table->json('indicators'); // Array JSON daftar indikator [{id: 1, name: "...", criteria: "..."}, ...]
            $table->timestamps();
        });

        // Tabel Nilai Observasi TP Siswa (Dinamis JSON)
        Schema::create('internship_tp_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('internship_id')->constrained('internships')->cascadeOnDelete();
            $table->json('scores'); // Menyimpan semua skor per TP & Indikator dalam bentuk JSON
            $table->json('notes')->nullable(); // Menyimpan catatan per TP
            $table->float('final_tp_score')->default(0); // Nilai Rerata Akhir TP
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_tp_scores');
    }
};