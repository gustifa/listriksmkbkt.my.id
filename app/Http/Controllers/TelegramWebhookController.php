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

        // -------------------------------------------------------------------
        // 1. HANDLE CALLBACK QUERY (RESPON TOMBOL SETUJUI / TOLAK DARI ADMIN)
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
        // 2. HANDLE BAGIKAN KONTAK (TEKAN TOMBOL SHARE CONTACT)
        // -------------------------------------------------------------------
        if (isset($message['contact'])) {
            try {
                $contact    = $message['contact'];
                $phone      = $contact['phone_number'];
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

                $firstName  = $contact['first_name'] ?? '';
                $lastName   = $contact['last_name'] ?? '';
                $parentName = trim($firstName . ' ' . $lastName) ?: 'Orang Tua';
                $username   = $message['from']['username'] ?? null;

                // Pencocokan nomor HP ke database (memeriksa 9 digit terakhir)
                $student = Student::where('phone', 'like', '%' . substr($cleanPhone, -9))
                            ->orWhere('parent_phone', 'like', '%' . substr($cleanPhone, -9))
                            ->first();

                if ($student) {
                    // KONDISI A: NOMOR HP TERDAFTAR -> LANGSUNG APPROVED
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
                    // KONDISI B: NOMOR HP TIDAK TERDAFTAR -> MINTA INPUT NISN / NIS
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
            } catch (\Exception $e) {
                Log::error("Error Telegram Contact Webhook: " . $e->getMessage());
                $telegramService->sendMessageWithRemoveKeyboard($chatId, "⚠️ Terjadi kendala sistem. Silakan coba lagi atau hubungi Admin SISFO.");
            }

            return response()->json(['status' => 'ok']);
        }

        // -------------------------------------------------------------------
        // 3. HANDLE PESAN TEKS DAN PERINTAH BOT
        // -------------------------------------------------------------------
        $text = trim($message['text'] ?? '');
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
                     "2️⃣ *Nomor HP beda dengan database?*\n" .
                     "Ketik *NISN / NIS* putra/putri Anda saat diminta untuk mengajukan verifikasi ke Admin SISFO.\n\n" .
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

        // -------------------------------------------------------------------
        // 4. HANDLE PROSES INPUT NISN / NIS DARI ORANG TUA
        // -------------------------------------------------------------------
        $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

        if ($subscriber && $subscriber->approval_status === 'pending_nisn' && !empty($text)) {
            $student = Student::where('nisn', $text)->orWhere('nis', $text)->first();

            if (!$student) {
                $reply = "❌ *Siswa tidak ditemukan.*\n\n" .
                         "NISN/NIS *{$text}* tidak cocok dengan database sekolah. Silakan periksa kembali dan ketik ulang nomor NISN/NIS yang benar:";
                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                return response()->json(['status' => 'ok']);
            }

            // Simpan student_id dan ubah status menjadi pending_approval
            $subscriber->update([
                'student_id'      => $student->id,
                'approval_status' => 'pending_approval'
            ]);

            // Kirim Balasan ke Telegram Orang Tua
            $reply = "⏳ *Pendaftaran Dalam Proses Verifikasi!*\n\n" .
                     "Data Siswa Ditemukan:\n" .
                     "👤 Nama: *{$student->name}*\n" .
                     "🏫 Kelas: " . ($student->classroom ? $student->classroom->name : '-') . "\n\n" .
                     "Pengajuan Anda telah diteruskan ke *Admin SISFO*. Mohon tunggu konfirmasi persetujuan dari Admin untuk mengaktifkan notifikasi.";
            
            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);

            // Kirim Notifikasi ke Grup Telegram Admin
            $this->notifyAdminGroup($subscriber, $student, $telegramService);

            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Mengirimkan Notifikasi Ringkasan ke Grup Telegram Admin
     */
    private function notifyAdminGroup(TelegramSubscriber $subscriber, Student $student, TelegramService $telegramService): void
    {
        $adminGroupChatId = config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID'));

        if (empty($adminGroupChatId)) {
            Log::warning("TELEGRAM_ADMIN_CHAT_ID belum dikonfigurasi di file .env");
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
     * Menangani Klik Tombol Setujui / Tolak dari Admin di Telegram
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

            // Update data siswa
            if ($student) {
                $student->update([
                    'telegram_chat_id' => $subscriber->chat_id,
                    'phone'            => $subscriber->phone_number
                ]);
            }

            // Pesan Konfirmasi ke Telegram Ortu
            $replyOrtu = "🎉 *Pendaftaran Disetujui Admin!*\n\n" .
                         "Akun Telegram Anda telah diverifikasi dan dihubungkan dengan siswa:\n" .
                         "👤 Nama: *{$student->name}*\n" .
                         "🏫 Kelas: " . ($student->classroom ? $student->classroom->name : '-') . "\n\n" .
                         "Notifikasi presensi harian sudah aktif.";
            
            $telegramService->sendMessageWithRemoveKeyboard($subscriber->chat_id, $replyOrtu);

            // Update Pesan Teks di Grup Admin
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

            // Pesan Penolakan ke Telegram Ortu
            $replyOrtu = "❌ *Pendaftaran Ditolak Admin*\n\n" .
                         "Mohon maaf, pengajuan pendaftaran akun Telegram Anda tidak dapat diverifikasi.\n" .
                         "Silakan hubungi pihak sekolah / Admin SISFO untuk pembaruan data.";
            
            $telegramService->sendMessageWithRemoveKeyboard($subscriber->chat_id, $replyOrtu);

            // Update Pesan Teks di Grup Admin
            $adminReply = "❌ *Pengajuan Ditolak*\n" .
                          "Ortu: {$subscriber->parent_name} (+{$subscriber->phone_number})\n" .
                          "Siswa: " . ($student ? $student->name : '-');

            $telegramService->editMessageText($adminChat, $messageId, $adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan ditolak.");
        }
    }
}