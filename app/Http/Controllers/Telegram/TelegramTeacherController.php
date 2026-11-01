<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TeacherAttendanceService;
use App\Models\TelegramSubscriber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramTeacherController extends Controller
{
    protected $attendanceService;

    public function __construct(TeacherAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Menangani khusus unggahan foto rekap presensi dari Guru
     */
    public function handlePhotoUpload(array $message, TelegramSubscriber $subscriber)
    {
        $chatId = $message['chat']['id'];
        $photos = $message['photo'] ?? [];

        if (empty($photos)) {
            return $this->replyTelegram($chatId, "⚠️ Foto tidak ditemukan.");
        }

        // 1. Beri respons cepat ke Telegram bahwa gambar sedang diproses
        $this->replyTelegram($chatId, "⏳ *Sedang menganalisis foto rekap presensi guru...*\nMohon tunggu beberapa detik.");

        try {
            // 2. Ambil file_id foto ukuran terbesar (elemen terakhir array)
            $bestPhoto = end($photos);
            $fileId    = $bestPhoto['file_id'];

            // 3. Unduh foto dari server Telegram
            $photoPath = $this->downloadPhotoFromTelegram($fileId);

            if (!$photoPath) {
                return $this->replyTelegram($chatId, "❌ *Gagal Mengunduh Foto*\nTerjadi kesalahan koneksi ke server Telegram.");
            }

            // 4. Proses OCR Vision lewat TeacherAttendanceService
            $result = $this->attendanceService->processAttendancePhoto($photoPath);

            // 5. Berikan balasan pasti (Sukses / Gagal) agar bot tidak menggantung
            if ($result && !empty($result['attendances'])) {
                $totalSiswa = count($result['attendances']);
                $tanggal    = $result['date'] ?? date('Y-m-d');

                $replyMsg = "✅ *Rekap Presensi Berhasil Diproses!*\n\n" .
                            "📅 *Tanggal:* {$tanggal}\n" .
                            "👥 *Total Siswa Terbaca:* {$totalSiswa} siswa\n\n" .
                            "Data kehadiran telah berhasil disimpan ke database SISFO.";

                return $this->replyTelegram($chatId, $replyMsg);
            } else {
                return $this->replyTelegram($chatId, "⚠️ *Gagal Menganalisis Foto*\n\nTeks rekap presensi tidak terbaca dengan jelas oleh AI. Pastikan foto cukup terang dan tulisan tegas, lalu coba upload ulang.");
            }

        } catch (\Exception $e) {
            Log::error("Teacher Controller Error: " . $e->getMessage());
            return $this->replyTelegram($chatId, "❌ *Terjadi Kesalahan Server*\nSistem tidak dapat memproses foto saat ini.");
        }
    }

    /**
     * Helper Unduh Foto dari Telegram API
     */
    private function downloadPhotoFromTelegram(string $fileId): ?string
    {
        $botToken = config('services.telegram.bot_token');

        // Get file path from Telegram
        $res = Http::get("https://api.telegram.org/bot{$botToken}/getFile?file_id={$fileId}");
        if (!$res->successful()) return null;

        $filePath = $res->json('result.file_path');
        $fileUrl  = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";

        // Save to local storage
        $imageContent = file_get_contents($fileUrl);
        $fileName     = 'attendance_guru_' . time() . '.jpg';
        $savePath     = 'public/presensi_guru/' . $fileName;

        \Illuminate\Support\Facades\Storage::put($savePath, $imageContent);

        return 'presensi_guru/' . $fileName;
    }

    /**
     * Helper Kirim Balasan Telegram
     */
    private function replyTelegram($chatId, string $text)
    {
        $botToken = config('services.telegram.bot_token');
        return Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown'
        ]);
    }
}