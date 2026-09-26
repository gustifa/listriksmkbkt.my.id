<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scanner_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_token')->unique(); // Token unik dari React
            $table->string('device_name'); // Misal: "Kiosk Lobby Utama"
            $table->string('status')->default('active'); // active, blocked
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scanner_devices');
    }
};
