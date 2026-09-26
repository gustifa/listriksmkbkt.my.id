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
        Schema::table('teachers', function (Blueprint $table) {
            // Menambahkan kolom baru setelah kolom 'nip' (jika ada) atau di akhir
            $table->string('pangkat')->nullable()->after('nip')->comment('Contoh: Penata Muda Tk. I');
            $table->string('golongan')->nullable()->after('pangkat')->comment('Contoh: III/b');
            $table->string('tugas_tambahan')->nullable()->after('golongan')->comment('Contoh: Kepala Bengkel, Wali Kelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
             $table->dropColumn(['pangkat', 'golongan', 'tugas_tambahan']);
        });
    }
};
