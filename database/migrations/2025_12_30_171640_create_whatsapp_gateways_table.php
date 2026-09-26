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
        Schema::create('whatsapp_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique(); // contoh: gateway_1
            $table->string('name'); // contoh: WA Admin 1
            $table->string('number')->nullable(); // 62812...
            $table->string('status')->default('disconnected');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_gateways');
    }
};
