<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use App\Services\Telegram\GeminiAiService;
use App\Models\TelegramSubscriber;
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
     * Memproses pesan bebas menggunakan Intent Router (Direct SQL) + AI Fallback (Baru)
     */
    public function handleHybridRequest(string $chatId, string $text, ?TelegramSubscriber $subscriber = null): void
    {
        try {
            $lowerText = strtolower(trim($text));

            // 1. INTENT ROUTER: DETEKSI KATA KUNCI PRESENSI / GATE / HADIR
            if (str_contains($lowerText, 'presensi') || str_contains($lowerText, 'hadir') || str_contains($lowerText, 'masuk') || str_contains($lowerText, 'kehadiran')) {
                $reply = $this->getAttendanceData($subscriber);
                $this->telegramService->sendMessage($chatId, $reply);
                return;
            }

            // 2. INTENT ROUTER: DETEKSI KATA KUNCI JADWAL / MENGAJAR / KELAS
            if (str_contains($lowerText, 'jadwal') || str_contains($lowerText, 'mengajar') || str_contains($lowerText, 'kelas')) {
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
            Log::error("Error Hybrid Telegram Controller: " . $e->getMessage());
            $this->telegramService->sendMessage($chatId, "⚠️ Maaf, terjadi kendala saat memproses permintaan Anda.");
        }
    }

    /**
     * Fetch Direct Query Presensi dari Database PostgreSQL (100% Akurat)
     */
    private function getAttendanceData(?TelegramSubscriber $subscriber): string
    {
        if (!$subscriber) {
            return "⚠️ Akun Telegram Anda belum terhubung dengan SISFO SMKN 1 Bukittinggi. Silakan lakukan registrasi terlebih dahulu.";
        }

        // Jika Pengguna adalah Siswa
        if ($subscriber->student_id && $subscriber->student) {
            $student = $subscriber->student;
            $attendance = \App\Models\StudentAttendance::where('student_id', $student->id)
                ->whereDate('date', now())
                ->first();

            if ($attendance) {
                return "📋 *Status Presensi Hari Ini*\n\n" .
                       "👤 Nama: *{$student->name}*\n" .
                       "🏫 Kelas: *" . optional($student->classroom)->name . "*\n" .
                       "⏰ Jam Masuk: *" . ($attendance->time_in ?? '-') . "*\n" .
                       "📌 Status: *" . strtoupper($attendance->status) . "*";
            }

            return "📋 *Status Presensi Hari Ini*\n\nBelum ada catatan masuk gate/presensi untuk Anda hari ini.";
        }

        // Jika Pengguna adalah Orang Tua
        if ($subscriber->role === 'parent' && $subscriber->student_id) {
            $student = $subscriber->student;
            $attendance = \App\Models\StudentAttendance::where('student_id', $student->id)
                ->whereDate('date', now())
                ->first();

            if ($attendance) {
                return "👨‍👩‍👦 *Laporan Presensi Anak Hari Ini*\n\n" .
                       "👤 Nama Anak: *{$student->name}*\n" .
                       "🏫 Kelas: *" . optional($student->classroom)->name . "*\n" .
                       "⏰ Jam Masuk: *" . ($attendance->time_in ?? '-') . "*\n" .
                       "📌 Status: *" . strtoupper($attendance->status) . "*";
            }

            return "👨‍👩‍👦 *Laporan Presensi Anak Hari Ini*\n\nAnak Anda (*{$student->name}*) belum mencatatkan kehadiran di gate sekolah hari ini.";
        }

        return "⚠️ Fitur laporan presensi ini khusus untuk akun Siswa dan Orang Tua terdaftar.";
    }

    /**
     * Fetch Direct Query Jadwal dari Database PostgreSQL (100% Akurat)
     */
    private function getScheduleData(?TelegramSubscriber $subscriber): string
    {
        if (!$subscriber) {
            return "⚠️ Akun Telegram Anda belum terhubung dengan SISFO SMKN 1 Bukittinggi.";
        }

        // Jika Pengguna adalah Guru
        if ($subscriber->teacher_id && $subscriber->teacher) {
            $teacher = $subscriber->teacher;
            $dayOfWeek = date('N'); // 1 = Senin, 7 = Minggu

            $assignments = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('day', $dayOfWeek)
                ->with(['classroom', 'subject'])
                ->orderBy('time_slot', 'asc')
                ->get();

            if ($assignments->isNotEmpty()) {
                $text = "📚 *Jadwal Mengajar Hari Ini*\n" .
                        "👨‍🏫 Guru: *{$teacher->name}*\n\n";

                foreach ($assignments as $item) {
                    $text .= "• Jam Ke-{$item->time_slot}: *" . optional($item->classroom)->name . "* - " . optional($item->subject)->name . "\n";
                }

                return $text;
            }

            return "📚 *Jadwal Mengajar Hari Ini*\n\nTidak ada jadwal mengajar untuk Bapak/Ibu hari ini.";
        }

        return "ℹ️ Untuk jadwal pelajaran siswa, silakan gunakan perintah /jadwal.";
    }
}