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
        Schema::table('inventories', function (Blueprint $table) {
            // Tambahkan kolom brand_model jika belum ada
            if (!Schema::hasColumn('inventories', 'brand_model')) {
                $table->string('brand_model')->nullable()->after('category');
            }

            // Tambahkan kolom min_quantity jika belum ada
            if (!Schema::hasColumn('inventories', 'min_quantity')) {
                $table->integer('min_quantity')->default(5)->after('quantity');
            }

            // Tambahkan kolom acquisition_year jika belum ada
            if (!Schema::hasColumn('inventories', 'acquisition_year')) {
                $table->integer('acquisition_year')->nullable()->after('funding_source');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['brand_model', 'min_quantity', 'acquisition_year']);
        });
    }
};