<?php

namespace App\Services\Telegram;

use App\Models\Student;
use App\Models\IzinSiswa;
use App\Models\TelegramSubscriber;
use App\Models\AttendanceSetting;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TelegramStudentService
{
    /**
     * Mengambil data siswa yang terhubung dengan akun Telegram terdaftar.
     */
    public function getSubscriberStudent(string $chatId, TelegramService $telegramService): ?Student
    {
        $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
                $q->where('chat_id', (string)$chatId)
                  ->orWhere('chat_id', (int)$chatId);
            })
            ->where('approval_status', 'approved')
            ->where('is_active', true)
            ->first();

        if (!$subscriber || !$subscriber->student_id) {
            $telegramService->sendMessageWithRemoveKeyboard(
                $chatId, 
                "⚠️ *Akun Anda belum terverifikasi.*\n\nKetik `/start` untuk mendaftar dan menghubungkan akun dengan data siswa."
            );
            return null;
        }

        return Student::with('classroom')->find($subscriber->student_id);
    }

    /**
     * Menangani awal mula command /izin atau /sakit.
     */
    public function handleIzinCommand(string $chatId, string $lowerText, TelegramService $telegramService): void
    {
        Cache::forget("telegram_permit_step_{$chatId}");

        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $type = str_contains($lowerText, 'sakit') ? 'sakit' : 'izin';

        Cache::put("telegram_permit_step_{$chatId}", [
            'type'       => $type,
            'step'       => 'WAITING_REASON',
            'student_id' => $student->id
        ], now()->addMinutes(10));

        $jenisText = strtoupper($type);
        $prompt = "📝 *FORM PENGAJUAN " . $jenisText . "*\n\n" .
                  "👤 Siswa: *{$student->name}*\n" .
                  "🏫 Kelas: *" . (optional($student->classroom)->name ?? '-') . "*\n\n" .
                  "Silakan ketikkan *alasan/keterangan lengkap* mengapa siswa tidak dapat hadir hari ini.\n\n" .
                  "_(Ketik `stop` atau `/batal` untuk membatalkan)_";

        $telegramService->sendMessage($chatId, $prompt, 'info', 'Pengajuan Izin');
    }

    /**
     * Menangani proses input teks/foto multi-step izin & sakit.
     */
    public function handlePermitConversation(string $chatId, array $message, array $permitSession, TelegramService $telegramService): void
    {
        $student = Student::find($permitSession['student_id']);
        if (!$student) return;

        $text  = trim($message['text'] ?? '');
        $photo = $message['photo'] ?? null;

        if ($permitSession['step'] === 'WAITING_REASON' && !empty($text) && !str_starts_with($text, '/')) {
            $permitSession['reason'] = $text;

            if ($permitSession['type'] === 'sakit') {
                $permitSession['step'] = 'WAITING_PHOTO';
                Cache::put("telegram_permit_step_{$chatId}", $permitSession, now()->addMinutes(10));

                $prompt = "📸 *UNGGAH SURAT DOKTER / KETERANGAN SAKIT*\n\n" .
                          "Silakan *kirimkan foto surat dokter* Anda ke chat ini.\n\n" .
                          "_(Ketik *'tanpa surat'* jika tidak ada foto, atau ketik *'stop'* untuk membatalkan)_";

                $telegramService->sendMessage($chatId, $prompt, 'info', 'Upload Surat');
                return;
            } else {
                $phone = $student->phone ?? $student->student_phone;
                $this->saveIzinToDatabase($student, 'izin', $permitSession['reason'], null, $phone, $chatId, $telegramService);
                Cache::forget("telegram_permit_step_{$chatId}");
                return;
            }
        }

        if ($permitSession['step'] === 'WAITING_PHOTO') {
            $filePath = null;

            if ($photo) {
                $fileId = end($photo)['file_id'];
                $filePath = $this->downloadTelegramFile($fileId, $student->nis ?? 'bot');
            }

            $phone = $student->phone ?? $student->student_phone;
            $this->saveIzinToDatabase($student, 'sakit', $permitSession['reason'], $filePath, $phone, $chatId, $telegramService);
            Cache::forget("telegram_permit_step_{$chatId}");
            return;
        }
    }

    /**
     * Menyimpan data pengajuan izin/sakit ke database dan mengirim notifikasi ke grup verifikasi.
     */
    private function saveIzinToDatabase(Student $student, string $type, string $reason, ?string $filePath, ?string $phone, string $chatId, TelegramService $telegramService): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $existing = IzinSiswa::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            $statusText = $existing->status === 'approved' ? 'DISETUJUI' : 'PENDING VERIFIKASI';
            $telegramService->sendMessage($chatId, "⚠️ *Pengajuan Ditolak!* Siswa atas nama *{$student->name}* sudah memiliki pengajuan hari ini ({$today}) dengan status *{$statusText}*.", 'info', 'Sudah Ada Pengajuan');
            return;
        }

        $izin = IzinSiswa::create([
            'id'               => (string) Str::uuid(),
            'student_id'       => $student->id,
            'type'             => $type,
            'date'             => $today,
            'end_date'         => $today,
            'reason'           => $reason,
            'status'           => 'pending',
            'attachment_path'  => $filePath,
            'wa_number'        => $phone ?? $student->phone,
            'telegram_chat_id' => $chatId,
        ]);

        $className = optional($student->classroom)->name ?? '-';
        $jenisText = strtoupper($type);

        $replyMsg = "✅ *PENGAJUAN {$jenisText} BERHASIL DIKIRIM!*\n\n" .
                    "👤 Nama Siswa : *{$student->name}*\n" .
                    "🏫 Kelas : *{$className}*\n" .
                    "📅 Tanggal : *{$today}*\n" .
                    "📝 Alasan : _{$reason}_\n" .
                    "⏳ Status : *PENDING* (Menunggu Verifikasi Walas/BK/Proka/Kabeng/Admin)\n\n" .
                    "_Jika terdapat kesalahan data, silakan tekan tombol di bawah ini untuk membatalkan:_";

        $userKeyboard = [
            [
                ['text' => '❌ Batalkan Pengajuan Saya', 'callback_data' => "cancel_user_izin_{$izin->id}"]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $replyMsg, $userKeyboard);
        $this->notifyIzinToApprovers($izin, $student, $telegramService);
    }

    /**
     * Meneruskan notifikasi izin ke grup Telegram Admin/Wali Kelas.
     */
    private function notifyIzinToApprovers(IzinSiswa $izin, Student $student, TelegramService $telegramService): void
    {
        $setting = AttendanceSetting::first();
        $classroomGroupChatId = optional($student->classroom)->telegram_chat_id;

        $approverChatId = !empty($classroomGroupChatId) 
            ? $classroomGroupChatId 
            : ($setting?->telegram_admin_chat_id ?? config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID')));

        if (empty($approverChatId)) return;

        $className = optional($student->classroom)->name ?? '-';
        $jenisText = strtoupper($izin->type ?? 'IZIN');

        $text = "📩 *PENGAJUAN " . $jenisText . " SISWA BARU*\n\n" .
                "👤 Nama Siswa : *{$student->name}*\n" .
                "🏫 Kelas : *{$className}*\n" .
                "📅 Tanggal : *{$izin->date}*\n" .
                "📝 Alasan : _{$izin->reason}_\n" .
                "⏳ Status : *PENDING VERIFIKASI*\n\n" .
                "Mohon Wali Kelas, BK, Proka, Kabeng, atau Admin untuk memverifikasi pengajuan ini:";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Setujui (Approve)', 'callback_data' => "appizin_{$izin->id}"],
                    ['text' => '❌ Tolak / Batalkan', 'callback_data' => "rejizin_{$izin->id}"]
                ]
            ]
        ];

        if (!empty($izin->attachment_path) && Storage::disk('public')->exists($izin->attachment_path)) {
            $token   = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
            $baseUrl = "https://api.telegram.org/bot{$token}/";
            $fullPath = Storage::disk('public')->path($izin->attachment_path);

            try {
                Http::attach(
                    'photo', file_get_contents($fullPath), basename($fullPath)
                )->post($baseUrl . 'sendPhoto', [
                    'chat_id'      => $approverChatId,
                    'caption'      => $text,
                    'parse_mode'   => 'Markdown',
                    'reply_markup' => json_encode($keyboard)
                ]);
                return;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim foto surat ke grup: " . $e->getMessage());
            }
        }

        $telegramService->sendMessageWithInlineKeyboard($approverChatId, $text, $keyboard['inline_keyboard']);
    }

    /**
     * Menampilkan menu inline keyboard rekap.
     */
    public function sendRekapMenu(string $chatId, TelegramService $telegramService): void
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $className = optional($student->classroom)->name ?? '-';

        $text = "📊 *MENU PILIHAN REKAP ABSENSI*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *{$className}*\n\n" .
                "Silakan pilih jenis rekapitulasi presensi yang ingin ditayangkan di bawah ini:";

        $keyboard = [
            [
                ['text' => '🚪 Presensi Gerbang', 'callback_data' => 'rekap_gerbang'],
                ['text' => '📚 Presensi Kelas', 'callback_data' => 'rekap_pembelajaran']
            ],
            [
                ['text' => '🕌 Presensi Sholat', 'callback_data' => 'rekap_sholat'],
                ['text' => '🌐 Semua Presensi', 'callback_data' => 'rekap_semua']
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    public function sendRekapGerbang(string $chatId, TelegramService $telegramService): void
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $logs = DB::table('daily_attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        $text = "🚪 *REKAP PRESENSI GERBANG (7 HARI TERAKHIR)*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *" . (optional($student->classroom)->name ?? '-') . "*\n" .
                "--------------------------------------\n";

        if ($logs->isEmpty()) {
            $text .= "_\nBelum ada catatan presensi gerbang._\n\n";
        } else {
            foreach ($logs as $log) {
                $dateFormatted = date('d/m/Y', strtotime($log->date));
                $masuk  = $log->arrival_time ? substr($log->arrival_time, 0, 5) : '-';
                $pulang = $log->departure_time ? substr($log->departure_time, 0, 5) : '-';

                $rawStatus = strtolower(trim($log->status ?? ''));
                if (empty($rawStatus) || in_array($rawStatus, ['alfa', 'alpha', 'a', 'tanpa keterangan'])) {
                    $status = '❌ ALFA (Tanpa Keterangan)';
                } elseif (in_array($rawStatus, ['izin', 'i'])) {
                    $status = 'ℹ️ IZIN';
                } elseif (in_array($rawStatus, ['sakit', 's'])) {
                    $status = '🏥 SAKIT';
                } else {
                    $status = '✅ ' . strtoupper($rawStatus);
                }

                $text .= "📅 *{$dateFormatted}* [{$status}]\n" .
                         " ├ Datang : `{$masuk}` WIB\n" .
                         " └ Pulang : `{$pulang}` WIB\n\n";
            }
        }

        $text .= "--- *Listrik SMKN 1 Bukittinggi* ---";

        $setting   = AttendanceSetting::first();
        $groupLink = $setting?->telegram_admin_group_link 
            ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

        $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    public function sendRekapPembelajaran(string $chatId, TelegramService $telegramService): void
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $logs = DB::table('attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        $text = "📚 *REKAP PRESENSI PEMBELAJARAN (7 TERAKHIR)*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *" . (optional($student->classroom)->name ?? '-') . "*\n" .
                "--------------------------------------\n";

        if ($logs->isEmpty()) {
            $text .= "_\nBelum ada catatan presensi pembelajaran kelas._\n\n";
        } else {
            foreach ($logs as $log) {
                $dateFormatted = date('d/m/Y', strtotime($log->date));
                $masuk = $log->check_in_time ? substr($log->check_in_time, 0, 5) : '-';

                $rawStatus = strtolower(trim($log->status ?? ''));
                if (empty($rawStatus) || in_array($rawStatus, ['alfa', 'alpha', 'a', 'tanpa keterangan'])) {
                    $status = '❌ ALFA (Tanpa Keterangan)';
                } elseif (in_array($rawStatus, ['izin', 'i'])) {
                    $status = 'ℹ️ IZIN';
                } elseif (in_array($rawStatus, ['sakit', 's'])) {
                    $status = '🏥 SAKIT';
                } else {
                    $status = '✅ ' . strtoupper($rawStatus);
                }

                $text .= "📅 *{$dateFormatted}* | Jam: `{$masuk}` WIB\n" .
                         " └ Status: *{$status}*\n\n";
            }
        }

        $text .= "--- *Listrik SMKN 1 Bukittinggi* ---";

        $setting   = AttendanceSetting::first();
        $groupLink = $setting?->telegram_admin_group_link 
            ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

        $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    public function sendRekapSholat(string $chatId, TelegramService $telegramService): void
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $logs = DB::table('prayer_attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        $text = "🕌 *REKAP PRESENSI IBADAH SHOLAT (7 TERAKHIR)*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *" . (optional($student->classroom)->name ?? '-') . "*\n" .
                "--------------------------------------\n";

        if ($logs->isEmpty()) {
            $text .= "_\nBelum ada catatan presensi sholat._\n\n";
        } else {
            foreach ($logs as $log) {
                $dateFormatted = date('d/m/Y', strtotime($log->date));
                $sholat = ucfirst($log->prayer_name ?? 'Sholat');
                $masuk  = $log->check_in_time ? substr($log->check_in_time, 0, 5) : '-';

                $rawStatus = strtolower(trim($log->status ?? ''));
                if (empty($rawStatus) || in_array($rawStatus, ['alfa', 'alpha', 'a', 'tanpa keterangan'])) {
                    $status = '❌ ALFA (Tanpa Keterangan)';
                } elseif (in_array($rawStatus, ['izin', 'i'])) {
                    $status = 'ℹ️ IZIN';
                } elseif (in_array($rawStatus, ['sakit', 's'])) {
                    $status = '🏥 SAKIT';
                } else {
                    $status = '✅ ' . strtoupper($rawStatus);
                }

                $text .= "📅 *{$dateFormatted}* ({$sholat})\n" .
                         " ├ Waktu  : `{$masuk}` WIB\n" .
                         " └ Status : *{$status}*\n\n";
            }
        }

        $text .= "--- *Listrik SMKN 1 Bukittinggi* ---";

        $setting   = AttendanceSetting::first();
        $groupLink = $setting?->telegram_admin_group_link 
            ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

        $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    public function sendRekapSemua(string $chatId, TelegramService $telegramService): void
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $className = optional($student->classroom)->name ?? '-';

        $text = "🌐 *REKAP LENGKAP SEMUA PRESENSI (7 HARI TERAKHIR)*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *{$className}*\n" .
                "======================================\n\n";

        // 1. GERBANG
        $text .= "🚪 *[ PRESENSI GERBANG ]*\n";
        $gerbang = DB::table('daily_attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')->limit(3)->get();

        if ($gerbang->isEmpty()) {
            $text .= "_Belum ada data gerbang._\n\n";
        } else {
            foreach ($gerbang as $g) {
                $tgl = date('d/m/Y', strtotime($g->date));
                $m = $g->arrival_time ? substr($g->arrival_time, 0, 5) : '-';
                $p = $g->departure_time ? substr($g->departure_time, 0, 5) : '-';
                $st = strtoupper($g->status ?? 'HADIR');
                $text .= "• {$tgl} [{$st}] - Datang: `{$m}`, Pulang: `{$p}`\n";
            }
            $text .= "\n";
        }

        // 2. PEMBELAJARAN
        $text .= "📚 *[ PRESENSI KELAS ]*\n";
        $pembelajaran = DB::table('attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')->limit(3)->get();

        if ($pembelajaran->isEmpty()) {
            $text .= "_Belum ada data kelas._\n\n";
        } else {
            foreach ($pembelajaran as $p) {
                $tgl = date('d/m/Y', strtotime($p->date));
                $m = $p->check_in_time ? substr($p->check_in_time, 0, 5) : '-';
                $st = strtoupper($p->status ?? 'HADIR');
                $text .= "• {$tgl} [{$st}] - Jam: `{$m}` WIB\n";
            }
            $text .= "\n";
        }

        // 3. SHOLAT
        $text .= "🕌 *[ PRESENSI SHOLAT ]*\n";
        $sholat = DB::table('prayer_attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')->limit(3)->get();

        if ($sholat->isEmpty()) {
            $text .= "_Belum ada data sholat._\n\n";
        } else {
            foreach ($sholat as $s) {
                $tgl = date('d/m/Y', strtotime($s->date));
                $sh = ucfirst($s->prayer_name ?? 'Sholat');
                $m = $s->check_in_time ? substr($s->check_in_time, 0, 5) : '-';
                $st = strtoupper($s->status ?? 'HADIR');
                $text .= "• {$tgl} ({$sh}) [{$st}] - Jam: `{$m}` WIB\n";
            }
            $text .= "\n";
        }

        $text .= "--- *Listrik SMKN 1 Bukittinggi* ---";

        $setting   = AttendanceSetting::first();
        $groupLink = $setting?->telegram_admin_group_link 
            ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

        $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function downloadTelegramFile(string $fileId, string $nis): ?string
    {
        try {
            $token = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
            if (empty($token)) return null;
            
            $res = Http::get("https://api.telegram.org/bot{$token}/getFile?file_id={$fileId}");
            if (!$res->successful()) return null;

            $filePathOnTelegram = $res->json('result.file_path');
            $fileContent = Http::get("https://api.telegram.org/file/bot{$token}/{$filePathOnTelegram}")->body();

            $folder = 'surat_sakit/' . date('Y-m-d');
            $filename = "surat_telegram_{$nis}_" . time() . ".jpg";
            $fullPath = "{$folder}/{$filename}";

            Storage::disk('public')->put($fullPath, $fileContent);

            return $fullPath;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Download Telegram File Error: " . $e->getMessage());
            return null;
        }
    }
}