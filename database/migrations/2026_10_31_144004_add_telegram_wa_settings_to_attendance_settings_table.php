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
        Schema::table('attendance_settings', function (Blueprint $table) {
            // 1. Sakelar (Toggle) Notifikasi
            if (!Schema::hasColumn('attendance_settings', 'enable_telegram_notification')) {
                $table->boolean('enable_telegram_notification')->default(true)->after('id');
            }
            if (!Schema::hasColumn('attendance_settings', 'enable_wa_notification')) {
                $table->boolean('enable_wa_notification')->default(true)->after('enable_telegram_notification');
            }

            // 2. Parameter Konfigurasi Telegram Bot
            if (!Schema::hasColumn('attendance_settings', 'telegram_bot_token')) {
                $table->string('telegram_bot_token')->nullable()->after('enable_wa_notification');
            }
            if (!Schema::hasColumn('attendance_settings', 'telegram_admin_chat_id')) {
                $table->string('telegram_admin_chat_id')->nullable()->after('telegram_bot_token');
            }
            if (!Schema::hasColumn('attendance_settings', 'telegram_bot_username')) {
                $table->string('telegram_bot_username')->nullable()->after('telegram_admin_chat_id');
            }
            if (!Schema::hasColumn('attendance_settings', 'telegram_admin_group_link')) {
                $table->string('telegram_admin_group_link')->nullable()->after('telegram_bot_username');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn([
                'enable_telegram_notification',
                'enable_wa_notification',
                'telegram_bot_token',
                'telegram_admin_chat_id',
                'telegram_bot_username',
                'telegram_admin_group_link',
            ]);
        });
    }
};