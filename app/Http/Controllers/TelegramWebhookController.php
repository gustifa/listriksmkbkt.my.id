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

        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $chatId  = $update['message']['chat']['id'];
        $message = $update['message'];

        // 1. Jika Pengguna Mengirimkan Kontak (Menekan Tombol Share Contact)
        if (isset($message['contact'])) {
            $contact    = $message['contact'];
            $phone      = $contact['phone_number'];
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

            // Ambil nama dari kontak Telegram
            $firstName  = $contact['first_name'] ?? '';
            $lastName   = $contact['last_name'] ?? '';
            $parentName = trim($firstName . ' ' . $lastName) ?: 'Orang Tua';
            $username   = $message['from']['username'] ?? null;

            // Cocokkan 9 digit terakhir dengan database SISFO
            $student = Student::where('phone', 'like', '%' . substr($cleanPhone, -9))->first();

            if ($student) {
                // SKENARIO A: NOMOR TERDAFTAR DI DATABASE (LANGSUNG AKTIF)
                $student->update(['telegram_chat_id' => $chatId]);

                TelegramSubscriber::updateOrCreate(
                    ['chat_id' => (string) $chatId],
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
            } else {
                // SKENARIO B: NOMOR TIDAK TERDAFTAR (MASUK STATUS PENDING APPROVAL)
                TelegramSubscriber::updateOrCreate(
                    ['chat_id' => (string) $chatId],
                    [
                        'student_id'      => null,
                        'parent_name'     => $parentName,
                        'phone_number'    => $cleanPhone,
                        'username'        => $username,
                        'is_active'       => false,
                        'approval_status' => 'pending',
                        'subscribed_at'   => now(),
                    ]
                );

                $reply = "⏳ *Pendaftaran Dalam Proses Verifikasi!*\n\n" .
                        "Nomor HP Telegram Anda (`+{$cleanPhone}`) belum terdaftar di database sekolah.\n\n" .
                        "Pengajuan pendaftaran Anda telah diteruskan ke *Admin SISFO*. Mohon tunggu konfirmasi persetujuan dari Admin untuk mengaktifkan notifikasi.";
            }

            // Kirim balasan dan hapus custom keyboard
            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            return response()->json(['status' => 'ok']);
        }

        // 2. Tentukan perintah teks dari pengguna
        $text = strtolower(trim($message['text'] ?? ''));

        // Pendaftaran (/start atau /daftar)
        if ($text === '/start' || $text === '/daftar') {
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

        // Bantuan (/help)
        if ($text === '/help') {
            $reply = "ℹ️ *BANTUAN BOT NOTIFIKASI SISFO*\n\n" .
                     "1️⃣ *Bagaimana cara mendaftar?*\n" .
                     "Ketik `/start` lalu tekan tombol *'📱 Bagikan Nomor Kontak Saya'*.\n\n" .
                     "2️⃣ *Nomor HP beda dengan database?*\n" .
                     "Jika nomor HP Anda berbeda, pengajuan akan masuk ke status *Pending* untuk diverifikasi oleh Admin SISFO.\n\n" .
                     "3️⃣ *Bagaimana berhenti berlangganan?*\n" .
                     "Ketik `/batal` atau `/stop` kapan saja.\n\n" .
                     "Jika mengalami kendala, silakan hubungi *Admin SISFO SMKN 1 Bukittinggi*.";

            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            return response()->json(['status' => 'ok']);
        }

        // Pembatalan Pendaftaran (/batal atau /stop)
        if ($text === '/batal' || $text === '/stop') {
            $subscriber = TelegramSubscriber::where('chat_id', (string) $chatId)->first();

            if ($subscriber && $subscriber->is_active) {
                // Ubah status subscriber menjadi nonaktif & rejected/cancelled
                $subscriber->update([
                    'is_active'       => false,
                    'approval_status' => 'rejected'
                ]);

                // Kosongkan telegram_chat_id di tabel student terkait (jika ada)
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

        return response()->json(['status' => 'ok']);
    }
}