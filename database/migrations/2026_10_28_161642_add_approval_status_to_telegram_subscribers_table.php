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
            if (!Schema::hasColumn('telegram_subscribers', 'approval_status')) {
                // Status: approved (langsung/disetujui), pending (menunggu), rejected (ditolak)
                $table->enum('approval_status', ['approved', 'pending', 'rejected'])
                      ->default('approved')
                      ->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_subscribers', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};