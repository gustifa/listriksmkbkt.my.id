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
        Schema::table('daily_attendances', function (Blueprint $table) {
            // Menambahkan kolom academic_year_id bertipe UUID (nullable)
            // dan menghubungkannya dengan tabel academic_years
            $table->foreignUuid('academic_year_id')
                  ->nullable()
                  ->constrained('academic_years')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            // Hapus foreign key dan kolom jika migration di-rollback
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
