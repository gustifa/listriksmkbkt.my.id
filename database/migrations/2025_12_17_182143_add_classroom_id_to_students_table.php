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
        Schema::table('students', function (Blueprint $table) {
            // Tambahkan kolom classroom_id dengan tipe UUID (karena classroom.id adalah UUID)
            // Tambahkan index agar pencarian cepat
            $table->uuid('classroom_id')->nullable()->after('name')->index();

            // (Opsional) Tambahkan Foreign Key Constraint
            // Pastikan tabel 'classrooms' sudah ada sebelum migration ini dieksekusi
            $table->foreign('classroom_id')
                  ->references('id')
                  ->on('classrooms')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Hapus foreign key (jika dibuat)
            $table->dropForeign(['classroom_id']);

            // Hapus kolom
            $table->dropColumn('classroom_id');
        });
    }
};
