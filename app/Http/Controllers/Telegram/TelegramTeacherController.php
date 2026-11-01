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

    public function handlePhotoUpload(array $message, TelegramSubscriber $subscriber)
    {
        $chatId = $message['chat']['id'];
        $photos = $message['photo'] ?? [];

        if (empty($photos)) {
            return response()->json(['status' => 'ok']);
        }

        $this->replyTelegram($chatId, "⏳ *Sedang menganalisis foto rekap presensi guru...*\nMohon tunggu beberapa detik.");

        try {
            $bestPhoto = end($photos);
            $fileId    = $bestPhoto['file_id'];
            
            $photoPath = $this->downloadPhotoFromTelegram($fileId);

            if (!$photoPath) {
                $this->replyTelegram($chatId, "❌ *Gagal Mengunduh Foto*\nTerjadi kesalahan koneksi ke server Telegram.");
                return response()->json(['status' => 'ok']);
            }

            $parsedData = $this->attendanceService->processAttendancePhoto($photoPath);

            if (!$parsedData || empty($parsedData['attendances'])) {
                $this->replyTelegram($chatId, "⚠️ *Gagal Menganalisis Foto*\n\nTeks rekap presensi tidak terbaca dengan jelas. Pastikan foto cukup terang dan tulisan tegas.");
                return response()->json(['status' => 'ok']);
            }

            $targetDate   = !empty($parsedData['date']) ? $parsedData['date'] : date('Y-m-d');
            $successCount = 0;
            
            // Ambil nama guru dengan aman (fallback ke parent_name jika null)
            $teacherName = 'Guru';
            if ($subscriber->teacher_id) {
                $teacher = DB::table('teachers')->where('id', $subscriber->teacher_id)->first();
                if ($teacher) $teacherName = $teacher->name;
            } elseif (!empty($subscriber->parent_name)) {
                $teacherName = $subscriber->parent_name;
            }

            foreach ($parsedData['attendances'] as $row) {
                $studentName = trim($row['student_name'] ?? '');
                $studentNis  = trim($row['nis'] ?? '');
                $statusRaw   = strtolower(trim($row['status'] ?? 'hadir'));

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

                $student = null;
                if (!empty($studentNis)) {
                    $student = DB::table('students')
                        ->where('nis', $studentNis)
                        ->orWhere('nisn', $studentNis)
                        ->first();
                }

                if (!$student && !empty($studentName)) {
                    $student = DB::table('students')
                        ->where('name', 'ILIKE', "%{$studentName}%")
                        ->first();
                }

                if ($student) {
                    $existing = DB::table('daily_attendances')
                        ->where('student_id', $student->id)
                        ->where('date', $targetDate)
                        ->first();

                    if ($existing) {
                        DB::table('daily_attendances')
                            ->where('id', $existing->id)
                            ->update([
                                'status'      => $status,
                                'recorded_by' => 'Telegram Bot OCR (' . $teacherName . ')',
                                'updated_at'  => now(),
                                'is_synced'   => true,
                            ]);
                    } else {
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
                }
            }

            $replyMsg = "✅ *Berhasil Menginput Rekap Presensi!*\n\n" .
                        "📅 *Tanggal:* {$targetDate}\n" .
                        "📊 *Total Siswa Terinput:* {$successCount} Siswa\n\n" .
                        "Data presensi telah tersimpan otomatis ke sistem SISFO.";

            $this->replyTelegram($chatId, $replyMsg);

        } catch (\Throwable $e) {
            Log::error("Teacher Controller Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            $this->replyTelegram($chatId, "❌ *Terjadi Kesalahan Server*\nSistem tidak dapat memproses foto saat ini.");
        }

        return response()->json(['status' => 'ok']);
    }

    // private function downloadPhotoFromTelegram(string $fileId): ?string
    // {
    //     $botToken = $this->getPureBotToken();
    //     if (empty($botToken)) return null;

    //     $url = "https://api.telegram.org/bot{$botToken}/getFile?file_id={$fileId}";

    //     $res = Http::withoutVerifying()->timeout(30)->retry(2, 500)->get($url);
    //     if (!$res->successful()) return null;

    //     $filePathOnTelegram = $res->json('result.file_path');
    //     $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePathOnTelegram}";

    //     $imageContent = @file_get_contents($fileUrl);
    //     if (!$imageContent) return null;

    //     $fileName = 'attendance_guru_' . time() . '_' . uniqid() . '.jpg';
    //     $relativePath = 'presensi_guru/' . $fileName;

    //     Storage::disk('public')->put($relativePath, $imageContent);

    //     return $relativePath;
    // }
    private function downloadPhotoFromTelegram(string $fileId): ?string
{
    $botToken = $this->getPureBotToken();
    if (empty($botToken)) return null;

    $url = "https://api.telegram.org/bot{$botToken}/getFile?file_id={$fileId}";

    $res = Http::withoutVerifying()->timeout(30)->retry(3, 1000)->get($url);
    if (!$res->successful()) return null;

    $filePathOnTelegram = $res->json('result.file_path');
    $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePathOnTelegram}";

    // Menggunakan Http Client agar lebih aman menangani unduhan file besar
    $responseFile = Http::withoutVerifying()->timeout(60)->get($fileUrl);
    
    if (!$responseFile->successful()) {
        Log::error("Gagal mengunduh isi file foto dari URL Telegram: {$fileUrl}");
        return null;
    }

    $fileName = 'attendance_guru_' . time() . '_' . uniqid() . '.jpg';
    $relativePath = 'presensi_guru/' . $fileName;

    // Pastikan direktori ada
    if (!Storage::disk('public')->exists('presensi_guru')) {
        Storage::disk('public')->makeDirectory('presensi_guru');
    }

    Storage::disk('public')->put($relativePath, $responseFile->body());

    return $relativePath;
}

    private function replyTelegram($chatId, string $text)
    {
        $botToken = $this->getPureBotToken();
        if (empty($botToken)) return false;

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        // Coba kirim Markdown, jika gagal fallback otomatis ke Plain Text
        $res = Http::withoutVerifying()->timeout(30)->retry(2, 500)->post($url, [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown'
        ]);

        if (!$res->successful()) {
            return Http::withoutVerifying()->timeout(30)->retry(2, 500)->post($url, [
                'chat_id' => $chatId,
                'text'    => $text,
            ]);
        }

        return $res;
    }

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