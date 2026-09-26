<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tambahkan relasi jurusan ke Guru
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('major_id')
                  ->nullable() // Nullable karena mungkin ada guru mapel umum (Matematika, dll)
                  ->after('id')
                  ->constrained('majors')
                  ->onDelete('set null');
        });

        // Tambahkan relasi jurusan ke Kelas
        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('major_id')
                  ->nullable()
                  ->after('name')
                  ->constrained('majors')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['major_id']);
            $table->dropColumn('major_id');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['major_id']);
            $table->dropColumn('major_id');
        });
    }
};