<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Nama Perusahaan/Instansi
            $table->string('sector')->nullable(); // Bidang Usaha
            $table->string('address'); // Alamat
            $table->string('contact_person')->nullable(); // Nama Pembimbing DU/DI
            $table->string('phone')->nullable(); // Kontak
            $table->integer('quota')->default(0); // Kuota maksimal siswa
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industries');
    }
};