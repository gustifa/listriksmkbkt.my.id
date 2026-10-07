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
        Schema::table('exams', function (Blueprint $table) {
            // Menambahkan kolom switch review jika belum ada
            if (!Schema::hasColumn('exams', 'allow_review')) {
                $table->boolean('allow_review')->default(true)->after('is_active');
            }
            if (!Schema::hasColumn('exams', 'show_correct_answer')) {
                $table->boolean('show_correct_answer')->default(false)->after('allow_review');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['allow_review', 'show_correct_answer']);
        });
    }
};