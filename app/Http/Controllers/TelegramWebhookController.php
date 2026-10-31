<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\IzinSiswa;
use App\Services\TelegramService;
use App\Models\TelegramSubscriber;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, TelegramService $telegramService)
    {
        if ($request->isMethod('get')) {
            return response()->json([
                'status'  => true,
                'message' => 'Endpoint Webhook Telegram SISFO Aktif!'
            ]);
        }

        try {
            $update = $request->all();

            // =========================================================================
            // 1. HANDLE CALLBACK QUERY (APPROVAL IZIN, PEMBATALAN, REKAP & ADMIN)
            // =========================================================================
            if (isset($update['callback_query'])) {
                $callback = $update['callback_query'];
                $data     = $callback['data'];

                if (str_starts_with($data, 'rekap_')) {
                    $this->handleRekapCallback($callback, $telegramService);
                } elseif (str_starts_with($data, 'cancel_user_izin_')) {
                    $this->handleUserCancelIzinCallback($callback, $telegramService);
                } elseif (str_starts_with($data, 'appizin_') || str_starts_with($data, 'rejizin_')) {
                    $this->handleIzinApprovalCallback($callback, $telegramService);
                } else {
                    $this->handleAdminCallback($callback, $telegramService);
                }

                return response()->json(['status' => 'ok']);
            }

            if (!isset($update['message'])) {
                return response()->json(['status' => 'ok']);
            }

            $chatId  = (string) $update['message']['chat']['id'];
            $message = $update['message'];

            // Ambil link group dinamis dari DB atau fallback ke config/env
            $setting   = AttendanceSetting::first();
            $groupLink = $setting?->telegram_admin_group_link 
                ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

            // =========================================================================
            // 2. HANDLE BAGIKAN KONTAK (TEKAN TOMBOL SHARE CONTACT)
            // =========================================================================
            if (isset($message['contact'])) {
                try {
                    $contact    = $message['contact'];
                    $phone      = $contact['phone_number'];
                    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

                    $firstName  = $contact['first_name'] ?? '';
                    $lastName   = $contact['last_name'] ?? '';
                    $parentName = trim($firstName . ' ' . $lastName) ?: 'Pengguna';
                    $username   = isset($message['from']['username']) ? (string)$message['from']['username'] : null;

                    $lastDigits = strlen($cleanPhone) >= 9 ? substr($cleanPhone, -9) : $cleanPhone;

                    $studentByParent  = Student::where('phone', 'like', '%' . $lastDigits)->first();
                    $studentByStudent = Student::where('student_phone', 'like', '%' . $lastDigits)->first();

                    if ($studentByParent) {
                        $student  = $studentByParent;
                        $userType = 'parent';
                    } elseif ($studentByStudent) {
                        $student  = $studentByStudent;
                        $userType = 'student';
                    } else {
                        $student  = null;
                        $userType = 'parent';
                    }

                    if ($student) {
                        try {
                            $student->update(['telegram_chat_id' => $chatId]);
                        } catch (\Exception $ex) {
                            Log::warning("Gagal update telegram_chat_id di student: " . $ex->getMessage());
                        }

                        TelegramSubscriber::updateOrCreate(
                            ['chat_id' => $chatId],
                            [
                                'student_id'      => $student->id,
                                'parent_name'     => (string) $parentName,
                                'phone_number'    => (string) $cleanPhone,
                                'username'        => $username,
                                'user_type'       => $userType,
                                'is_active'       => true,
                                'approval_status' => 'approved',
                                'subscribed_at'   => now()->format('Y-m-d H:i:s'),
                            ]
                        );

                        $className = optional($student->classroom)->name ?? '-';
                        $roleLabel = $userType === 'student' ? 'Siswa' : 'Orang Tua/Wali';

                        $reply = "✅ *Pendaftaran Berhasil!*\n\n" .
                                "Akun Telegram Anda telah terhubung sebagai *{$roleLabel}*:\n" .
                                "👤 Nama Siswa: *{$student->name}*\n" .
                                "🏫 Kelas: *{$className}*\n\n" .
                                "📌 *Menu Layanan Bot:*\n" .
                                "🔹 `/izin` : Pengajuan Izin / Sakit\n" .
                                "🔹 `/rekap` : Menu Pilihan Rekap Absensi\n" .
                                "🔹 `/gerbang` : Rekap Kehadiran Gerbang\n" .
                                "🔹 `/pembelajaran` : Rekap Jam Belajar\n" .
                                "🔹 `/sholat` : Rekap Ibadah Sholat\n\n" .
                                "Silakan klik tombol di bawah untuk bergabung ke Grup Telegram Admin SISFO:";

                        $keyboard = [
                            [
                                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                            ]
                        ];

                        $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                    } else {
                        TelegramSubscriber::updateOrCreate(
                            ['chat_id' => $chatId],
                            [
                                'student_id'      => null,
                                'parent_name'     => (string) $parentName,
                                'phone_number'    => (string) $cleanPhone,
                                'username'        => $username,
                                'user_type'       => 'parent',
                                'is_active'       => false,
                                'approval_status' => 'pending',
                                'subscribed_at'   => now()->format('Y-m-d H:i:s'),
                            ]
                        );

                        $reply = "⚠️ *Nomor HP Anda (`+{$cleanPhone}`) belum terdaftar di database sekolah.*\n\n" .
                                "Silakan *ketik nomor NISN atau NIS* siswa di bawah ini untuk mengajukan pendaftaran ke Admin SISFO:";

                        $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                    }
                } catch (\Exception $e) {
                    Log::error("Error Telegram Contact Webhook: " . $e->getMessage());
                    $telegramService->sendMessageWithRemoveKeyboard($chatId, "⚠️ Terjadi kendala sistem. Silakan coba beberapa saat lagi.");
                }

                return response()->json(['status' => 'ok']);
            }

            // =========================================================================
            // 3. HANDLE PESAN TEKS & COMMAND BOT
            // =========================================================================
            $text      = trim($message['text'] ?? '');
            $lowerText = strtolower($text);
            $photo     = $message['photo'] ?? null;

            // A. COMMAND BATAL PROSES / BATALKAN PENGAJUAN PENDING
            if (in_array($lowerText, ['/batal', 'batal', '/batal_izin'])) {
                Cache::forget("telegram_permit_step_{$chatId}");

                $student = $this->getSubscriberStudent($chatId, $telegramService);

                if ($student) {
                    $today = Carbon::today()->format('Y-m-d');
                    $pendingIzin = IzinSiswa::where('student_id', $student->id)
                        ->whereDate('date', $today)
                        ->where('status', 'pending')
                        ->first();

                    if ($pendingIzin) {
                        $pendingIzin->delete();

                        $telegramService->sendMessage(
                            $chatId, 
                            "🗑️ *Pengajuan Izin/Sakit Dibatalkan & Dihapus!*\n\nData pengajuan untuk siswa *{$student->name}* pada hari ini telah berhasil dihapus dari sistem database.", 
                            'info', 
                            'Pengajuan Dibatalkan'
                        );

                        return response()->json(['status' => 'ok']);
                    }
                }

                $telegramService->sendMessage($chatId, "❌ *Proses pengajuan telah dibatalkan.*", 'info', 'Pengajuan Batal');
                return response()->json(['status' => 'ok']);
            }

            // B. COMMAND /izin ATAU /sakit (INISIASI PENGAJUAN)
            if (in_array($lowerText, ['/izin', 'izin', '/sakit', 'sakit'])) {
                Cache::forget("telegram_permit_step_{$chatId}");

                // Menggunakan helper teruji yang sama persis dengan /gerbang
                $student = $this->getSubscriberStudent($chatId, $telegramService);
                if (!$student) {
                    return response()->json(['status' => 'ok']);
                }

                $type = str_contains($lowerText, 'sakit') ? 'sakit' : 'izin';

                // Simpan State Conversational ke Cache selama 10 Menit
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
                          "_(Ketik `/batal` untuk membatalkan)_";

                $telegramService->sendMessage($chatId, $prompt, 'info', 'Pengajuan Izin');
                return response()->json(['status' => 'ok']);
            }

            // C. PROSES MULTI-STEP CONVERSATION PENGAJUAN IZIN/SAKIT
            $permitSession = Cache::get("telegram_permit_step_{$chatId}");

            if ($permitSession) {
                $student = Student::find($permitSession['student_id']);

                if ($student) {
                    // STEP 1: Menerima Teks Alasan
                    if ($permitSession['step'] === 'WAITING_REASON' && !empty($text) && !str_starts_with($text, '/')) {
                        $permitSession['reason'] = $text;

                        if ($permitSession['type'] === 'sakit') {
                            $permitSession['step'] = 'WAITING_PHOTO';
                            Cache::put("telegram_permit_step_{$chatId}", $permitSession, now()->addMinutes(10));

                            $prompt = "📸 *UNGGAH SURAT DOKTER / KETERANGAN SAKIT*\n\n" .
                                      "Silakan *kirimkan foto surat dokter* Anda ke chat ini.\n\n" .
                                      "_(Jika tidak ada foto surat dokter, ketik *'tanpa surat'* untuk melanjutkan)_";

                            $telegramService->sendMessage($chatId, $prompt, 'info', 'Upload Surat');
                            return response()->json(['status' => 'ok']);
                        } else {
                            // Ambil nomor WA dari data siswa secara aman
                            $phone = $student->phone ?? $student->student_phone;
                            $this->saveIzinToDatabase($student, 'izin', $permitSession['reason'], null, $phone, $chatId, $telegramService);
                            Cache::forget("telegram_permit_step_{$chatId}");
                            return response()->json(['status' => 'ok']);
                        }
                    }

                    // STEP 2: Menerima Foto / Konfirmasi Tanpa Surat (Khusus Sakit)
                    if ($permitSession['step'] === 'WAITING_PHOTO') {
                        $filePath = null;

                        if ($photo) {
                            $fileId = end($photo)['file_id'];
                            $filePath = $this->downloadTelegramFile($fileId, $student->nis ?? 'bot');
                        }

                        $phone = $student->phone ?? $student->student_phone;
                        $this->saveIzinToDatabase($student, 'sakit', $permitSession['reason'], $filePath, $phone, $chatId, $telegramService);
                        Cache::forget("telegram_permit_step_{$chatId}");
                        return response()->json(['status' => 'ok']);
                    }
                }
            }

            // D. COMMAND PERINTAH REKAP
            if (in_array($lowerText, ['/rekap', '/menu', 'rekap', 'menu'])) {
                $this->sendRekapMenu($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/gerbang', 'gerbang'])) {
                $this->sendRekapGerbang($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/pembelajaran', '/kelas', 'pembelajaran'])) {
                $this->sendRekapPembelajaran($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/sholat', 'sholat'])) {
                $this->sendRekapSholat($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            // E. PERINTAH /start ATAU /daftar
            if (str_starts_with($lowerText, '/start') || $lowerText === '/daftar') {

                // DEEP LINKING DARI DASHBOARD SISWA
                if (preg_match('/^\/start\s+student_([a-f0-9\-]+)$/i', $text, $matches)) {
                    $studentId = $matches[1];
                    $student   = Student::find($studentId);

                    if ($student) {
                        $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

                        if (empty($student->student_phone) && (!$subscriber || empty($subscriber->phone_number))) {
                            TelegramSubscriber::updateOrCreate(
                                ['chat_id' => $chatId],
                                [
                                    'student_id'      => $student->id,
                                    'parent_name'     => $student->name,
                                    'user_type'       => 'student',
                                    'is_active'       => false,
                                    'approval_status' => 'pending',
                                ]
                            );

                            $prompt = "📱 *Verifikasi Nomor Telepon Siswa*\n\n" .
                                      "Halo *{$student->name}*, nomor telepon Anda belum terdata di sistem sekolah.\n\n" .
                                      "Silakan tekan tombol *'📱 Bagikan Nomor Kontak Saya'* di bawah untuk mendaftarkan nomor HP Telegram Anda.";

                            $telegramService->sendContactButton($chatId, $prompt);
                            return response()->json(['status' => 'ok']);
                        }

                        if (empty($student->student_phone) && $subscriber && !empty($subscriber->phone_number)) {
                            try {
                                $student->update([
                                    'student_phone'    => $subscriber->phone_number,
                                    'telegram_chat_id' => $chatId,
                                ]);
                            } catch (\Exception $ex) {}
                        } else {
                            try {
                                $student->update(['telegram_chat_id' => $chatId]);
                            } catch (\Exception $ex) {}
                        }

                        $firstName   = $message['from']['first_name'] ?? '';
                        $lastName    = $message['from']['last_name'] ?? '';
                        $studentName = trim($firstName . ' ' . $lastName) ?: $student->name;
                        $username    = $message['from']['username'] ?? null;

                        TelegramSubscriber::updateOrCreate(
                            ['chat_id' => $chatId],
                            [
                                'student_id'      => $student->id,
                                'parent_name'     => $studentName,
                                'phone_number'    => $student->student_phone ?? ($subscriber->phone_number ?? null),
                                'username'        => $username,
                                'user_type'       => 'student',
                                'is_active'       => true,
                                'approval_status' => 'approved',
                                'subscribed_at'   => now()->format('Y-m-d H:i:s'),
                            ]
                        );

                        $className = optional($student->classroom)->name ?? '-';

                        $reply = "🎉 *Pendaftaran Berhasil!*\n\n" .
                                 "Akun Telegram Anda telah terhubung sebagai *Siswa*:\n" .
                                 "👤 Nama Siswa: *{$student->name}*\n" .
                                 "🏫 Kelas: *{$className}*\n" .
                                 "🆔 NISN: *{$student->nisn}*\n\n" .
                                 "📌 *Gunakan perintah bot berikut:*\n" .
                                 "🔹 `/izin` : Ajukan Izin / Sakit\n" .
                                 "🔹 `/rekap` : Cek Riwayat Absensi";

                        $keyboard = [
                            [
                                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                            ]
                        ];

                        $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                        return response()->json(['status' => 'ok']);
                    }
                }

                // CEK JIKA USER SUDAH APPROVED
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

                if ($subscriber && $subscriber->approval_status === 'approved' && $subscriber->is_active) {
                    $student     = Student::find($subscriber->student_id);
                    $studentName = $student ? $student->name : '-';
                    $className   = $student && $student->classroom ? $student->classroom->name : '-';
                    $roleLabel   = ($subscriber->user_type ?? 'parent') === 'student' ? 'Siswa' : 'Orang Tua/Wali';

                    $reply = "✅ *Akun Anda Sudah Terdaftar & Aktif!*\n\n" .
                             "👤 Peran: *{$roleLabel}*\n" .
                             "🎯 Siswa Terhubung: *{$studentName}*\n" .
                             "🏫 Kelas: *{$className}*\n\n" .
                             "📌 *Layanan Fitur Bot:*\n" .
                             "🔹 `/izin` : Form Pengajuan Izin / Sakit\n" .
                             "🔹 `/rekap` : Menu Rekap Interaktif\n" .
                             "🔹 `/gerbang` : Rekap Presensi Gerbang\n" .
                             "🔹 `/pembelajaran` : Rekap Presensi Kelas\n" .
                             "🔹 `/sholat` : Rekap Presensi Sholat\n\n" .
                             "Ketik `/batal` jika ingin membatalkan interaksi.";

                    $keyboard = [
                        [
                            ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                        ]
                    ];

                    $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                    return response()->json(['status' => 'ok']);
                }

                // PENDING STATUS
                if ($subscriber && $subscriber->approval_status === 'pending' && !$subscriber->is_active) {
                    $reply = "⏳ *Pendaftaran Anda Sedang Menunggu Verifikasi Admin*\n\n" .
                             "Mohon tunggu konfirmasi persetujuan dari Admin SISFO.";
                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                    return response()->json(['status' => 'ok']);
                }

                // BELUM TERDAFTAR
                $prompt = "🤖 *SELAMAT DATANG DI BOT SISFO Listrik SMKN 1 BUKITTINGGI*\n\n" .
                          "Bot ini menyediakan layanan pengajuan izin/sakit serta informasi rekapitulasi absensi gerbang, kelas, dan sholat.\n\n" .
                          "Silakan tekan tombol *'📱 Bagikan Nomor Kontak Saya'* di bawah untuk mendaftar.";
                
                $telegramService->sendContactButton($chatId, $prompt);
                return response()->json(['status' => 'ok']);
            }

            // F. COMMAND /help
            if ($lowerText === '/help') {
                $reply = "ℹ️ *BANTUAN BOT SISFO*\n\n" .
                         "📌 *Perintah Utama:*\n" .
                         "• `/izin` : Pengajuan Izin / Sakit Tidak Masuk\n" .
                         "• `/rekap` : Tampilkan menu rekap lengkap\n" .
                         "• `/gerbang` : Cek absensi di gerbang sekolah\n" .
                         "• `/pembelajaran` : Cek absensi di jam kelas\n" .
                         "• `/sholat` : Cek absensi ibadah sholat\n" .
                         "• `/batal` : Batal pengajuan/interaksi\n\n" .
                         "Butuh bantuan lebih lanjut? Bergabunglah dengan Grup Admin kami:";

                $keyboard = [
                    [
                        ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                    ]
                ];

                $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                return response()->json(['status' => 'ok']);
            }

            // G. HANDLE INPUT NISN/NIS PENDING
            $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

            if ($subscriber && $subscriber->approval_status === 'pending' && !$subscriber->student_id && !empty($text)) {
                $student = Student::where('nisn', $text)->orWhere('nis', $text)->first();

                if (!$student) {
                    $reply = "❌ *Siswa tidak ditemukan.*\n\n" .
                             "NISN/NIS *{$text}* tidak cocok dengan database sekolah. Silakan ketik ulang nomor NISN/NIS yang benar:";
                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                    return response()->json(['status' => 'ok']);
                }

                $subscriber->update(['student_id' => $student->id]);
                $className = optional($student->classroom)->name ?? '-';

                $reply = "⏳ *Pengajuan Diteruskan ke Admin!*\n\n" .
                         "Data Siswa Ditemukan:\n" .
                         "👤 Nama: *{$student->name}*\n" .
                         "🏫 Kelas: *{$className}*\n\n" .
                         "Mohon tunggu konfirmasi persetujuan dari Admin.";
                
                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                $this->notifyAdminGroup($subscriber, $student, $telegramService);

                return response()->json(['status' => 'ok']);
            }

        } catch (\Exception $e) {
            Log::error("Telegram Webhook Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        }

        return response()->json(['status' => 'ok']);
    }

    // =========================================================================
    // HELPER PENGAJUAN IZIN / SAKIT KE DATABASE & NOTIFIKASI APPROVAL
    // =========================================================================

    private function saveIzinToDatabase(Student $student, string $type, string $reason, ?string $filePath, ?string $phone, string $chatId, TelegramService $telegramService): void
    {
        $today = Carbon::today()->format('Y-m-d');

        // Cek jika siswa sudah memiliki pengajuan aktif (pending / approved) pada hari ini
        $existing = IzinSiswa::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            $statusText = $existing->status === 'approved' ? 'DISETUJUI' : 'PENDING VERIFIKASI';
            $telegramService->sendMessage($chatId, "⚠️ *Pengajuan Ditolak!* Siswa atas nama *{$student->name}* sudah memiliki pengajuan hari ini ({$today}) dengan status *{$statusText}*.", 'info', 'Sudah Ada Pengajuan');
            return;
        }

        // 1. Simpan ke tabel 'izin_siswas'
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

        // Keyboard Inline Pembatalan untuk Pemohon
        $userKeyboard = [
            [
                ['text' => '❌ Batalkan Pengajuan Saya', 'callback_data' => "cancel_user_izin_{$izin->id}"]
            ]
        ];

        // 2. Balas ke Pemohon dengan tombol Batal
        $telegramService->sendMessageWithInlineKeyboard($chatId, $replyMsg, $userKeyboard);

        // 3. Kirim Notifikasi Interaktif ke Grup Admin / Walas / BK / Kabeng / Proka (Terpisah per kelas jika ada)
        $this->notifyIzinToApprovers($izin, $student, $telegramService);
    }

    /**
     * Mengirimkan Notifikasi Pengajuan Izin Baru ke Grup Telegram Kelas / Admin dengan Tombol Approve/Reject
     */
    // private function notifyIzinToApprovers(IzinSiswa $izin, Student $student, TelegramService $telegramService): void
    // {
    //     $setting = AttendanceSetting::first();

    //     // 1. Cek Chat ID spesifik di level Kelas (Classroom)
    //     $classroomGroupChatId = optional($student->classroom)->telegram_chat_id;

    //     // 2. Fallback: Jika grup kelas kosong, gunakan Chat ID Admin dari DB/env
    //     $approverChatId = !empty($classroomGroupChatId) 
    //         ? $classroomGroupChatId 
    //         : ($setting?->telegram_admin_chat_id ?? config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID')));

    //     if (empty($approverChatId)) return;

    //     $className = optional($student->classroom)->name ?? '-';
    //     $jenisText = strtoupper($izin->type ?? 'IZIN');

    //     $text = "📩 *PENGAJUAN " . $jenisText . " SISWA BARU*\n\n" .
    //             "👤 Nama Siswa : *{$student->name}*\n" .
    //             "🏫 Kelas : *{$className}*\n" .
    //             "📅 Tanggal : *{$izin->date}*\n" .
    //             "📝 Alasan : _{$izin->reason}_\n" .
    //             "⏳ Status : *PENDING VERIFIKASI*\n\n" .
    //             "Mohon Wali Kelas, BK, Proka, Kabeng, atau Admin untuk memverifikasi pengajuan ini:";

    //     $keyboard = [
    //         [
    //             ['text' => '✅ Setujui (Approve)', 'callback_data' => "appizin_{$izin->id}"],
    //             ['text' => '❌ Tolak / Batalkan', 'callback_data' => "rejizin_{$izin->id}"]
    //         ]
    //     ];

    //     $telegramService->sendMessageWithInlineKeyboard($approverChatId, $text, $keyboard);
    // }

    /**
     * Mengirimkan Notifikasi Pengajuan Izin Baru (beserta Foto Surat jika ada) ke Grup Telegram Kelas / Admin
     */
    private function notifyIzinToApprovers(IzinSiswa $izin, Student $student, TelegramService $telegramService): void
    {
        $setting = AttendanceSetting::first();

        // 1. Cek Chat ID spesifik di level Kelas (Classroom)
        $classroomGroupChatId = optional($student->classroom)->telegram_chat_id;

        // 2. Fallback: Jika grup kelas kosong, gunakan Chat ID Admin dari DB/env
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

        // 3. JIKA ADA LAMPIRAN SURAT SAKIT, KIRIM PESAN FOTO KE GRUP
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
                Log::error("Gagal kirim foto surat ke grup: " . $e->getMessage());
            }
        }

        // 4. JIKA TANPA SURAT, KIRIM PESAN TEKS BIASA
        $telegramService->sendMessageWithInlineKeyboard($approverChatId, $text, $keyboard['inline_keyboard']);
    }

    /**
     * Handle Eksekusi Pembatalan Mandiri oleh Orang Tua / Siswa
     */
    private function handleUserCancelIzinCallback(array $callbackQuery, TelegramService $telegramService): void
    {
        $callbackId = $callbackQuery['id'];
        $data       = $callbackQuery['data'];
        $message    = $callbackQuery['message'];
        $chatId     = (string) $message['chat']['id'];
        $messageId  = $message['message_id'];

        if (!str_contains($data, '_')) return;

        $izinId = str_replace('cancel_user_izin_', '', $data);
        $izin   = IzinSiswa::find($izinId);

        if (!$izin) {
            $telegramService->answerCallbackQuery($callbackId, "Data pengajuan izin sudah tidak ditemukan.", true);
            return;
        }

        if ($izin->status !== 'pending') {
            $statusText = strtoupper($izin->status);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan tidak dapat dibatalkan karena sudah diproses (Status: {$statusText}).", true);
            return;
        }

        $studentName = optional($izin->student)->name ?? 'Siswa';

        // Hapus record dari database izin_siswas
        $izin->delete();

        // Edit pesan pada chat pemohon
        $userReply = "🗑️ *PENGAJUAN DIHAPUS / DIBATALKAN*\n\n" .
                      "Pengajuan izin/sakit untuk *{$studentName}* pada tanggal *{$izin->date}* telah berhasil Anda batalkan dan dihapus dari sistem.";

        $telegramService->editMessageText($chatId, $messageId, $userReply);
        $telegramService->answerCallbackQuery($callbackId, "Pengajuan izin berhasil dibatalkan dan dihapus.");
    }

    /**
     * Handle Eksekusi Klik Tombol Approve / Reject Izin oleh Admin/Walas/BK/Proka/Kabeng
     */
    private function handleIzinApprovalCallback(array $callbackQuery, TelegramService $telegramService): void
    {
        $callbackId = $callbackQuery['id'];
        $data       = $callbackQuery['data'];
        $message    = $callbackQuery['message'];
        $adminChat  = (string) $message['chat']['id'];
        $messageId  = $message['message_id'];
        $fromUser   = $callbackQuery['from'];
        $approver   = trim(($fromUser['first_name'] ?? '') . ' ' . ($fromUser['last_name'] ?? '')) ?: 'Petugas Sekolah';

        if (!str_contains($data, '_')) return;

        [$action, $izinId] = explode('_', $data);
        $izin = IzinSiswa::find($izinId);

        if (!$izin) {
            $telegramService->answerCallbackQuery($callbackId, "Data pengajuan izin tidak ditemukan.", true);
            return;
        }

        if ($izin->status !== 'pending') {
            $statusText = strtoupper($izin->status);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ini sudah diproses sebelumnya (Status: {$statusText}).", true);
            return;
        }

        $student     = Student::find($izin->student_id);
        $studentName = $student ? $student->name : 'Siswa';
        $className   = optional($student?->classroom)->name ?? '-';

        if ($action === 'appizin') {
            // 1. Update Status DB menjadi Approved
            $izin->update([
                'status'      => 'approved',
                'approved_by' => $approver,
                'approved_at' => now(),
            ]);

            // 2. Edit Pesan di Grup Admin/Guru
            $adminReply = "✅ *PENGAJUAN " . strtoupper($izin->type) . " DISETUJUI*\n\n" .
                          "👤 Siswa : *{$studentName}* ({$className})\n" .
                          "📅 Tanggal : *{$izin->date}*\n" .
                          "📝 Alasan : _{$izin->reason}_\n" .
                          "👨‍🏫 Diverifikasi Oleh : *{$approver}*";

            $telegramService->editMessageText($adminChat, $messageId, $adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Izin siswa berhasil disetujui!");

            // 3. Kirim Notifikasi Balasan ke Pemohon (Orang Tua / Siswa)
            if (!empty($izin->telegram_chat_id)) {
                $userMsg = "🎉 *PENGAJUAN " . strtoupper($izin->type) . " DISETUJUI!*\n\n" .
                           "Pengajuan izin/sakit untuk siswa *{$studentName}* pada tanggal *{$izin->date}* telah *DISETUJU & DIVERIFIKASI* oleh pihak sekolah.\n\n" .
                           "Terima kasih telah memberikan konfirmasi ketidakhadiran.";
                $telegramService->sendMessage($izin->telegram_chat_id, $userMsg, 'info', 'Persetujuan Izin');
            }

        } elseif ($action === 'rejizin') {
            // 1. Hapus Record dari Database agar Siswa Bisa Mengajukan Ulang
            $typeText = strtoupper($izin->type);
            $izinDate = $izin->date;
            $userChat = $izin->telegram_chat_id;

            $izin->delete();

            // 2. Edit Pesan di Grup Admin/Guru
            $adminReply = "🗑️ *PENGAJUAN " . $typeText . " DIBATALKAN & DIHAPUS*\n\n" .
                          "👤 Siswa : *{$studentName}* ({$className})\n" .
                          "📅 Tanggal : *{$izinDate}*\n" .
                          "👨‍🏫 Dibatalkan Oleh : *{$approver}*";

            $telegramService->editMessageText($adminChat, $messageId, $adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan izin berhasil dibatalkan dan dihapus.");

            // 3. Kirim Notifikasi Balasan ke Pemohon
            if (!empty($userChat)) {
                $userMsg = "❌ *PENGAJUAN " . $typeText . " DIBATALKAN / DIHAPUS*\n\n" .
                           "Pengajuan izin/sakit untuk siswa *{$studentName}* pada tanggal *{$izinDate}* telah dibatalkan oleh pihak sekolah.\n\n" .
                           "Catatan pengajuan telah dihapus dari database. Anda dapat melakukan pengajuan ulang jika terdapat kekeliruan.";
                $telegramService->sendMessage($userChat, $userMsg, 'info', 'Izin Dibatalkan');
            }
        }
    }

    // Download Foto Surat Dokter dari Server Telegram API dan Simpan ke public/storage/surat_sakit/
    private function downloadTelegramFile(string $fileId, string $nis): ?string
    {
        try {
            $token = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
            if (empty($token)) return null;
            
            $res = Http::get("https://api.telegram.org/bot{$token}/getFile?file_id={$fileId}");
            if (!$res->successful()) return null;

            $filePathOnTelegram = $res->json('result.file_path');
            $fileContent = Http::get("https://api.telegram.org/file/bot{$token}/{$filePathOnTelegram}")->body();

            // Diselaraskan dengan folder 'surat_sakit' yang ada di public/storage
            $folder = 'surat_sakit/' . date('Y-m-d');
            $filename = "surat_telegram_{$nis}_" . time() . ".jpg";
            $fullPath = "{$folder}/{$filename}";

            Storage::disk('public')->put($fullPath, $fileContent);

            return $fullPath;
        } catch (\Exception $e) {
            Log::error("Download Telegram File Error: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // HELPER LOGIKA FUNGSI REKAP ABSENSI
    // =========================================================================

    private function getSubscriberStudent($chatId, TelegramService $telegramService)
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

    private function sendRekapMenu($chatId, TelegramService $telegramService)
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
                ['text' => '🕌 Presensi Sholat', 'callback_data' => 'rekap_sholat']
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function sendRekapGerbang($chatId, TelegramService $telegramService)
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

        $keyboard = [
            [
                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function sendRekapPembelajaran($chatId, TelegramService $telegramService)
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

        $keyboard = [
            [
                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function sendRekapSholat($chatId, TelegramService $telegramService)
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

        $keyboard = [
            [
                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function handleRekapCallback(array $callbackQuery, TelegramService $telegramService): void
    {
        $chatId = (string) $callbackQuery['message']['chat']['id'];
        $data   = $callbackQuery['data'];

        if ($data === 'rekap_gerbang') {
            $this->sendRekapGerbang($chatId, $telegramService);
        } elseif ($data === 'rekap_pembelajaran') {
            $this->sendRekapPembelajaran($chatId, $telegramService);
        } elseif ($data === 'rekap_sholat') {
            $this->sendRekapSholat($chatId, $telegramService);
        }
    }

    private function notifyAdminGroup(TelegramSubscriber $subscriber, Student $student, TelegramService $telegramService): void
    {
        $setting          = AttendanceSetting::first();
        $adminGroupChatId = $setting?->telegram_admin_chat_id 
            ?? config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID'));

        if (empty($adminGroupChatId)) return;

        $className = optional($student->classroom)->name ?? '-';
        $roleLabel = ($subscriber->user_type ?? 'parent') === 'student' ? 'Siswa' : 'Orang Tua';

        $text = "📩 *Pengajuan Pendaftaran Telegram*\n\n" .
                "👤 Pemohon: *{$subscriber->parent_name}* ({$roleLabel})\n" .
                "📞 No HP: `+{$subscriber->phone_number}`\n" .
                "🎯 Siswa: *{$student->name}* ({$className})\n" .
                "🆔 NISN: `{$student->nisn}`\n\n" .
                "Apakah Anda ingin menyetujui penghubungan akun ini?";

        $keyboard = [
            [
                ['text' => '✅ Setujui', 'callback_data' => "approve_{$subscriber->id}"],
                ['text' => '❌ Tolak', 'callback_data' => "reject_{$subscriber->id}"]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($adminGroupChatId, $text, $keyboard);
    }

    private function handleAdminCallback(array $callbackQuery, TelegramService $telegramService): void
    {
        $callbackId = $callbackQuery['id'];
        $data       = $callbackQuery['data'];
        $message    = $callbackQuery['message'];
        $adminChat  = (string) $message['chat']['id'];
        $messageId  = $message['message_id'];

        if (!str_contains($data, '_')) return;

        [$action, $subscriberId] = explode('_', $data);
        $subscriber = TelegramSubscriber::find($subscriberId);

        if (!$subscriber || $subscriber->approval_status === 'approved') {
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ini sudah diproses.", true);
            return;
        }

        $student   = Student::find($subscriber->student_id);
        $setting   = AttendanceSetting::first();
        $groupLink = $setting?->telegram_admin_group_link 
            ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

        if ($action === 'approve') {
            $subscriber->update([
                'approval_status' => 'approved',
                'is_active'       => true,
                'subscribed_at'   => now()->format('Y-m-d H:i:s'),
            ]);

            if ($student) {
                try {
                    $student->update(['telegram_chat_id' => $subscriber->chat_id]);
                } catch (\Exception $ex) {}
            }

            $className = optional($student?->classroom)->name ?? '-';

            $replyOrtu = "🎉 *Pendaftaran Disetujui Admin!*\n\n" .
                         "Akun Telegram Anda telah diverifikasi untuk siswa:\n" .
                         "👤 Nama: *{$student->name}*\n" .
                         "🏫 Kelas: *{$className}*\n\n" .
                         "Gunakan perintah `/rekap` untuk melihat rekap absensi.\n\n" .
                         "Silakan bergabung ke Grup Telegram Admin SISFO di bawah ini:";

            $keyboard = [
                [
                    ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                ]
            ];

            $telegramService->sendMessageWithInlineKeyboard($subscriber->chat_id, $replyOrtu, $keyboard);

            $adminReply = "✅ *Pengajuan Disetujui*\n" .
                          "Pemohon: {$subscriber->parent_name} (+{$subscriber->phone_number})\n" .
                          "Siswa: " . ($student ? $student->name : '-');
            
            $telegramService->editMessageText($adminChat, $messageId, $adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pendaftaran berhasil disetujui!");

        } elseif ($action === 'reject') {
            $subscriber->update([
                'approval_status' => 'rejected',
                'is_active'       => false,
            ]);

            $replyOrtu = "❌ *Pendaftaran Ditolak Admin*\n\n" .
                         "Pengajuan pendaftaran akun Telegram Anda tidak dapat diverifikasi.";
            
            $telegramService->sendMessageWithRemoveKeyboard($subscriber->chat_id, $replyOrtu);

            $adminReply = "❌ *Pengajuan Ditolak*\n" .
                          "Pemohon: {$subscriber->parent_name} (+{$subscriber->phone_number})";

            $telegramService->editMessageText($adminChat, $messageId, $adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ditolak.");
        }
    }
}