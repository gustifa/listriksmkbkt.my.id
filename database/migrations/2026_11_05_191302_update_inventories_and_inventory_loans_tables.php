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
        // 1. Update Tabel inventories
        Schema::table('inventories', function (Blueprint $table) {
            $table->enum('type', ['alat', 'bahan'])->default('alat')->after('category');
            $table->integer('min_quantity')->default(5)->after('quantity')->comment('Alert batas minimal stok bahan');
            $table->enum('funding_source', ['BOS', 'DAK', 'APBD_SUMBAR', 'APBN', 'HIBAH'])->default('BOS')->after('condition');
            $table->integer('acquisition_year')->nullable()->after('funding_source');
            
            // Indeks untuk mempercepat pencarian Ajax
            $table->index(['type', 'category']);
        });

        // 2. Update Tabel inventory_loans
        Schema::table('inventory_loans', function (Blueprint $table) {
            $table->uuid('student_id')->nullable()->after('inventory_id');
            $table->uuid('teacher_id')->nullable()->after('student_id');
            $table->uuid('schedule_id')->nullable()->after('teacher_id');
            
            $table->timestamp('expected_return_date')->nullable()->after('loan_date');
            $table->enum('loan_type', ['peminjaman_alat', 'pemakaian_bahan'])->default('peminjaman_alat')->after('status');

            // Foreign Key Constraints (opsional namun direkomendasikan jika tabel relasi sudah ada)
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('set null');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('set null');

            // Indeks filter laporan
            $table->index(['status', 'loan_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex(['type', 'category']);
            $table->dropColumn(['type', 'min_quantity', 'funding_source', 'acquisition_year']);
        });

        Schema::table('inventory_loans', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['schedule_id']);
            $table->dropIndex(['status', 'loan_type']);
            $table->dropColumn(['student_id', 'teacher_id', 'schedule_id', 'expected_return_date', 'loan_type']);
        });
    }
};