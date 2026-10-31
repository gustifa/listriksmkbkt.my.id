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
        Schema::table('classrooms', function (Blueprint $table) {
            // Menambahkan kolom telegram_chat_id bertipe varchar (nullable) setelah kolom name
            if (!Schema::hasColumn('classrooms', 'telegram_chat_id')) {
                $table->string('telegram_chat_id', 50)->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            if (Schema::hasColumn('classrooms', 'telegram_chat_id')) {
                $table->dropColumn('telegram_chat_id');
            }
        });
    }
};