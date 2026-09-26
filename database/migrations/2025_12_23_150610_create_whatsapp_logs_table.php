<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_number'); // Nomor Tujuan
            $table->text('message');            // Isi Pesan
            $table->string('status');           // success / failed
            $table->text('api_response')->nullable(); // Respon dari Fonnte (untuk debugging)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};