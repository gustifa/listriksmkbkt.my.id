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
        Schema::table('telegram_subscribers', function (Blueprint $table) {
            // Menambahkan kolom user_type setelah username dengan default 'parent'
            $table->string('user_type', 20)->default('parent')->nullable()->after('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_subscribers', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
    }
};