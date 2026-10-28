<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Services\TelegramService;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, TelegramService $telegramService)
    {
        $update = $request->all();

        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $chatId = $update['message']['chat']['id'];
        $text   = trim($update['message']['text'] ?? '');

        // Fitur 1: Mengirim informasi Chat ID
        if ($text === '/start' || $text === '/id') {
            $reply = "Selamat datang di Bot Absensi Sekolah.\n\nID Telegram Anda: `{$chatId}`\n\nGunakan perintah berikut untuk mendaftarkan NIS siswa:\n`/daftar NIS_SISWA`\nContoh: `/daftar 36846`";
            $telegramService->sendMessage($chatId, $reply);
        } 
        // Fitur 2: Pendaftaran NIS otomatis via chat
        elseif (str_starts_with($text, '/daftar')) {
            $parts = explode(' ', $text);
            $nis = $parts[1] ?? null;

            if (!$nis) {
                $telegramService->sendMessage($chatId, "⚠️ Format salah! Gunakan: `/daftar NIS_SISWA`");
                return response()->json(['status' => 'ok']);
            }

            $student = Student::where('nis', $nis)->first();
            if ($student) {
                $student->update(['telegram_chat_id' => $chatId]);
                $telegramService->sendMessage($chatId, "✅ Berhasil terhubung! Notifikasi absensi untuk siswa *{$student->name}* akan dikirimkan ke Telegram ini.");
            } else {
                $telegramService->sendMessage($chatId, "❌ NIS {$nis} tidak ditemukan dalam sistem.");
            }
        }

        return response()->json(['status' => 'ok']);
    }
}