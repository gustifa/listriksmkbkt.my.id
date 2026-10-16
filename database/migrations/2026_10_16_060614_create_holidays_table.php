<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PERBAIKAN AUTOMATIS SEQUENCE POSTGRESQL AGAR TIDAK DUPLICATE KEY
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('migrations_id_seq', (SELECT COALESCE(MAX(id), 0) + 1 FROM migrations), false);");
        }

        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Penggunaan UUID
            $table->string('title'); // Nama Libur (misal: "Hari Raya Idul Fitri")
            $table->date('start_date'); // Tanggal Mulai Libur
            $table->date('end_date'); // Tanggal Selesai Libur
            $table->string('type')->default('nasional'); // Tipe: nasional, sekolah, atau khusus
            $table->boolean('is_active')->default(true); // Status aktif/tidaknya aturan libur
            $table->text('description')->nullable(); // Keterangan tambahan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};