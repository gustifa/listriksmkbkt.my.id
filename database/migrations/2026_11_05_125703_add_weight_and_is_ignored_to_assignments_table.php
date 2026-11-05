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
        Schema::table('assignments', function (Blueprint $table) {
            // Menambahkan kolom weight (default 1) dan is_ignored (default false)
            $table->decimal('weight', 5, 2)->default(1)->after('max_score');
            $table->boolean('is_ignored')->default(false)->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['weight', 'is_ignored']);
        });
    }
};