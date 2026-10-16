<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            
            // Morph UUID untuk subject dan causer
            $table->nullableUuidMorphs('subject');
            $table->nullableUuidMorphs('causer');
            
            $table->string('event')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->json('properties')->nullable();
            $table->json('attribute_changes')->nullable(); // <-- TAMBAHKAN BARIS INI
            $table->timestamps();

            $table->index('log_name');
        });
    }
};
