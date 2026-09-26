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
        Schema::table('industries', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('status');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('radius')->default(100)->after('longitude')->comment('Radius dalam meter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            $blueprint->dropColumn(['latitude', 'longitude', 'radius']);
        });
    }
};
