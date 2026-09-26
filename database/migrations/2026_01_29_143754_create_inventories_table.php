<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi ke Tabel Ruangan (Wajib, karena alat ada di ruangan tertentu)
            $table->foreignUuid('room_id')->constrained('rooms')->onDelete('cascade');

            $table->string('name'); // Nama Barang (Obeng, Tang, Multimeter)
            $table->string('code')->unique(); // Kode Barang (INV-BL01-001)
            $table->string('brand')->nullable(); // Merk

            // Kategori: Alat (Aset Tetap), Bahan (Habis Pakai), Mesin (Aset Berat)
            $table->enum('category', ['alat', 'bahan', 'mesin'])->default('alat');

            $table->integer('quantity')->default(0); // Jumlah
            $table->string('unit')->default('pcs'); // Satuan (pcs, set, unit, kg)

            // Kondisi Barang
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');

            $table->date('purchase_date')->nullable(); // Tanggal Beli/Pengadaan
            $table->text('description')->nullable(); // Keterangan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
