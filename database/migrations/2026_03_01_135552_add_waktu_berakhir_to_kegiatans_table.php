<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            // Menambahkan kolom batas waktu (bisa kosong jika absen tanpa batas waktu)
            $table->dateTime('waktu_berakhir')->nullable()->after('tanggal');
        });
    }

    public function down()
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropColumn('waktu_berakhir');
        });
    }
};
