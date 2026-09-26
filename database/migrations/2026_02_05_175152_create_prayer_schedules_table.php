<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prayer_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique(); // Tanggal unik
            $table->string('subuh');
            $table->string('dhuha');
            $table->string('dzuhur');
            $table->string('ashar');
            $table->string('maghrib');
            $table->string('isya');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('prayer_schedules');
    }
};
