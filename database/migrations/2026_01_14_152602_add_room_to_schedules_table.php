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
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignUuid('room_id')
                  ->nullable()
                  ->after('classroom_id') // Letakkan setelah kolom classroom_id
                  ->constrained('rooms')
                  ->onDelete('set null'); // Jika ruangan dihapus, jadwal tetap ada tapi room_id jadi null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            //
        });
    }
};
