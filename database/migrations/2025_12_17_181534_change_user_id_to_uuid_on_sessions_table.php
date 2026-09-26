<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Hapus kolom user_id yang lama (bigint)
            $table->dropColumn('user_id');
        });

        Schema::table('sessions', function (Blueprint $table) {
            // Tambahkan kolom user_id baru dengan tipe UUID
            $table->uuid('user_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        // Mengembalikan ke tipe bigint jika rollback diperlukan (ini mungkin butuh penyesuaian)
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->index(); // Kembali ke foreignId (bigint)
        });
    }
};
