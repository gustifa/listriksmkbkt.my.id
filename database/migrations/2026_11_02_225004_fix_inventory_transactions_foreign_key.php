<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Hapus transaksi lama yang item_id nya TIDAK ADA di tabel inventories
        // Ini mencegah error Foreign Key Violation saat constraint baru diterapkan
        DB::statement("
            DELETE FROM inventory_transactions 
            WHERE item_id NOT IN (SELECT id FROM inventories)
        ");

        // 2. Hapus constraint Foreign Key lama yang mengarah ke tabel items (jika ada)
        DB::statement("
            ALTER TABLE inventory_transactions 
            DROP CONSTRAINT IF EXISTS inventory_transactions_item_id_foreign
        ");

        // 3. Pasang Foreign Key baru yang mengarah langsung ke tabel inventories
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreign('item_id')
                  ->references('id')
                  ->on('inventories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE inventory_transactions 
            DROP CONSTRAINT IF EXISTS inventory_transactions_item_id_foreign
        ");

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreign('item_id')
                  ->references('id')
                  ->on('items')
                  ->onDelete('cascade');
        });
    }
};