<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom uuid sementara
        Schema::table('settings', function (Blueprint $table) {
            $table->uuid('uuid_id')->nullable();
        });

        // 2. Isi kolom uuid_id untuk semua data yang sudah ada
        $settings = DB::table('settings')->get();
        foreach ($settings as $setting) {
            DB::table('settings')
                ->where('id', $setting->id)
                ->update(['uuid_id' => (string) Str::uuid()]);
        }

        // 3. Hapus primary key lama (id int8) dan jadikan uuid_id sebagai primary key baru
        Schema::table('settings', function (Blueprint $table) {
            $table->dropPrimary(['id']);
            $table->dropColumn('id');
        });

        // 4. Rename uuid_id menjadi id dan atur sebagai Primary Key
        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('uuid_id', 'id');
        });

        DB::statement('ALTER TABLE settings ADD PRIMARY KEY (id);');
    }

    public function down(): void
    {
        // Kebalikan jika rollback (opsional)
        Schema::table('settings', function (Blueprint $table) {
            $table->dropPrimary(['id']);
            $table->dropColumn('id');
            $table->bigIncrements('id')->first();
        });
    }
};