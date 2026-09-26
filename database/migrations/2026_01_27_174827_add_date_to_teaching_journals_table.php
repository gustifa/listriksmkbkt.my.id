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
        Schema::table('teaching_journals', function (Blueprint $table) {
            $table->date('date')->nullable()->after('schedule_id')->index();
            // $table->date('date')->after('schedule_id')->index(); // Menambahkan kolom tanggal

            // Opsional: Hapus unique key lama jika ada, ganti dengan kombinasi schedule_id + date
            // $table->dropUnique(['schedule_id']);
            // $table->unique(['schedule_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teaching_journals', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }
};
