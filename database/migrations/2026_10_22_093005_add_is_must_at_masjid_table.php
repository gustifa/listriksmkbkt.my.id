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
        Schema::table('prayer_locations', function (Blueprint $table) {
            // Flag lokasi masjid (true = Wajib Masjid, false = Boleh Rumah/PJJ)
            $table->boolean('is_must_at_masjid_subuh')->default(false)->after('is_active');
            $table->boolean('is_must_at_masjid_zuhur')->default(true)->after('is_must_at_masjid_subuh');// Default Zuhur di Masjid
            $table->boolean('is_must_at_masjid_ashar')->default(false)->after('is_must_at_masjid_zuhur');
            $table->boolean('is_must_at_masjid_maghrib')->default(false)->after('is_must_at_masjid_ashar');
            $table->boolean('is_must_at_masjid_isya')->default(false)->after('is_must_at_masjid_maghrib');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prayer_locations', function (Blueprint $table) {
            $table->dropColumn(['is_must_at_masjid_subuh', 'is_must_at_masjid_zuhur', 'is_must_at_masjid_ashar', 'is_must_at_masjid_maghrib', 'is_must_at_masjid_isya'  ]);
        });
    }
};
