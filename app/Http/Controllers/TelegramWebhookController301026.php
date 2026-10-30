<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Services\TelegramService;
use App\Models\TelegramSubscriber;
use Illuminate\Support\Facades\Log;

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

        $update = $request->all();

        // 1. HANDLE CALLBACK QUERY (RESPON TOMBOL INLINE DARI ADMIN TELEGRAM)
        if (isset($update['callback_query'])) {
            $this->handleAdminCallback($update['callback_query'], $telegramService);
            return response()->json(['status' => 'ok']);
        }

        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $chatId  = (string) $update['message']['chat']['id'];
        $message = $update['message'];

        // 2. HANDLE BAGIKAN KONTAK (TEKAN TOMBOL SHARE CONTACT)
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

                // Cek pencocokan nomor HP
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
                    // SKENARIO A: NOMOR HP TERDAFTAR (LANGSUNG APPROVED)
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
                            "Notifikasi presensi harian akan otomatis dikirimkan ke akun ini.";

                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                } else {
                    // SKENARIO B: NOMOR HP TIDAK TERDAFTAR -> MASUK STATUS PENDING & MINTA NISN/NIS
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
                Log::error("Error Telegram Contact Webhook: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                $telegramService->sendMessageWithRemoveKeyboard($chatId, "⚠️ Terjadi kendala sistem. Silakan coba beberapa saat lagi atau hubungi Admin SISFO.");
            }

            return response()->json(['status' => 'ok']);
        }

        // 3. HANDLE PESAN TEKS & COMMAND BOT
        $text      = trim($message['text'] ?? '');
        $lowerText = strtolower($text);

        // Perintah /start atau /daftar
        if (str_starts_with($lowerText, '/start') || $lowerText === '/daftar') {

            // A. DEEP LINKING DARI DASHBOARD SISWA (/start student_ID)
            if (preg_match('/^\/start\s+student_([a-f0-9\-]+)$/i', $text, $matches)) {
                $studentId = $matches[1];
                $student   = Student::find($studentId);

                if ($student) {
                    $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

                    // VALIDASI NOMOR HP SISWA:
                    // Jika student_phone di database masih kosong, minta bagikan kontak untuk pengisian otomatis
                    if (empty($student->student_phone) && (!$subscriber || empty($subscriber->phone_number))) {
                        
                        // Simpan draf subscriber sementara agar bot ingat siswa yang sedang mendaftar
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
                                  "Silakan tekan tombol *'📱 Bagikan Nomor Kontak Saya'* di bawah untuk mendaftarkan nomor HP Telegram Anda secara otomatis ke sistem.";

                        $telegramService->sendContactButton($chatId, $prompt);
                        return response()->json(['status' => 'ok']);
                    }

                    // Jika student_phone di DB kosong TAPI siswa sudah mengirim kontak, update student_phone secara otomatis!
                    if (empty($student->student_phone) && $subscriber && !empty($subscriber->phone_number)) {
                        try {
                            $student->update([
                                'student_phone'    => $subscriber->phone_number,
                                'telegram_chat_id' => $chatId,
                            ]);
                        } catch (\Exception $ex) {
                            Log::warning("Gagal update student_phone: " . $ex->getMessage());
                        }
                    } else {
                        // Jika student_phone sudah ada di DB, langsung update telegram_chat_id
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
                             "🆔 NISN: *{$student->nisn}*\n" .
                             "📱 No. HP: `+" . ($student->student_phone ?? $subscriber->phone_number ?? '-') . "`\n\n" .
                             "Notifikasi presensi harian akan otomatis dikirimkan ke akun ini.";

                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                    return response()->json(['status' => 'ok']);
                }
            }

            // B. CEK JIKA USER SUDAH APPROVED
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
                         "Anda tidak perlu mendaftar ulang. Notifikasi presensi akan otomatis dikirimkan ke akun ini.\n\n" .
                         "Ketik `/batal` jika ingin berhenti berlangganan.";

                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                return response()->json(['status' => 'ok']);
            }

            // C. JIKA AKUN MENUNGGU PERSETUJUAN / INPUT NISN (STATUS PENDING)
            if ($subscriber && $subscriber->approval_status === 'pending' && !$subscriber->is_active) {
                if ($subscriber->student_id) {
                    $student     = Student::find($subscriber->student_id);
                    $studentName = $student ? $student->name : '-';

                    $reply = "⏳ *Pendaftaran Anda Sedang Menunggu Verifikasi Admin*\n\n" .
                             "Pengajuan untuk siswa *{$studentName}* telah diterima dan sedang diproses oleh Admin SISFO. Mohon tunggu konfirmasi persetujuan.";
                } else {
                    $reply = "⚠️ *Status Pendaftaran Belum Lengkap*\n\n" .
                             "Nomor HP Anda belum terdaftar di sistem. Silakan *ketik nomor NISN atau NIS* siswa sekarang untuk mengajukan pendaftaran:";
                }

                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                return response()->json(['status' => 'ok']);
            }

            // D. JIKA BELUM TERDAFTAR atau AKUN NONAKTIF -> Tampilkan tombol Share Contact
            $prompt = "🤖 *SELAMAT DATANG DI BOT SISFO SMKN 1 BUKITTINGGI*\n\n" .
                      "Bot ini memberikan layanan notifikasi presensi siswa secara *real-time* langsung ke akun Telegram Orang Tua/Wali & Siswa.\n\n" .
                      "📌 *Panduan Perintah Bot:*\n" .
                      "🔹 `/start` atau `/daftar` : Cek status / pendaftaran\n" .
                      "🔹 `/batal` atau `/stop` : Membatalkan notifikasi Telegram\n" .
                      "🔹 `/help` : Informasi bantuan\n\n" .
                      "--------------------------------------\n" .
                      "Langkah Pendaftaran:\n" .
                      "Silakan tekan tombol *'📱 Bagikan Nomor Kontak Saya'* di bawah ini untuk menghubungkan akun Telegram Anda dengan data siswa.";
            
            $telegramService->sendContactButton($chatId, $prompt);
            return response()->json(['status' => 'ok']);
        }

        // Perintah /help
        if ($lowerText === '/help') {
            $reply = "ℹ️ *BANTUAN BOT NOTIFIKASI SISFO*\n\n" .
                     "1️⃣ *Bagaimana cara mendaftar?*\n" .
                     "Ketik `/start` lalu tekan tombol *'📱 Bagikan Nomor Kontak Saya'*.\n\n" .
                     "2️⃣ *Nomor HP tidak terdaftar?*\n" .
                     "Ketik *NISN atau NIS* siswa saat diminta untuk mengajukan verifikasi ke Admin SISFO.\n\n" .
                     "3️⃣ *Bagaimana berhenti berlangganan?*\n" .
                     "Ketik `/batal` atau `/stop` kapan saja.";

            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            return response()->json(['status' => 'ok']);
        }

        // Perintah /batal atau /stop
        if ($lowerText === '/batal' || $lowerText === '/stop') {
            $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

            if ($subscriber && $subscriber->is_active) {
                $subscriber->update([
                    'is_active'       => false,
                    'approval_status' => 'rejected'
                ]);

                if ($subscriber->student_id) {
                    try {
                        Student::where('id', $subscriber->student_id)->update(['telegram_chat_id' => null]);
                    } catch (\Exception $ex) {}
                }

                $reply = "❌ *Pendaftaran Dibatalkan!*\n\n" .
                         "Akun Telegram Anda telah dinonaktifkan dari sistem notifikasi sekolah.\n\n" .
                         "Ketik `/start` atau `/daftar` jika ingin mendaftar kembali.";
            } else {
                $reply = "ℹ️ Akun Telegram Anda tidak terdaftar atau sudah nonaktif di sistem kami.";
            }

            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            return response()->json(['status' => 'ok']);
        }

        // 4. HANDLE PROSES INPUT NISN / NIS DARI PENGGUNA
        $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

        if ($subscriber && $subscriber->approval_status === 'pending' && !$subscriber->student_id && !empty($text)) {
            $student = Student::where('nisn', $text)->orWhere('nis', $text)->first();

            if (!$student) {
                $reply = "❌ *Siswa tidak ditemukan.*\n\n" .
                         "NISN/NIS *{$text}* tidak cocok dengan database sekolah. Silakan periksa kembali dan ketik ulang nomor NISN/NIS yang benar:";
                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                return response()->json(['status' => 'ok']);
            }

            $subscriber->update([
                'student_id' => $student->id,
            ]);

            $className = optional($student->classroom)->name ?? '-';

            $reply = "⏳ *Pengajuan Diteruskan ke Admin!*\n\n" .
                     "Data Siswa Ditemukan:\n" .
                     "👤 Nama: *{$student->name}*\n" .
                     "🏫 Kelas: *{$className}*\n\n" .
                     "Pengajuan Anda telah diteruskan ke *Admin SISFO*. Mohon tunggu konfirmasi persetujuan dari Admin untuk mengaktifkan notifikasi.";
            
            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);

            $this->notifyAdminGroup($subscriber, $student, $telegramService);

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ok']);
    }

    private function notifyAdminGroup(TelegramSubscriber $subscriber, Student $student, TelegramService $telegramService): void
    {
        $adminGroupChatId = config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID'));

        if (empty($adminGroupChatId)) {
            return;
        }

        $className = optional($student->classroom)->name ?? '-';
        $roleLabel = ($subscriber->user_type ?? 'parent') === 'student' ? 'Siswa' : 'Orang Tua';

        $text = "📩 *Pengajuan Pendaftaran Telegram*\n\n" .
                "👤 Pemohon: *{$subscriber->parent_name}* ({$roleLabel})\n" .
                "📞 No HP: `+{$subscriber->phone_number}`\n" .
                "🎯 Siswa: *{$student->name}* ({$className})\n" .
                "🆔 NISN: `{$student->nisn}`\n\n" .
                "Apakah Anda ingin menyetujui penghubungan akun ini?";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Setujui', 'callback_data' => "approve_{$subscriber->id}"],
                    ['text' => '❌ Tolak', 'callback_data' => "reject_{$subscriber->id}"]
                ]
            ]
        ];

        $telegramService->sendMessage($adminGroupChatId, $text, 'pendaftaran', 'Pengajuan Ortu/Siswa', $keyboard);
    }

    private function handleAdminCallback(array $callbackQuery, TelegramService $telegramService): void
    {
        $callbackId = $callbackQuery['id'];
        $data       = $callbackQuery['data'];
        $message    = $callbackQuery['message'];
        $adminChat  = (string) $message['chat']['id'];
        $messageId  = $message['message_id'];

        if (!str_contains($data, '_')) {
            return;
        }

        [$action, $subscriberId] = explode('_', $data);
        $subscriber = TelegramSubscriber::find($subscriberId);

        if (!$subscriber || $subscriber->approval_status === 'approved') {
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ini sudah diproses atau tidak valid.", true);
            return;
        }

        $student = Student::find($subscriber->student_id);

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
                         "Akun Telegram Anda telah diverifikasi dan dihubungkan dengan siswa:\n" .
                         "👤 Nama: *{$student->name}*\n" .
                         "🏫 Kelas: *{$className}*\n\n" .
                         "Notifikasi presensi harian sudah aktif.";
            
            $telegramService->sendMessageWithRemoveKeyboard($subscriber->chat_id, $replyOrtu);

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
                         "Mohon maaf, pengajuan pendaftaran akun Telegram Anda tidak dapat diverifikasi.\n" .
                         "Silakan hubungi pihak sekolah / Admin SISFO untuk pembaruan data.";
            
            $telegramService->sendMessageWithRemoveKeyboard($subscriber->chat_id, $replyOrtu);

            $adminReply = "❌ *Pengajuan Ditolak*\n" .
                          "Pemohon: {$subscriber->parent_name} (+{$subscriber->phone_number})\n" .
                          "Siswa: " . ($student ? $student->name : '-');

            $telegramService->editMessageText($adminChat, $messageId, $adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ditolak.");
        }
    }
}