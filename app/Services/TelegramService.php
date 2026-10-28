<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    /**
     * Kirim Pesan Teks via Telegram API
     */
    public function sendMessage($chatId, string $text): bool
    {
        if (empty($chatId) || empty($this->token)) {
            return false;
        }

        try {
            $response = Http::post($this->baseUrl . 'sendMessage', [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Gagal mengirim Telegram ke {$chatId}: " . $e->getMessage());
            return false;
        }
    }
}