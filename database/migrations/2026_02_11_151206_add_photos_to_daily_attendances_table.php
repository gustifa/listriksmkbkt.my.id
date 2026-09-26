<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            // Menambahkan kolom foto setelah kolom status
            $table->string('photo_in')->nullable()->after('status')->comment('Foto bukti saat datang');
            $table->string('photo_out')->nullable()->after('photo_in')->comment('Foto bukti saat pulang');
        });
    }

    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropColumn(['photo_in', 'photo_out']);
        });
    }
};
