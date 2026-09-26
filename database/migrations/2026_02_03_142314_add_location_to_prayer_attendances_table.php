<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menambah kolom latitude dan longitude.
     */
    public function up(): void
    {
        Schema::table('prayer_attendances', function (Blueprint $blueprint) {
            // Menggunakan decimal untuk akurasi koordinat GPS yang tepat
            // Lat: -90 sampai 90 (10 digit, 8 angka di belakang koma)
            // Lng: -180 sampai 180 (11 digit, 8 angka di belakang koma)
            $blueprint->decimal('latitude', 10, 8)->nullable()->after('prayer_name');
            $blueprint->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Membatalkan migrasi (rollback).
     */
    public function down(): void
    {
        Schema::table('prayer_attendances', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['latitude', 'longitude']);
        });
    }
};