<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use App\Services\Telegram\GeminiAiService;
use Illuminate\Support\Facades\Log;

class TelegramAiController extends Controller
{
    protected GeminiAiService $aiService;
    protected TelegramService $telegramService;

    public function __construct(GeminiAiService $aiService, TelegramService $telegramService)
    {
        $this->aiService = $aiService;
        $this->telegramService = $telegramService;
    }

    /**
     * Menangani pesan teks bebas dari pengguna Telegram menggunakan AI Gemini/Multi-API
     */
    public function handleAiResponse(string $chatId, string $text): void
    {
        try {
            // Panggil service AI untuk memproses teks
            $aiReply = $this->aiService->askAi($text);

            // Kirimkan balasan AI ke chat Telegram
            $this->telegramService->sendMessage($chatId, $aiReply);
        } catch (\Exception $e) {
            Log::error("Error pada TelegramAiController: " . $e->getMessage());
            $this->telegramService->sendMessage($chatId, "⚠️ Terjadi kesalahan saat memproses jawaban AI.");
        }
    }
}