<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Services\TelegramService;
use App\Models\TelegramSubscriber; // Jangan lupa import model ini

class TelegramWebhookController extends Controller
{
    // public function handle(Request $request, TelegramService $telegramService)
    // {
    //     if ($request->isMethod('get')) {
    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Endpoint Webhook Telegram SISFO Aktif!'
    //         ]);
    //     }

    //     $update = $request->all();

    //     if (!isset($update['message'])) {
    //         return response()->json(['status' => 'ok']);
    //     }

    //     $chatId = $update['message']['chat']['id'];
    //     $message = $update['message'];

    //     // 1. Jika Pengguna Mengirimkan Kontak (Menekan Tombol Share Contact)
    //     if (isset($message['contact'])) {
    //         $phone = $message['contact']['phone_number'];
            
    //         // Bersihkan format nomor HP (ambil angka saja)
    //         $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            
    //         // Cocokkan 9 digit terakhir dengan database SISFO
    //         $student = Student::where('phone', 'like', '%' . substr($cleanPhone, -9))->first();

    //         if ($student) {
    //             $student->update(['telegram_chat_id' => $chatId]);
                
    //             $reply = "✅ *Pendaftaran Berhasil!*\n\n" .
    //                      "Akun Telegram Anda telah terhubung dengan siswa:\n" .
    //                      "👤 Nama: *{$student->name}*\n" .
    //                      "🏫 Kelas: " . ($student->classroom ? $student->classroom->name : '-') . "\n\n" .
    //                      "Notifikasi presensi harian akan dikirimkan ke akun ini.";
    //         } else {
    //             $reply = "❌ *Pendaftaran Gagal!*\n\n" .
    //                      "Nomor HP Telegram Anda (`+{$cleanPhone}`) tidak ditemukan di database sekolah.\n" .
    //                      "Silakan hubungi pihak tata usaha sekolah untuk memperbarui nomor HP Anda.";
    //         }

    //         // Kirim balasan dan hapus custom keyboard
    //         $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
    //         return response()->json(['status' => 'ok']);
    //     }

    //     // 2. Jika Pengguna Ketik /start atau /daftar
    //     $text = trim($message['text'] ?? '');

    //     if ($text === '/start' || $text === '/daftar') {
    //         $prompt = "Selamat datang di Bot Absensi SISFO.\n\n" .
    //                   "Untuk menerima notifikasi presensi putra/putri Anda, silakan tekan tombol *'Bagikan Kontak'* di bawah ini:";
            
    //         // Kirim pesan dengan Tombol Share Contact
    //         $telegramService->sendContactButton($chatId, $prompt);
    //     }

    //     return response()->json(['status' => 'ok']);
    // }
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
                // A. Update telegram_chat_id pada tabel students (opsional/jika masih dipakai)
                $student->update(['telegram_chat_id' => $chatId]);

                // B. SIMPAN / UPDATE KE TABEL telegram_subscribers (Kunci agar muncul di monitoring)
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

        // 2. Jika Pengguna Ketik /start atau /daftar
        $text = trim($message['text'] ?? '');

        if ($text === '/start' || $text === '/daftar') {
            $prompt = "Selamat datang di Bot Absensi SISFO.\n\n" .
                    "Untuk menerima notifikasi presensi putra/putri Anda, silakan tekan tombol *'Bagikan Kontak'* di bawah ini:";
            
            // Kirim pesan dengan Tombol Share Contact
            $telegramService->sendContactButton($chatId, $prompt);
        }

        return response()->json(['status' => 'ok']);
    }
}