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

        // 1. HANDLE CALLBACK QUERY (RESPON TOMBOL SETUJUI / TOLAK DARI ADMIN TELEGRAM)
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
                $parentName = trim($firstName . ' ' . $lastName) ?: 'Orang Tua';
                $username   = $message['from']['username'] ?? null;

                $lastDigits = strlen($cleanPhone) >= 9 ? substr($cleanPhone, -9) : $cleanPhone;

                // Mencari pencocokan ke kolom phone (HP Ortu) atau student_phone (HP Siswa)
                $student = Student::where(function ($q) use ($lastDigits) {
                    $q->where('phone', 'like', '%' . $lastDigits)
                      ->orWhere('student_phone', 'like', '%' . $lastDigits);
                })->first();

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
                            'parent_name'     => $parentName,
                            'phone_number'    => $cleanPhone,
                            'username'        => $username,
                            'is_active'       => true,
                            'approval_status' => 'approved',
                            'subscribed_at'   => now(),
                        ]
                    );

                    $className = optional($student->classroom)->name ?? '-';

                    $reply = "✅ *Pendaftaran Berhasil!*\n\n" .
                            "Akun Telegram Anda telah terhubung dengan siswa:\n" .
                            "👤 Nama: *{$student->name}*\n" .
                            "🏫 Kelas: *{$className}*\n\n" .
                            "Notifikasi presensi harian akan otomatis dikirimkan ke akun ini.";

                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                } else {
                    // SKENARIO B: NOMOR HP TIDAK TERDAFTAR -> MINTA INPUT NISN/NIS
                    TelegramSubscriber::updateOrCreate(
                        ['chat_id' => $chatId],
                        [
                            'student_id'      => null,
                            'parent_name'     => $parentName,
                            'phone_number'    => $cleanPhone,
                            'username'        => $username,
                            'is_active'       => false,
                            'approval_status' => 'pending_nisn',
                            'subscribed_at'   => now(), // Mengisi value agar lolos Not Null PostgreSQL
                        ]
                    );

                    $reply = "⚠️ *Nomor HP Anda (`+{$cleanPhone}`) belum terdaftar di database sekolah.*\n\n" .
                            "Silakan *ketik nomor NISN atau NIS* putra/putri Anda di bawah ini untuk mengajukan verifikasi pendaftaran ke Admin SISFO:";

                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                }
            } catch (\Exception $e) {
                Log::error("Error Telegram Contact Webhook: " . $e->getMessage() . " File: " . $e->getFile() . " Line: " . $e->getLine());
                $telegramService->sendMessageWithRemoveKeyboard($chatId, "⚠️ Terjadi kendala sistem. Silakan coba beberapa saat lagi atau hubungi Admin SISFO.");
            }

            return response()->json(['status' => 'ok']);
        }

        // 3. HANDLE PESAN TEKS & COMMAND BOT
        $text      = trim($message['text'] ?? '');
        $lowerText = strtolower($text);

        // Perintah /start atau /daftar
        if ($lowerText === '/start' || $lowerText === '/daftar') {
            $prompt = "🤖 *SELAMAT DATANG DI BOT SISFO SMKN 1 BUKITTINGGI*\n\n" .
                      "Bot ini memberikan layanan notifikasi presensi siswa secara *real-time* langsung ke akun Telegram Orang Tua/Wali.\n\n" .
                      "📌 *Panduan Perintah Bot:*\n" .
                      "🔹 `/start` atau `/daftar` : Menampilkan tombol pendaftaran\n" .
                      "🔹 `/batal` atau `/stop` : Membatalkan notifikasi Telegram\n" .
                      "🔹 `/help` : Informasi bantuan pendaftaran\n\n" .
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
                     "Ketik *NISN atau NIS* putra/putri Anda setelah membagikan kontak untuk mengajukan verifikasi ke Admin SISFO.\n\n" .
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

        // 4. HANDLE PROSES INPUT NISN / NIS OLEH ORANG TUA
        $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

        if ($subscriber && in_array($subscriber->approval_status, ['pending_nisn', 'pending']) && !empty($text)) {
            $student = Student::where('nisn', $text)->orWhere('nis', $text)->first();

            if (!$student) {
                $reply = "❌ *Siswa tidak ditemukan.*\n\n" .
                         "NISN/NIS *{$text}* tidak cocok dengan database sekolah. Silakan periksa kembali dan ketik ulang nomor NISN/NIS yang benar:";
                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                return response()->json(['status' => 'ok']);
            }

            // Hubungkan student_id & ubah status menjadi pending_approval
            $subscriber->update([
                'student_id'      => $student->id,
                'approval_status' => 'pending_approval'
            ]);

            $className = optional($student->classroom)->name ?? '-';

            $reply = "⏳ *Pengajuan Diteruskan ke Admin!*\n\n" .
                     "Data Siswa Ditemukan:\n" .
                     "👤 Nama: *{$student->name}*\n" .
                     "🏫 Kelas: *{$className}*\n\n" .
                     "Pengajuan Anda telah diteruskan ke *Admin SISFO*. Mohon tunggu konfirmasi persetujuan dari Admin untuk mengaktifkan notifikasi.";
            
            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);

            // Kirim pemberitahuan ke grup Telegram Admin (opsional jika dikonfigurasi)
            $this->notifyAdminGroup($subscriber, $student, $telegramService);

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Mengirimkan Notifikasi ke Grup Telegram Admin
     */
    private function notifyAdminGroup(TelegramSubscriber $subscriber, Student $student, TelegramService $telegramService): void
    {
        $adminGroupChatId = config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID'));

        if (empty($adminGroupChatId)) {
            return;
        }

        $className = optional($student->classroom)->name ?? '-';

        $text = "📩 *Pengajuan Pendaftaran Telegram Ortu*\n\n" .
                "👤 Ortu: *{$subscriber->parent_name}*\n" .
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

        $telegramService->sendMessage($adminGroupChatId, $text, 'pendaftaran', 'Pengajuan Ortu', $keyboard);
    }

    /**
     * Menangani Klik Tombol Setujui / Tolak oleh Admin di Telegram
     */
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

        if (!$subscriber || !in_array($subscriber->approval_status, ['pending_approval', 'pending_nisn', 'pending'])) {
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ini sudah diproses atau tidak valid.", true);
            return;
        }

        $student = Student::find($subscriber->student_id);

        if ($action === 'approve') {
            $subscriber->update([
                'approval_status' => 'approved',
                'is_active'       => true,
                'subscribed_at'   => now(),
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
                          "Ortu: {$subscriber->parent_name} (+{$subscriber->phone_number})\n" .
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
                          "Ortu: {$subscriber->parent_name} (+{$subscriber->phone_number})\n" .
                          "Siswa: " . ($student ? $student->name : '-');

            $telegramService->editMessageText($adminChat, $messageId, $adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ditolak.");
        }
    }
}