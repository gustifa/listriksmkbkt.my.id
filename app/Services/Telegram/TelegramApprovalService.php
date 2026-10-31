<?php

namespace App\Services\Telegram;

use App\Models\Student;
use App\Models\IzinSiswa;
use App\Models\TelegramSubscriber;
use App\Models\AttendanceSetting;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;

class TelegramApprovalService
{
    /**
     * Memproses callback tombol inline dari pesan grup atau user.
     */
    public function handleCallback(array $callback, TelegramService $telegramService, TelegramStudentService $studentService): void
    {
        $data = $callback['data'];

        if (str_starts_with($data, 'rekap_')) {
            $this->handleRekapCallback($callback, $telegramService, $studentService);
        } elseif (str_starts_with($data, 'cancel_user_izin_')) {
            $this->handleUserCancelIzinCallback($callback, $telegramService);
        } elseif (str_starts_with($data, 'appizin_') || str_starts_with($data, 'rejizin_')) {
            $this->handleIzinApprovalCallback($callback, $telegramService);
        } else {
            $this->handleAdminCallback($callback, $telegramService);
        }
    }

    private function handleRekapCallback(array $callbackQuery, TelegramService $telegramService, TelegramStudentService $studentService): void
    {
        $chatId = (string) $callbackQuery['message']['chat']['id'];
        $data   = $callbackQuery['data'];

        if ($data === 'rekap_gerbang') {
            $studentService->sendRekapGerbang($chatId, $telegramService);
        } elseif ($data === 'rekap_pembelajaran') {
            $studentService->sendRekapPembelajaran($chatId, $telegramService);
        } elseif ($data === 'rekap_sholat') {
            $studentService->sendRekapSholat($chatId, $telegramService);
        } elseif ($data === 'rekap_semua') {
            $studentService->sendRekapSemua($chatId, $telegramService);
        }
    }

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
        $izin->delete();

        $userReply = "🗑️ *PENGAJUAN DIHAPUS / DIBATALKAN*\n\n" .
                      "Pengajuan izin/sakit untuk *{$studentName}* pada tanggal *{$izin->date}* telah berhasil Anda batalkan dan dihapus dari sistem.";

        $telegramService->editMessageText($chatId, $messageId, $userReply);
        $telegramService->answerCallbackQuery($callbackId, "Pengajuan izin berhasil dibatalkan dan dihapus.");
    }

    private function handleIzinApprovalCallback(array $callbackQuery, TelegramService $telegramService): void
    {
        $callbackId = $callbackQuery['id'];
        $data       = $callbackQuery['data'];
        $message    = $callbackQuery['message'];
        $adminChat  = (string) $message['chat']['id'];
        $messageId  = $message['message_id'];
        $fromUser   = $callbackQuery['from'];
        
        $firstName  = trim($fromUser['first_name'] ?? '');
        $lastName   = trim($fromUser['last_name'] ?? '');
        $approver   = trim("{$firstName} {$lastName}") ?: ($fromUser['username'] ?? 'Petugas Sekolah');

        if (!str_contains($data, '_')) return;

        [$action, $izinId] = explode('_', $data);
        $izin = IzinSiswa::find($izinId);

        $updateGroupMessage = function(string $text) use ($message, $adminChat, $messageId, $telegramService) {
            $hasPhoto = isset($message['photo']);
            $token    = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));

            if ($hasPhoto) {
                Http::post("https://api.telegram.org/bot{$token}/editMessageCaption", [
                    'chat_id'    => $adminChat,
                    'message_id' => $messageId,
                    'caption'    => $text,
                    'parse_mode' => 'Markdown',
                ]);
            } else {
                $telegramService->editMessageText($adminChat, $messageId, $text);
            }
        };

        if (!$izin) {
            $adminReply = "⚠️ *PENGAJUAN TIDAK TERSEDIA*\n\n" .
                          "Pengajuan izin/sakit ini sudah dibatalkan/dihapus oleh pemohon atau telah diproses sebelumnya.";
            
            $updateGroupMessage($adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan izin ini sudah dibatalkan atau dihapus oleh pemohon.", true);
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
        $typeText    = strtoupper($izin->type ?? 'IZIN');
        $izinDate    = $izin->date;
        $userChat    = $izin->telegram_chat_id;

        if ($action === 'appizin') {
            $izin->update([
                'status'      => 'approved',
                'approved_by' => $approver,
                'approved_at' => now(),
            ]);

            $adminReply = "✅ *PENGAJUAN {$typeText} DISETUJUI*\n\n" .
                          "👤 Siswa : *{$studentName}* ({$className})\n" .
                          "📅 Tanggal : *{$izinDate}*\n" .
                          "📝 Alasan : _{$izin->reason}_\n" .
                          "👨‍🏫 Diverifikasi Oleh : *{$approver}*";

            $updateGroupMessage($adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Izin siswa berhasil disetujui oleh {$approver}!");

            if (!empty($userChat)) {
                $userMsg = "🎉 *PENGAJUAN {$typeText} DISETUJUI!*\n\n" .
                           "Pengajuan izin/sakit untuk siswa *{$studentName}* pada tanggal *{$izinDate}* telah *DISETUJU & DIVERIFIKASI* oleh *{$approver}*.\n\n" .
                           "Terima kasih telah memberikan konfirmasi ketidakhadiran.";
                $telegramService->sendMessage($userChat, $userMsg, 'info', 'Persetujuan Izin');
            }

        } elseif ($action === 'rejizin') {
            $adminReply = "🗑️ *PENGAJUAN {$typeText} DIBATALKAN / DITOLAK*\n\n" .
                          "👤 Siswa : *{$studentName}* ({$className})\n" .
                          "📅 Tanggal : *{$izinDate}*\n" .
                          "📝 Alasan : _{$izin->reason}_\n" .
                          "👨‍🏫 Dibatalkan Oleh : *{$approver}*";

            $updateGroupMessage($adminReply);
            $telegramService->answerCallbackQuery($callbackId, "Pengajuan izin berhasil dibatalkan oleh {$approver}.");

            if (!empty($userChat)) {
                $userMsg = "❌ *PENGAJUAN {$typeText} DIBATALKAN / DITOLAK*\n\n" .
                           "Pengajuan izin/sakit untuk siswa *{$studentName}* pada tanggal *{$izinDate}* telah dibatalkan/ditolak oleh *{$approver}*.\n\n" .
                           "Catatan pengajuan telah dihapus dari sistem. Anda dapat melakukan pengajuan ulang jika terdapat kekeliruan.";
                $telegramService->sendMessage($userChat, $userMsg, 'info', 'Izin Dibatalkan');
            }

            $izin->delete();
        }
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
                         "Gunakan perintah `/rekap` atau `/semua` untuk melihat rekap absensi.\n\n" .
                         "Silakan bergabung ke Grup Telegram Admin SISFO di bawah ini:";

            $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
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