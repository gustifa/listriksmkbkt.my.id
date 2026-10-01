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
            // Menambahkan kolom status (default: active, opsi: active, graduated, dropped_out)
            $table->string('status')->default('active')->after('classroom_id');

            // Menambahkan kolom last_classroom_id sebagai foreign key yang merujuk ke tabel classrooms
            $table->foreignUuid('last_classroom_id')
                  ->nullable()
                  ->after('status')
                  ->constrained('classrooms')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Menghapus foreign key constraint dan kolom saat rollback
            $table->dropForeign(['last_classroom_id']);
            $table->dropColumn(['status', 'last_classroom_id']);
        });
    }
};
