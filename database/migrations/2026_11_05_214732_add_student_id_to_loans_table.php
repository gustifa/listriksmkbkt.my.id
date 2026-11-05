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
        Schema::table('loans', function (Blueprint $table) {
            // 1. Ubah teacher_id menjadi nullable (opsional/bisa kosong)
            $table->uuid('teacher_id')->nullable()->change();

            // 2. Tambahkan student_id bertipe UUID agar sesuai dengan id pada tabel students
            $table->uuid('student_id')->nullable()->after('teacher_id');

            // 3. Tambahkan foreign key constraint ke tabel students
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
            $table->uuid('teacher_id')->nullable(false)->change();
        });
    }
};