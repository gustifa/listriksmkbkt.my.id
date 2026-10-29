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

        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $chatId  = (string) $update['message']['chat']['id'];
        $message = $update['message'];

        // 1. HANDLE SHARE CONTACT
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

                // Pencocokan ke kolom phone (HP Ortu) atau student_phone (HP Siswa)
                $student = Student::where(function ($q) use ($lastDigits) {
                    $q->where('phone', 'like', '%' . $lastDigits)
                      ->orWhere('student_phone', 'like', '%' . $lastDigits);
                })->first();

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
                            'parent_name'     => $parentName,
                            'phone_number'    => $cleanPhone,
                            'username'        => $username,
                            'is_active'       => true,
                            'approval_status' => 'approved',
                            'subscribed_at'   => now(), // Aman untuk kolom NOT NULL
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
                    $reply = "⚠️ *Nomor HP Anda (`+{$cleanPhone}`) belum terdaftar di database sekolah.*\n\n" .
                            "Silakan hubungi Admin SISFO / pihak sekolah untuk memperbarui data nomor telepon Anda.";

                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                }
            } catch (\Exception $e) {
                Log::error("Error Telegram Contact Webhook: " . $e->getMessage() . " File: " . $e->getFile() . " Line: " . $e->getLine());
                $telegramService->sendMessageWithRemoveKeyboard($chatId, "⚠️ Terjadi kendala sistem. Silakan coba beberapa saat lagi atau hubungi Admin SISFO.");
            }

            return response()->json(['status' => 'ok']);
        }

        // 2. HANDLE COMMAND BOT
        $text = trim($message['text'] ?? '');
        $lowerText = strtolower($text);

        if ($lowerText === '/start' || $lowerText === '/daftar') {
            $prompt = "🤖 *SELAMAT DATANG DI BOT SISFO SMKN 1 BUKITTINGGI*\n\n" .
                      "Bot ini memberikan layanan notifikasi presensi siswa secara *real-time* langsung ke akun Telegram Orang Tua/Wali.\n\n" .
                      "--------------------------------------\n" .
                      "Silakan tekan tombol *'📱 Bagikan Nomor Kontak Saya'* di bawah ini untuk menghubungkan akun Telegram Anda dengan data siswa.";
            
            $telegramService->sendContactButton($chatId, $prompt);
            return response()->json(['status' => 'ok']);
        }

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
                         "Akun Telegram Anda telah dinonaktifkan dari sistem notifikasi sekolah.";
            } else {
                $reply = "ℹ️ Akun Telegram Anda tidak terdaftar atau sudah nonaktif di sistem kami.";
            }

            $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'ok']);
    }
}