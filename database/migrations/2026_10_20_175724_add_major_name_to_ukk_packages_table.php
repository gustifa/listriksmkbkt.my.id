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
        Schema::table('ukk_packages', function (Blueprint $table) {
            // Menambahkan kolom major_name setelah major_code (nullable agar aman untuk data lama)
            $table->string('major_name')->nullable()->after('major_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ukk_packages', function (Blueprint $table) {
            $table->dropColumn('major_name');
        });
    }
};