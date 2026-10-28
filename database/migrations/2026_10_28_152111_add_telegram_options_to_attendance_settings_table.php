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
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_settings', 'send_photo_telegram')) {
                $table->boolean('send_photo_telegram')->default(true)->after('radius_meters');
            }
            if (!Schema::hasColumn('attendance_settings', 'learning_mode')) {
                $table->string('learning_mode', 10)->default('PTM')->after('send_photo_telegram');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['send_photo_telegram', 'learning_mode']);
        });
    }
};