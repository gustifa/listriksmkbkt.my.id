<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inventory_id')->constrained('inventories')->onDelete('cascade');

            $table->string('borrower_name'); // Nama Peminjam (Siswa/Guru)
            $table->integer('quantity'); // Jumlah yang dipinjam

            $table->dateTime('loan_date'); // Tanggal Pinjam
            $table->dateTime('return_date')->nullable(); // Tanggal Kembali (diisi saat dikembalikan)

            $table->enum('status', ['dipinjam', 'kembali', 'hilang'])->default('dipinjam');
            $table->text('notes')->nullable(); // Catatan kondisi saat pinjam/kembali

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_loans');
    }
};
