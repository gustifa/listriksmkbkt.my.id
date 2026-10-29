<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Services\TelegramService;
use App\Models\TelegramSubscriber;

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

        // -------------------------------------------------------------------
        // A. HANDLE CALLBACK QUERY (TINDAKAN ADMIN: SETUJUI / TOLAK)
        // -------------------------------------------------------------------
        if (isset($update['callback_query'])) {
            $this->handleAdminCallback($update['callback_query'], $telegramService);
            return response()->json(['status' => 'ok']);
        }

        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $chatId  = (string) $update['message']['chat']['id'];
        $message = $update['message'];

        // -------------------------------------------------------------------
        // B. HANDLE BAGIKAN KONTAK (NOMOR HP ORTU)
        // -------------------------------------------------------------------
        if (isset($message['contact'])) {
            $contact    = $message['contact'];
            $phone      = $contact['phone_number'];
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

            $firstName  = $contact['first_name'] ?? '';
            $lastName   = $contact['last_name'] ?? '';
            $parentName = trim($firstName . ' ' . $lastName) ?: 'Orang Tua';
            $username   = $message['from']['username'] ?? null;

            // Cocokkan nomor HP dengan database siswa
            $student = Student::where('phone', 'like', '%' . substr($cleanPhone, -9))->first();

            if ($student) {
                // SKENARIO 1: NOMOR HP TERDAFTAR (LANGSUNG AKTIF)
                $student->update(['telegram_chat_id' => $chatId]);

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

                $reply = "✅ *Pendaftaran Berhasil!*\n\n" .
                        "Akun Telegram Anda telah terhubung dengan siswa:\n" .
                        "👤 Nama: *{$student->name}*\n" .
                        "🏫 Kelas: " . ($student->classroom ? $student->classroom->name : '-') . "\n\n" .
                        "Notifikasi presensi harian akan otomatis dikirimkan ke akun ini.";

                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            } else {
                // SKENARIO 2: NOMOR HP TIDAK TERDAFTAR (MINTA INPUT NISN/NIS)
                TelegramSubscriber::updateOrCreate(
                    ['chat_id' => $chatId],
                    [
                        'student_id'      => null,
                        'parent_name'     => $parentName,
                        'phone_number'    => $cleanPhone,
                        'username'        => $username,
                        'is_active'       => false,
                        'approval_status' => 'pending_nisn',
                        'subscribed_at'   => null,
                    ]
                );

                $reply = "⚠️ *Nomor HP Anda (`+{$cleanPhone}`) belum terdaftar di database sekolah.*\n\n" .
                        "Silakan *ketik NISN atau NIS* putra/putri Anda di bawah ini untuk mengajukan pendaftaran ke Admin:";

                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            }

            return response()->json(['status' => 'ok']);
        }

        // -------------------------------------------------------------------
        // C. HANDLE PESAN TEKS & PERINTAH BOT
        // -------------------------------------------------------------------
        $text = trim($message['text'] ?? '');
        $lowerText = strtolower($text);

        // 1. Pendaftaran (/start atau /daftar)
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

        // 2. Bantuan (/help)
        if ($lowerText === '/help') {
            $reply = "ℹ️ *BANTUAN BOT NOTIFIKASI SISFO*\n\n" .
                     "1️⃣ *Bagaimana cara mendaftar?*\n" .
                     "Ketik `/start` lalu tekan tombol *'📱 Bagikan Nomor Kontak Saya'*.\n\n" .
                     "2️⃣ *Nomor HP beda dengan database?*\n" .
                     "Jika nomor HP Anda belum terdata, sistem akan meminta Anda menginputkan *NISN / NIS* putra/putri Anda untuk diverifikasi Admin SISFO.\n\n" .
                     "3️⃣ *Bagaimana berhenti berlangganan?*\n" .
                     "Ketik `/batal` atau `/stop` kapan saja.\n\n" .
                     "Jika mengalami kendala, silakan hubungi *Admin SISFO SMKN 1 Bukittinggi*.";

            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            return response()->json(['status' => 'ok']);
        }

        // 3. Pembatalan Pendaftaran (/batal atau /stop)
        if ($lowerText === '/batal' || $lowerText === '/stop') {
            $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

            if ($subscriber && $subscriber->is_active) {
                $subscriber->update([
                    'is_active'       => false,
                    'approval_status' => 'rejected'
                ]);

                if ($subscriber->student_id) {
                    Student::where('id', $subscriber->student_id)
                        ->update(['telegram_chat_id' => null]);
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

        // 4. HANDLE INPUT NISN/NIS DARI ORTU (JIKA STATUS SUBSCRIBER = pending_nisn)
        $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

        if ($subscriber && $subscriber->approval_status === 'pending_nisn' && !empty($text)) {
            $student = Student::where('nisn', $text)->orWhere('nis', $text)->first();

            if (!$student) {
                $reply = "❌ *Siswa tidak ditemukan.*\n\n" .
                         "NISN/NIS *{$text}* tidak cocok dengan data kami. Silakan periksa kembali dan ketik ulang nomor NISN/NIS yang benar:";
                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                return response()->json(['status' => 'ok']);
            }

            // Update subscriber: kaitkan student_id & ubah status ke pending_approval
            $subscriber->update([
                'student_id'      => $student->id,
                'approval_status' => 'pending_approval'
            ]);

            // Balasan ke Ortu
            $reply = "⏳ *Pendaftaran Dalam Proses Verifikasi!*\n\n" .
                     "Data Siswa Ditemukan:\n" .
                     "👤 Nama: *{$student->name}*\n" .
                     "🏫 Kelas: " . ($student->classroom ? $student->classroom->name : '-') . "\n\n" .
                     "Pengajuan Anda telah diteruskan ke *Admin SISFO*. Mohon tunggu konfirmasi persetujuan dari Admin untuk mengaktifkan notifikasi.";
            
            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);

            // Kirim Notifikasi Pengajuan ke Grup Admin SISFO
            $this->notifyAdminGroup($subscriber, $student, $telegramService);

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Kirim Notifikasi Pengajuan ke Grup Telegram Admin
     */
    private function notifyAdminGroup(TelegramSubscriber $subscriber, Student $student, TelegramService $telegramService): void
    {
        $adminGroupChatId = config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID'));

        if (empty($adminGroupChatId)) {
            return;
        }

        $text = "📩 *Pengajuan Pendaftaran Telegram Ortu*\n\n" .
                "👤 Ortu: *{$subscriber->parent_name}*\n" .
                "📞 No HP: `+{$subscriber->phone_number}`\n" .
                "🎯 Siswa: *{$student->name}* (" . ($student->classroom ? $student->classroom->name : '-') . ")\n" .
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
     * Handle Eksekusi Tombol Approve/Reject dari Admin
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

        if (!$subscriber || $subscriber->approval_status !== 'pending_approval') {
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ini sudah diproses atau tidak valid.", true);
            return;
        }

        $student = Student::find($subscriber->student_id);

        if ($action === 'approve') {
            // Update Subscriber
            $subscriber->update([
                'approval_status' => 'approved',
                'is_active'       => true,
                'subscribed_at'   => now(),
            ]);

            // Update Student telegram_chat_id & phone ortu
            if ($student) {
                $student->update([
                    'telegram_chat_id' => $subscriber->chat_id,
                    'phone'            => $subscriber->phone_number
                ]);
            }

            // Notifikasi Ke Ortu
            $replyOrtu = "🎉 *Pendaftaran Disetujui Admin!*\n\n" .
                         "Akun Telegram Anda telah diverifikasi dan dihubungkan dengan siswa:\n" .
                         "👤 Nama: *{$student->name}*\n" .
                         "🏫 Kelas: " . ($student->classroom ? $student->classroom->name : '-') . "\n\n" .
                         "Notifikasi presensi harian sudah aktif.";
            
            $telegramService->sendMessageWithRemoveKeyboard($subscriber->chat_id, $replyOrtu);

            // Update Pesan di Grup Admin
            $adminReply = "✅ *Pengajuan Disetujui*\n" .
                          "Ortu: {$subscriber->parent_name} (+{$subscriber->phone_number})\n" .
                          "Siswa: {$student->name}";
            
            $telegramService->editMessageText($adminChat, $messageId, $adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pendaftaran berhasil disetujui!");

        } elseif ($action === 'reject') {
            $subscriber->update([
                'approval_status' => 'rejected',
                'is_active'       => false,
            ]);

            // Notifikasi Ke Ortu
            $replyOrtu = "❌ *Pendaftaran Ditolak Admin*\n\n" .
                         "Mohon maaf, pengajuan pendaftaran akun Telegram Anda tidak dapat diverifikasi.\n" .
                         "Silakan hubungi pihak sekolah / Admin SISFO untuk pembaruan data.";
            
            $telegramService->sendMessageWithRemoveKeyboard($subscriber->chat_id, $replyOrtu);

            // Update Pesan di Grup Admin
            $adminReply = "❌ *Pengajuan Ditolak*\n" .
                          "Ortu: {$subscriber->parent_name} (+{$subscriber->phone_number})\n" .
                          "Siswa: " . ($student ? $student->name : '-');

            $telegramService->editMessageText($adminChat, $messageId, $adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ditolak.");
        }
    }
}