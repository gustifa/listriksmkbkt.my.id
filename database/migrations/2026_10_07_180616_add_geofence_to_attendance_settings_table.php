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
            $table->decimal('latitude', 10, 8)->nullable()->default(-0.30512300)->after('early_departure_time');
            $table->decimal('longitude', 11, 8)->nullable()->default(100.36912300)->after('latitude');
            $table->integer('radius_meters')->nullable()->default(100)->after('longitude'); // Radius default 100 meter
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_meters']);
        });
    }
};
