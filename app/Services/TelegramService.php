<?php

namespace App\Services;

use App\Models\TelegramNotification;
use App\Models\TelegramSubscriber;
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
     * Kirim Pesan Teks via Telegram API sekaligus mencatat Log Monitoring
     */
    public function sendMessage($chatId, string $text, string $type = 'absensi', ?string $title = null): bool
    {
        if (empty($chatId) || empty($this->token)) {
            Log::warning("Telegram: Chat ID atau Token kosong.");
            return false;
        }

        // 1. Ambil data subscriber berdasarkan Chat ID
        $subscriber = TelegramSubscriber::where('chat_id', (string) $chatId)->first();
        $recipientName = $subscriber ? $subscriber->parent_name : 'Orang Tua / Pengguna';
        $logTitle = $title ?? ($type === 'absensi' ? 'Notifikasi Presensi' : 'Notifikasi Bot');

        // 2. Buat record awal log di database (Status: Pending)
        $log = TelegramNotification::create([
            'telegram_subscriber_id' => $subscriber?->id,
            'recipient_name'         => $recipientName,
            'chat_id'                => (string) $chatId,
            'title'                  => $logTitle,
            'message'                => $text,
            'type'                   => $type,
            'status'                 => 'pending',
        ]);

        try {
            $response = Http::post($this->baseUrl . 'sendMessage', [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                Log::info("Telegram SUKSES terkirim ke Chat ID: {$chatId}");

                // 3. Update log status ke Success
                $log->update([
                    'status'  => 'success',
                    'sent_at' => now(),
                ]);

                return true;
            }

            $errorResponseBody = $response->body();
            Log::error("Telegram GAGAL terkirim ke Chat ID: {$chatId}. Respon: " . $errorResponseBody);

            // 4. Update log status ke Failed jika API mengembalikan respons gagal
            $log->update([
                'status'    => 'failed',
                'error_log' => $errorResponseBody,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error("Telegram ERROR ke {$chatId}: " . $e->getMessage());

            // 5. Update log status ke Failed jika terjadi exception
            $log->update([
                'status'    => 'failed',
                'error_log' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Kirim Pesan dengan Tombol Share Contact
     */
    public function sendContactButton($chatId, string $text): bool
    {
        return Http::post($this->baseUrl . 'sendMessage', [
            'chat_id'      => $chatId,
            'text'         => $text,
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode([
                'keyboard' => [
                    [
                        [
                            'text'            => '📱 Bagikan Nomor Kontak Saya',
                            'request_contact' => true
                        ]
                    ]
                ],
                'resize_keyboard'   => true,
                'one_time_keyboard' => true
            ])
        ])->successful();
    }

    /**
     * Kirim Pesan Sekaligus Menghapus Keyboard
     */
    public function sendMessageWithRemoveKeyboard($chatId, string $text): bool
    {
        return Http::post($this->baseUrl . 'sendMessage', [
            'chat_id'      => $chatId,
            'text'         => $text,
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode([
                'remove_keyboard' => true
            ])
        ])->successful();
    }
}