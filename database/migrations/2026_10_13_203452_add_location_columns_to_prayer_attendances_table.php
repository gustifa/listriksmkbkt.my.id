<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prayer_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('prayer_attendances', 'location_type')) {
                $table->enum('location_type', ['school', 'home'])->default('school');
            }
            if (!Schema::hasColumn('prayer_attendances', 'masjid_name')) {
                $table->string('masjid_name')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('prayer_attendances', function (Blueprint $table) {
            $table->dropColumn(['location_type', 'masjid_name']);
        });
    }
};