<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_permits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->date('date');

            // WAKTU
            $table->dateTime('time_out')->nullable(); // Waktu Izin Keluar
            $table->dateTime('time_in')->nullable();  // Waktu Kembali (Null jika belum kembali)

            // DETAIL
            $table->string('reason'); // Toilet, UKS, BK, Osis, Pulang, Lainnya
            $table->text('description')->nullable(); // Keterangan tambahan
            $table->string('status')->default('active'); // active (sedang diluar), returned (sudah kembali), closed (pulang)

            // BUKTI TEKNIS
            $table->string('method')->default('barcode'); // barcode, face, manual
            $table->string('image_evidence')->nullable(); // Foto wajah saat izin
            $table->string('recorded_by')->nullable(); // Petugas piket (jika ada)

            $table->timestamps();

            // Index untuk pencarian cepat hari ini
            $table->index(['student_id', 'date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_permits');
    }
};
