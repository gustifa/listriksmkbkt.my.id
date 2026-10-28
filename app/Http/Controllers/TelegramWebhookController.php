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
                // Update telegram_chat_id pada tabel students
                $student->update(['telegram_chat_id' => $chatId]);

                // Simpan / update ke tabel telegram_subscribers
                TelegramSubscriber::updateOrCreate(
                    ['chat_id' => (string) $chatId],
                    [
                        'student_id'    => $student->id,
                        'parent_name'   => $parentName,
                        'phone_number'  => $cleanPhone,
                        'username'      => $username,
                        'is_active'     => true,
                        'subscribed_at' => now(),
                    ]
                );

                $reply = "✅ *Pendaftaran Berhasil!*\n\n" .
                        "Akun Telegram Anda telah terhubung dengan siswa:\n" .
                        "👤 Nama: *{$student->name}*\n" .
                        "🏫 Kelas: " . ($student->classroom ? $student->classroom->name : '-') . "\n\n" .
                        "Notifikasi presensi harian akan dikirimkan ke akun ini.";
            } else {
                $reply = "❌ *Pendaftaran Gagal!*\n\n" .
                        "Nomor HP Telegram Anda (`+{$cleanPhone}`) tidak ditemukan di database sekolah.\n" .
                        "Silakan hubungi pihak tata usaha sekolah untuk memperbarui nomor HP Anda.";
            }

            // Kirim balasan dan hapus custom keyboard
            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            return response()->json(['status' => 'ok']);
        }

        // 2. Tentukan perintah teks dari pengguna
        $text = trim($message['text'] ?? '');

        // Pendaftaran (/start atau /daftar)
        if ($text === '/start' || $text === '/daftar') {
            $prompt = "Selamat datang di Bot Absensi SISFO.\n\n" .
                    "Untuk menerima notifikasi presensi putra/putri Anda, silakan tekan tombol *'Bagikan Kontak'* di bawah ini:";
            
            $telegramService->sendContactButton($chatId, $prompt);
            return response()->json(['status' => 'ok']);
        }

        // Pembatalan Pendaftaran (/batal atau /stop)
        if ($text === '/batal' || $text === '/stop') {
            $subscriber = TelegramSubscriber::where('chat_id', (string) $chatId)->first();

            if ($subscriber && $subscriber->is_active) {
                // Ubah status subscriber menjadi nonaktif
                $subscriber->update(['is_active' => false]);

                // Kosongkan telegram_chat_id di tabel student terkait (jika ada)
                if ($subscriber->student_id) {
                    Student::where('id', $subscriber->student_id)
                        ->update(['telegram_chat_id' => null]);
                }

                $reply = "❌ *Pendaftaran Dibatalkan!*\n\n" .
                         "Akun Telegram Anda telah dinonaktifkan dari sistem notifikasi sekolah.\n\n" .
                         "Ketik /start atau /daftar jika ingin mendaftar kembali.";
            } else {
                $reply = "ℹ️ Akun Telegram Anda tidak terdaftar atau sudah nonaktif di sistem kami.";
            }

            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ok']);
    }
}