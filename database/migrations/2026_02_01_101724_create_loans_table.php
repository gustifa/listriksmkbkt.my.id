<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Siapa yang meminjam (Guru)
            $table->foreignUuid('teacher_id')->constrained('teachers')->onDelete('cascade');
            
            // Barang apa yang dipinjam
            $table->foreignUuid('inventory_id')->constrained('inventories')->onDelete('cascade');
            
            $table->dateTime('borrow_date'); // Tanggal Pinjam
            $table->dateTime('return_date')->nullable(); // Tanggal Kembali (Null jika belum kembali)
            
            $table->enum('status', ['borrowed', 'returned', 'lost'])->default('borrowed');
            $table->integer('amount')->default(1); // Jumlah yang dipinjam
            $table->text('notes')->nullable(); // Keterangan (misal: untuk praktek kelas X)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};