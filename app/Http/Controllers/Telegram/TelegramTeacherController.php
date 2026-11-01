<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TeacherAttendanceService;
use App\Models\TelegramSubscriber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            $parsedData = $this->attendanceService->processAttendancePhoto($photoPath);

            if (!$parsedData || empty($parsedData['attendances'])) {
                $this->replyTelegram($chatId, "⚠️ *Gagal Menganalisis Foto*\n\nTeks rekap presensi tidak terbaca dengan jelas. Pastikan foto cukup terang dan tulisan tegas.");
                return response()->json(['status' => 'ok']);
            }

            // 3. Simpan Hasil OCR ke Tabel daily_attendances (PostgreSQL UUID Ready)
            $targetDate   = !empty($parsedData['date']) ? $parsedData['date'] : date('Y-m-d');
            $successCount = 0;
            $teacherName  = optional($subscriber->teacher)->name ?? 'Guru';

            foreach ($parsedData['attendances'] as $row) {
                $studentName = trim($row['student_name'] ?? '');
                $studentNis  = trim($row['nis'] ?? '');
                $statusRaw   = strtolower(trim($row['status'] ?? 'hadir'));

                // Normalisasi status presensi
                if (in_array($statusRaw, ['h', 'hadir', 'v', '✓', 'check'])) {
                    $status = 'hadir';
                } elseif (in_array($statusRaw, ['i', 'iz', 'izin', 'ijin'])) {
                    $status = 'izin';
                } elseif (in_array($statusRaw, ['s', 'skt', 'sakit'])) {
                    $status = 'sakit';
                } elseif (in_array($statusRaw, ['a', 'alp', 'alpha', 'alpa', 'tk'])) {
                    $status = 'alpha';
                } else {
                    $status = 'hadir';
                }

                // Cari siswa berdasarkan NIS/NISN dahulu
                $student = null;
                if (!empty($studentNis)) {
                    $student = DB::table('students')
                        ->where('nis', $studentNis)
                        ->orWhere('nisn', $studentNis)
                        ->first();
                }

                // Jika tidak ketemu NIS, cari berdasarkan NAMA (ILIKE PostgreSQL)
                if (!$student && !empty($studentName)) {
                    $student = DB::table('students')
                        ->where('name', 'ILIKE', "%{$studentName}%")
                        ->first();
                }

                if ($student) {
                    // Cek apakah presensi siswa pada tanggal tersebut sudah ada
                    $existing = DB::table('daily_attendances')
                        ->where('student_id', $student->id)
                        ->where('date', $targetDate)
                        ->first();

                    if ($existing) {
                        // Update record yang sudah ada
                        DB::table('daily_attendances')
                            ->where('id', $existing->id)
                            ->update([
                                'status'      => $status,
                                'recorded_by' => 'Telegram Bot OCR (' . $teacherName . ')',
                                'updated_at'  => now(),
                                'is_synced'   => true,
                            ]);
                    } else {
                        // Insert record baru dengan Primary Key UUID
                        DB::table('daily_attendances')->insert([
                            'id'          => (string) Str::uuid(),
                            'student_id'  => $student->id,
                            'date'        => $targetDate,
                            'status'      => $status,
                            'recorded_by' => 'Telegram Bot OCR (' . $teacherName . ')',
                            'created_at'  => now(),
                            'updated_at'  => now(),
                            'is_synced'   => true,
                        ]);
                    }
                    $successCount++;
                } else {
                    Log::warning("OCR Student Not Found: Nama={$studentName}, NIS={$studentNis}");
                }
            }

            $replyMsg = "✅ *Berhasil Menginput Rekap Presensi!*\n\n" .
                        "📅 *Tanggal:* {$targetDate}\n" .
                        "📊 *Total Siswa Terinput:* {$successCount} Siswa\n\n" .
                        "Data presensi telah tersimpan otomatis ke sistem SISFO.";

            $this->replyTelegram($chatId, $replyMsg);

        } catch (\Exception $e) {
            Log::error("Teacher Controller Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
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
     * Isolasi Murni Kunci Token Bot Telegram
     */
    private function getPureBotToken(): string
    {
        $rawToken = config('services.telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        $clean = trim(urldecode((string)$rawToken));
        $clean = str_replace(['[', ']', '(', ')', '{', '}', '"', "'", ' '], '', $clean);
        
        if (preg_match('/([0-9]{8,12}:[a-zA-Z0-9_\-]{30,50})/', $clean, $matches)) {
            return $matches[1];
        }

        return $clean;
    }
}