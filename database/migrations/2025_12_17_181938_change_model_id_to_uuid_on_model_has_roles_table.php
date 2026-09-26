<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Tambahkan ini

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            // 1. Hapus kunci asing dan indeks jika ada
            // Pastikan Anda tahu nama indeks lama (biasanya model_has_roles_model_id_foreign)
            // Jika Anda menggunakan Spatie/Permission, kolomnya mungkin model_id dan model_type
            $table->dropPrimary(['role_id', 'model_id', 'model_type']); // Drop primary key lama jika ada
            $table->dropColumn('model_id'); // Hapus kolom bigint lama
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            // 2. Tambahkan kolom model_id baru sebagai UUID
            $table->uuid('model_id');

            // 3. Tambahkan kembali Primary Key (jika Spatie/Permission menggunakannya)
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
    }

    public function down(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            // Mengembalikan primary key lama (jika ada)
            $table->dropPrimary(['role_id', 'model_id', 'model_type']);
            $table->dropColumn('model_id');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            // Mengembalikan ke bigInteger
            $table->bigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
    }
};
