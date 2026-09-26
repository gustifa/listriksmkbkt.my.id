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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->string('nomor_surat')->nullable(); // Boleh kosong jika bukan surat resmi
            $table->enum('kategori', ['Surat Masuk', 'Surat Keluar', 'SK', 'Jobsheet/Modul', 'Laporan', 'Lainnya']);
            $table->date('tanggal_dokumen');
            $table->string('file_path'); // Menyimpan lokasi file yang diupload
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
