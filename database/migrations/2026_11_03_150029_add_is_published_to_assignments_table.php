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
            // Menambahkan kolom is_published bertipe boolean dengan default false (tidak tampil di siswa)
            $table->boolean('is_published')->default(false)->after('instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Drop kolom is_published jika migration di-rollback
            $table->dropColumn('is_published');
        });
    }
};