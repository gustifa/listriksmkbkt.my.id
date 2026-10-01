<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('year'); // Contoh: "2025/2026"
            $table->enum('semester', ['ganjil', 'genap']);
            $table->boolean('is_active')->default(false); // Menandai semester/tahun ajaran aktif
            $table->timestamps();
        });

        // Tambahkan relasi ke tabel schedules (Jadwal) & teaching_assignments
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
        Schema::dropIfExists('academic_years');
    }
};
