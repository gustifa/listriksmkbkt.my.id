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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('item_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained(); // Siapa yang menginput
            $table->enum('type', ['in', 'out']); // Masuk atau Keluar
            $table->integer('quantity');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->string('funding_source')->nullable();
            $table->year('year')->nullable();
            $table->string('receiver')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
