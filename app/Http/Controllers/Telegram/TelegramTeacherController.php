<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TeacherAttendanceService;
use App\Models\TelegramSubscriber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramTeacherController extends Controller
{
    protected $attendanceService;

    public function __construct(TeacherAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Menangani unggahan foto rekap presensi dari Guru
     */
    public function handlePhotoUpload(array $message, TelegramSubscriber $subscriber)
    {
        $chatId = $message['chat']['id'];
        $photos = $message['photo'] ?? [];

        if (empty($photos)) {
            return response()->json(['status' => 'ok']);
        }

        // 1. Beri respons awal ke pengguna
        $this->replyTelegram($chatId, "⏳ *Sedang menganalisis foto rekap presensi guru...*\nMohon tunggu beberapa detik.");

        try {
            $bestPhoto = end($photos);
            $fileId    = $bestPhoto['file_id'];
            
            // Simpan foto fisik di public storage
            $photoPath = $this->downloadPhotoFromTelegram($fileId);

            if (!$photoPath) {
                $this->replyTelegram($chatId, "❌ *Gagal Mengunduh Foto*\nTerjadi kesalahan koneksi ke server Telegram.");
                return response()->json(['status' => 'ok']);
            }

            // 2. Jalankan Vision OCR via Service
            $result = $this->attendanceService->processAttendancePhoto($photoPath);

            if ($result && !empty($result['attendances'])) {
                $totalSiswa = count($result['attendances']);
                $tanggal    = $result['date'] ?? date('Y-m-d');

                $replyMsg = "✅ *Rekap Presensi Berhasil Diproses!*\n\n" .
                            "📅 *Tanggal:* {$tanggal}\n" .
                            "👥 *Total Siswa Terbaca:* {$totalSiswa} siswa\n\n" .
                            "Data kehadiran telah berhasil disimpan ke database SISFO.";

                $this->replyTelegram($chatId, $replyMsg);
            } else {
                $this->replyTelegram($chatId, "⚠️ *Gagal Menganalisis Foto*\n\nTeks rekap presensi tidak terbaca dengan jelas. Pastikan foto cukup terang dan tulisan tegas.");
            }

        } catch (\Exception $e) {
            Log::error("Teacher Controller Error: " . $e->getMessage());
            $this->replyTelegram($chatId, "❌ *Terjadi Kesalahan Server*\nSistem tidak dapat memproses foto saat ini.");
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Unduh Foto dari Telegram Server dengan Endpoint Murni (Timeout 30s)
     */
    private function downloadPhotoFromTelegram(string $fileId): ?string
    {
        $botToken = $this->getPureBotToken();
        if (empty($botToken)) return null;

        $url = "https://api.telegram.org/bot{$botToken}/getFile?file_id={$fileId}";

        $res = Http::withoutVerifying()
            ->timeout(30)
            ->retry(2, 500)
            ->get($url);

        if (!$res->successful()) return null;

        $filePathOnTelegram = $res->json('result.file_path');
        $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePathOnTelegram}";

        $imageContent = @file_get_contents($fileUrl);
        if (!$imageContent) return null;

        $fileName = 'attendance_guru_' . time() . '_' . uniqid() . '.jpg';
        $relativePath = 'presensi_guru/' . $fileName;

        Storage::disk('public')->put($relativePath, $imageContent);

        return $relativePath;
    }

    /**
     * Helper Balasan Telegram dengan Endpoint Murni (Timeout 30s)
     */
    private function replyTelegram($chatId, string $text)
    {
        $botToken = $this->getPureBotToken();
        if (empty($botToken)) return false;

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        return Http::withoutVerifying()
            ->timeout(30)
            ->retry(2, 500)
            ->post($url, [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown'
            ]);
    }

    /**
     * Isolasi Murni Kunci Token Bot Telegram (Bebas dari Kurung Siku / Markdown)
     */
    private function getPureBotToken(): string
    {
        $rawToken = config('services.telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        $clean = trim(urldecode((string)$rawToken));
        
        // Buang seluruh karakter Markdown / Kurung Siku / Spasi jika terikut
        $clean = str_replace(['[', ']', '(', ')', '{', '}', '"', "'", ' '], '', $clean);
        
        // Ekstrak token murni format angka:karakter (Contoh: 8420994424:AAEU...)
        if (preg_match('/([0-9]{8,12}:[a-zA-Z0-9_\-]{30,50})/', $clean, $matches)) {
            return $matches[1];
        }

        return $clean;
    }
}