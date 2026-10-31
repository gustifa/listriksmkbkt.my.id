<?php

namespace App\Services;

use App\Models\TelegramNotification;
use App\Models\TelegramSubscriber;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * Kirim Pesan Teks via Telegram API & Simpan Log Monitoring
     */
    public function sendMessage($chatId, string $text, string $type = 'absensi', ?string $title = null): bool
    {
        if (empty($chatId) || empty($this->token)) {
            Log::warning("Telegram: Chat ID atau Token kosong.");
            return false;
        }

        $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
            $q->where('chat_id', (string) $chatId)
              ->orWhere('chat_id', (int) $chatId);
        })->first();

        $recipientName = $subscriber ? $subscriber->parent_name : 'Orang Tua / Pengguna';
        $logTitle = $title ?? ($type === 'absensi' ? 'Notifikasi Presensi' : 'Notifikasi Bot');

        // Aman dari Crash: Buat log di dalam try-catch agar kegagalan DB tidak menghentikan bot
        $log = null;
        try {
            $log = TelegramNotification::create([
                'id'                     => (string) Str::uuid(),
                'telegram_subscriber_id' => $subscriber ? (string) $subscriber->id : null,
                'recipient_name'         => (string) $recipientName,
                'chat_id'                => (string) $chatId,
                'title'                  => (string) $logTitle,
                'message'                => (string) $text,
                'type'                   => (string) $type,
                'status'                 => 'pending',
            ]);
        } catch (\Exception $exLog) {
            Log::warning("Telegram Log Create Warning: " . $exLog->getMessage());
        }

        try {
            $response = Http::post($this->baseUrl . 'sendMessage', [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                Log::info("Telegram SUKSES terkirim ke Chat ID: {$chatId}");

                if ($log) {
                    $log->update([
                        'status'  => 'success',
                        'sent_at' => now(),
                    ]);
                }

                return true;
            }

            $errorResponseBody = $response->body();
            Log::error("Telegram GAGAL terkirim ke Chat ID: {$chatId}. Respon: " . $errorResponseBody);

            if ($log) {
                $log->update([
                    'status'    => 'failed',
                    'error_log' => $errorResponseBody,
                ]);
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Telegram ERROR ke {$chatId}: " . $e->getMessage());

            if ($log) {
                $log->update([
                    'status'    => 'failed',
                    'error_log' => $e->getMessage(),
                ]);
            }

            return false;
        }
    }

    /**
     * Kirim Pesan Foto dengan Caption via Telegram API & Simpan Log
     */
    public function sendPhoto($chatId, string $photoPath, string $caption = '', string $type = 'absensi', ?string $title = null): bool
    {
        if (empty($chatId) || empty($this->token)) {
            Log::warning("Telegram: Chat ID atau Token kosong.");
            return false;
        }

        // Cek Pengaturan Admin: Apakah fitur foto diaktifkan di database?
        $setting = AttendanceSetting::first();
        $isSendPhoto = $setting ? (bool) ($setting->send_photo_telegram ?? true) : true;

        // Jika Admin mematikan opsi foto, alihkan pengiriman ke pesan teks biasa
        if (!$isSendPhoto) {
            return $this->sendMessage($chatId, $caption, $type, $title);
        }

        $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
            $q->where('chat_id', (string) $chatId)
              ->orWhere('chat_id', (int) $chatId);
        })->first();

        $recipientName = $subscriber ? $subscriber->parent_name : 'Orang Tua / Pengguna';
        $logTitle = $title ?? ($type === 'absensi' ? 'Notifikasi Presensi (Wajah)' : 'Notifikasi Gambar');

        $log = null;
        try {
            $log = TelegramNotification::create([
                'id'                     => (string) Str::uuid(),
                'telegram_subscriber_id' => $subscriber ? (string) $subscriber->id : null,
                'recipient_name'         => (string) $recipientName,
                'chat_id'                => (string) $chatId,
                'title'                  => (string) $logTitle,
                'message'                => (string) $caption,
                'type'                   => (string) $type,
                'status'                 => 'pending',
            ]);
        } catch (\Exception $exLog) {
            Log::warning("Telegram Photo Log Create Warning: " . $exLog->getMessage());
        }

        try {
            $fullPath = Storage::disk('public')->path($photoPath);

            // Jika file fisik tidak ditemukan di storage, fallback kirim pesan teks
            if (!file_exists($fullPath)) {
                Log::warning("Telegram Photo Warning: File {$fullPath} tidak ditemukan, mengirim pesan teks saja.");
                if ($log) {
                    $log->delete();
                }
                return $this->sendMessage($chatId, $caption, $type, $title);
            }

            $response = Http::attach(
                'photo', file_get_contents($fullPath), basename($fullPath)
            )->post($this->baseUrl . 'sendPhoto', [
                'chat_id'    => $chatId,
                'caption'    => $caption,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                Log::info("Telegram Foto SUKSES terkirim ke Chat ID: {$chatId}");

                if ($log) {
                    $log->update([
                        'status'  => 'success',
                        'sent_at' => now(),
                    ]);
                }

                return true;
            }

            $errorResponseBody = $response->body();
            Log::error("Telegram Foto GAGAL terkirim ke Chat ID: {$chatId}. Respon: " . $errorResponseBody);

            if ($log) {
                $log->update([
                    'status'    => 'failed',
                    'error_log' => $errorResponseBody,
                ]);
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Telegram Foto ERROR ke {$chatId}: " . $e->getMessage());

            if ($log) {
                $log->update([
                    'status'    => 'failed',
                    'error_log' => $e->getMessage(),
                ]);
            }

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

    /**
     * Kirim Pesan dengan Tombol Inline (Interactive Buttons)
     */
    public function sendMessageWithInlineKeyboard($chatId, string $text, array $inlineKeyboard): bool
    {
        return Http::post($this->baseUrl . 'sendMessage', [
            'chat_id'      => $chatId,
            'text'         => $text,
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => $inlineKeyboard
            ])
        ])->successful();
    }

    /**
     * Jawab Callback Query dari Tombol Inline
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): bool
    {
        return Http::post($this->baseUrl . 'answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
            'show_alert'        => $showAlert,
        ])->successful();
    }

    /**
     * Edit Teks Pesan yang Sudah Terkirim (Untuk Notifikasi Admin)
     */
    public function editMessageText($chatId, $messageId, string $text): bool
    {
        return Http::post($this->baseUrl . 'editMessageText', [
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ])->successful();
    }
}