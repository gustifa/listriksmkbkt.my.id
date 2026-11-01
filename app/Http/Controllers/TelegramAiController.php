<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use App\Services\Telegram\GeminiAiService;
use App\Models\TelegramSubscriber;
use Illuminate\Support\Facades\DB;
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
     * Menangani pesan teks bebas dari pengguna Telegram menggunakan AI Gemini/Multi-API (Lama)
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

    /**
     * Memproses pesan bebas menggunakan Intent Router (Direct SQL) + AI Fallback
     */
    public function handleHybridRequest(string $chatId, string $text, ?TelegramSubscriber $subscriber = null): void
    {
        try {
            $lowerText = strtolower(trim($text));

            // 1. INTENT ROUTER: DETEKSI KATA KUNCI PRESENSI / ABSENSI / GATE / HADIR
            if (
                str_contains($lowerText, 'presensi') || 
                str_contains($lowerText, 'absensi')  || 
                str_contains($lowerText, 'absen')    || 
                str_contains($lowerText, 'hadir')    || 
                str_contains($lowerText, 'masuk')    || 
                str_contains($lowerText, 'kehadiran')
            ) {
                $reply = $this->getAttendanceData($subscriber);
                $this->telegramService->sendMessage($chatId, $reply);
                return;
            }

            // 2. INTENT ROUTER: DETEKSI KATA KUNCI JADWAL / MENGAJAR / KELAS
            if (
                str_contains($lowerText, 'jadwal')   || 
                str_contains($lowerText, 'mengajar') || 
                str_contains($lowerText, 'kelas')
            ) {
                $reply = $this->getScheduleData($subscriber);
                $this->telegramService->sendMessage($chatId, $reply);
                return;
            }

            // 3. FALLBACK: PERTANYAAN BEBAS DIOLAH OLEH AI GEMINI/GROQ
            $aiReply = method_exists($this->aiService, 'askAiWithContext')
                ? $this->aiService->askAiWithContext($text, $subscriber)
                : $this->aiService->askAi($text);

            $this->telegramService->sendMessage($chatId, $aiReply);

        } catch (\Exception $e) {
            Log::error("Error Hybrid Telegram Controller: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->telegramService->sendMessage($chatId, "⚠️ Maaf, terjadi kendala saat memproses permintaan Anda.");
        }
    }

    /**
     * Fetch Direct Query Presensi Harian dari Tabel `daily_attendances` (PostgreSQL)
     */
    private function getAttendanceData(?TelegramSubscriber $subscriber): string
    {
        if (!$subscriber) {
            return "⚠️ Akun Telegram Anda belum terhubung dengan SISFO SMKN 1 Bukittinggi. Silakan lakukan registrasi terlebih dahulu.";
        }

        // Ambil data siswa terkait
        $student = $subscriber->student ?? ($subscriber->student_id ? \App\Models\Student::find($subscriber->student_id) : null);

        if ($student) {
            // Query langsung ke tabel daily_attendances berdasarkan student_id dan date hari ini
            $attendance = DB::table('daily_attendances')
                ->where('student_id', $student->id)
                ->whereDate('date', now())
                ->first();

            $classroomName = object_get($student, 'classroom.name', '-');
            $arrivalTime   = $attendance && $attendance->arrival_time ? substr($attendance->arrival_time, 0, 5) : '-';
            $departureTime = $attendance && $attendance->departure_time ? substr($attendance->departure_time, 0, 5) : '-';
            $status        = $attendance ? strtoupper($attendance->status ?? 'HADIR') : 'BELUM PRESENSI';

            // Jika Pengguna adalah Orang Tua / Siswa
            if ($subscriber->role === 'parent' || (!$subscriber->teacher_id && $subscriber->student_id)) {
                if ($attendance) {
                    return "👨‍👩‍👦 *Laporan Presensi Gate Hari Ini*\n\n" .
                           "👤 Nama Anak: *{$student->name}*\n" .
                           "🏫 Kelas: *{$classroomName}*\n" .
                           "⏰ Jam Masuk: *{$arrivalTime} WIB*\n" .
                           "🚪 Jam Pulang: *{$departureTime} WIB*\n" .
                           "📌 Status: *{$status}*";
                }

                return "👨‍👩‍👦 *Laporan Presensi Gate Hari Ini*\n\nAnak Anda (*{$student->name}*) belum mencatatkan kehadiran di gate sekolah hari ini.";
            }

            if ($attendance) {
                return "📋 *Status Presensi Gate Hari Ini*\n\n" .
                       "👤 Nama: *{$student->name}*\n" .
                       "🏫 Kelas: *{$classroomName}*\n" .
                       "⏰ Jam Masuk: *{$arrivalTime} WIB*\n" .
                       "🚪 Jam Pulang: *{$departureTime} WIB*\n" .
                       "📌 Status: *{$status}*";
            }

            return "📋 *Status Presensi Gate Hari Ini*\n\nBelum ada catatan masuk gate/presensi untuk Anda hari ini.";
        }

        return "⚠️ Fitur laporan presensi ini khusus untuk akun Siswa dan Orang Tua terdaftar.";
    }

    /**
     * Fetch Direct Query Jadwal Mengajar dari Tabel `schedules` (PostgreSQL)
     */
    private function getScheduleData(?TelegramSubscriber $subscriber): string
    {
        if (!$subscriber) {
            return "⚠️ Akun Telegram Anda belum terhubung dengan SISFO SMKN 1 Bukittinggi.";
        }

        // Jika Pengguna adalah Guru
        if ($subscriber->teacher_id) {
            $teacher = $subscriber->teacher ?? \App\Models\Teacher::find($subscriber->teacher_id);

            if (!$teacher) {
                return "⚠️ Data guru tidak ditemukan di sistem SISFO.";
            }

            // Map nama hari dalam Bahasa Indonesia
            $daysMap = [
                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
                5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
            ];
            $todayName = $daysMap[date('N')] ?? 'Senin';

            // Query join tabel schedules, classrooms, dan subjects
            $schedules = DB::table('schedules')
                ->leftJoin('classrooms', 'schedules.classroom_id', '=', 'classrooms.id')
                ->leftJoin('subjects', 'schedules.subject_id', '=', 'subjects.id')
                ->where('schedules.teacher_id', $teacher->id)
                ->where(function($q) use ($todayName) {
                    $q->where('schedules.day', $todayName)
                      ->orWhere('schedules.day', (string)date('N'));
                })
                ->select(
                    'schedules.start_time',
                    'schedules.end_time',
                    'classrooms.name as classroom_name',
                    'subjects.name as subject_name'
                )
                ->orderBy('schedules.start_time', 'asc')
                ->get();

            if ($schedules->isNotEmpty()) {
                $text = "📚 *Jadwal Mengajar Hari Ini ({$todayName})*\n" .
                        "👨‍🏫 Guru: *{$teacher->name}*\n\n";

                foreach ($schedules as $item) {
                    $startTime = substr($item->start_time, 0, 5);
                    $endTime   = substr($item->end_time, 0, 5);
                    $text .= "• *{$startTime} - {$endTime}*: {$item->classroom_name} ({$item->subject_name})\n";
                }

                return $text;
            }

            return "📚 *Jadwal Mengajar Hari Ini ({$todayName})*\n\nTidak ada jadwal mengajar untuk Bapak/Ibu hari ini.";
        }

        return "ℹ️ Untuk jadwal pelajaran siswa, silakan gunakan perintah /jadwal.";
    }
}