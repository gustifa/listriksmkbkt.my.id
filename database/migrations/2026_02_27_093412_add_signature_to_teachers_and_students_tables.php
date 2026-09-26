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
        // Menambahkan kolom signature di tabel teachers
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('signature')->nullable()->after('name')->comment('Path file tanda tangan guru');
        });

        // Menambahkan kolom signature di tabel students
        Schema::table('students', function (Blueprint $table) {
            $table->string('signature')->nullable()->after('name')->comment('Path file tanda tangan siswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('signature');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }
};
