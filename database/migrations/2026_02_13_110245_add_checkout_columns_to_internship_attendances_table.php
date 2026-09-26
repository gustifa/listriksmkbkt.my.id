<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internship_attendances', function (Blueprint $table) {
            $table->time('check_out_time')->nullable()->after('time'); // Jam Pulang
            $table->string('photo_out_path')->nullable()->after('photo_path'); // Foto Pulang
        });
    }

    public function down(): void
    {
        Schema::table('internship_attendances', function (Blueprint $table) {
            $table->dropColumn(['check_out_time', 'photo_out_path']);
        });
    }
};
