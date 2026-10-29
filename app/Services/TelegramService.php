<?php

namespace App\Services;

use App\Models\TelegramNotification;
use App\Models\TelegramSubscriber;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        $subscriber = TelegramSubscriber::where('chat_id', (string) $chatId)->first();
        $recipientName = $subscriber ? $subscriber->parent_name : 'Orang Tua / Pengguna';
        $logTitle = $title ?? ($type === 'absensi' ? 'Notifikasi Presensi' : 'Notifikasi Bot');

        // Record log (Status: Pending)
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

                $log->update([
                    'status'  => 'success',
                    'sent_at' => now(),
                ]);

                return true;
            }

            $errorResponseBody = $response->body();
            Log::error("Telegram GAGAL terkirim ke Chat ID: {$chatId}. Respon: " . $errorResponseBody);

            $log->update([
                'status'    => 'failed',
                'error_log' => $errorResponseBody,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error("Telegram ERROR ke {$chatId}: " . $e->getMessage());

            $log->update([
                'status'    => 'failed',
                'error_log' => $e->getMessage(),
            ]);

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

        $subscriber = TelegramSubscriber::where('chat_id', (string) $chatId)->first();
        $recipientName = $subscriber ? $subscriber->parent_name : 'Orang Tua / Pengguna';
        $logTitle = $title ?? ($type === 'absensi' ? 'Notifikasi Presensi (Wajah)' : 'Notifikasi Gambar');

        // Record log (Status: Pending)
        $log = TelegramNotification::create([
            'telegram_subscriber_id' => $subscriber?->id,
            'recipient_name'         => $recipientName,
            'chat_id'                => (string) $chatId,
            'title'                  => $logTitle,
            'message'                => $caption,
            'type'                   => $type,
            'status'                 => 'pending',
        ]);

        try {
            $fullPath = Storage::disk('public')->path($photoPath);

            // Jika file fisik tidak ditemukan di storage, fallback kirim pesan teks
            if (!file_exists($fullPath)) {
                Log::warning("Telegram Photo Warning: File {$fullPath} tidak ditemukan, mengirim pesan teks saja.");
                $log->delete(); // Hapus log pending sementara agar tidak duplikat
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

                $log->update([
                    'status'  => 'success',
                    'sent_at' => now(),
                ]);

                return true;
            }

            $errorResponseBody = $response->body();
            Log::error("Telegram Foto GAGAL terkirim ke Chat ID: {$chatId}. Respon: " . $errorResponseBody);

            $log->update([
                'status'    => 'failed',
                'error_log' => $errorResponseBody,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error("Telegram Foto ERROR ke {$chatId}: " . $e->getMessage());

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