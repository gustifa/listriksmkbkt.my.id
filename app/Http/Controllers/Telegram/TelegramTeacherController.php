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
        $this->attendanceService =$attendanceService;
    }

    /**
     * Menangani unggahan foto rekap presensi dari Guru
     */
    public function handlePhotoUpload(array $message, TelegramSubscriber$subscriber)
    {
        $chatId =$message['chat']['id'];
        $photos =$message['photo'] ?? [];

        if (empty($photos)) {
            return response()->json(['status' => 'ok']);
        }

        // 1. Kirim pesan notifikasi awal ke Telegram
        $this->replyTelegram($chatId, "⏳ *Sedang menganalisis foto rekap presensi guru...*\nMohon tunggu beberapa detik.");

        try {
            // Ambil resolusi tertinggi
            $bestPhoto = end($photos);
            $fileId    =$bestPhoto['file_id'];
            
            // Simpan foto secara fisik ke folder presensi_guru
            $photoPath = $this->downloadPhotoFromTelegram($fileId);

            if (!$photoPath) {
                $this->replyTelegram($chatId, "❌ *Gagal Mengunduh Foto*\nTerjadi kesalahan koneksi ke server Telegram.");
                return response()->json(['status' => 'ok']);
            }

            // 2. Jalankan Vision OCR
            $result = $this->attendanceService->processAttendancePhoto($photoPath);

            if ($result && !empty($result['attendances'])) {
                $totalSiswa = count($result['attendances']);
                $tanggal    =$result['date'] ?? date('Y-m-d');

                $replyMsg = "✅ *Rekap Presensi Berhasil Diproses!*\n\n" .
                            "📅 *Tanggal:* {$tanggal}\n" .
                            "👥 *Total Siswa Terbaca:* {$totalSiswa} siswa\n\n" .
                            "Data kehadiran telah berhasil disimpan ke database SISFO.";

                $this->replyTelegram($chatId,$replyMsg);
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
     * Unduh Foto dari Telegram Server dengan Timeout Terukur (30 Detik)
     */
    private function downloadPhotoFromTelegram(string $fileId): ?string
    {
        $botToken = $this->getPureBotToken();
        if (empty($botToken)) return null;

        // Ditambahkan timeout 30 detik & retry 2 kali
        $res = Http::withoutVerifying()
            ->timeout(30)
            ->retry(2, 500)
            ->get("https://api.telegram.org/bot{$botToken}/getFile?file_id={$fileId}");

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
     * Helper Kirim Balasan Telegram dengan Timeout Terukur (30 Detik)
     */
    private function replyTelegram($chatId, string $text)
    {
        $botToken = $this->getPureBotToken();
        if (empty($botToken)) return false;

        // Ditambahkan timeout 30 detik & retry 2 kali agar tidak cURL error 28
        return Http::withoutVerifying()
            ->timeout(30)
            ->retry(2, 500)
            ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown'
            ]);
    }

    /**
     * Ekstraksi Token Bot Telegram Murni
     */
    private function getPureBotToken(): string
    {
        $rawToken = config('services.telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        $clean = trim(urldecode((string)$rawToken));
        
        // Buang karakter markdown/kurung siku jika terikut
        $clean = str_replace(['[', ']', '(', ')', '{', '}', '"', "'", ' '], '', $clean);
        
        // Ambil token Telegram murni format angka:karakter (misal: 8420994424:AAHJ...)
        if (preg_match('/([0-9]{8,12}:[a-zA-Z0-9_\-]{30,50})/', $clean, $matches)) {
            return $matches[1];
        }

        return $clean;
    }
}