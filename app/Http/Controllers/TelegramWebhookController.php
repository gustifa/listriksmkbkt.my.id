<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramSubscriber;
use App\Models\AttendanceSetting;
use App\Services\TelegramService;
use App\Services\Telegram\TelegramTeacherService;
use App\Services\Telegram\TelegramRegistrationService;
use App\Services\Telegram\TelegramStudentService;
use App\Services\Telegram\TelegramApprovalService;

class TelegramWebhookController extends Controller
{
    public function handle(
        Request $request, 
        TelegramService $telegramService,
        TelegramTeacherService $teacherService,
        TelegramRegistrationService $registrationService
    ) {
        if ($request->isMethod('get')) {
            return response()->json(['status' => true, 'message' => 'Endpoint Webhook Telegram SISFO Aktif!']);
        }

        try {
            $update = $request->all();

            // 1. HANDLE CALLBACK QUERY
            if (isset($update['callback_query'])) {
                // Dipanggil ke method callback (dapat dipisah ke Service tersendiri juga jika perlu)
                $this->handleCallbackQuery($update['callback_query'], $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (!isset($update['message'])) {
                return response()->json(['status' => 'ok']);
            }

            $chatId    = (string) $update['message']['chat']['id'];
            $message   = $update['message'];
            $setting   = AttendanceSetting::first();
            $groupLink = $setting?->telegram_admin_group_link 
                ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

            // 2. HANDLE BAGIKAN KONTAK
            if (isset($message['contact'])) {
                $registrationService->handleContact($message, $chatId, $groupLink, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            // 3. HANDLE COMMAND GURU
            $text      = trim($message['text'] ?? '');
            $lowerText = strtolower($text);

            if (in_array($lowerText, ['/jadwal', 'jadwal'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendJadwalGuru($chatId, $subscriber->teacher_id, $telegramService);
                }
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/walas', 'walas'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendRekapWalas($chatId, $subscriber->teacher_id, $telegramService);
                }
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/kabeng', 'kabeng'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendRekapKabeng($chatId, $subscriber->teacher_id, $telegramService);
                }
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/proka', 'proka'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendRekapProka($chatId, $subscriber->teacher_id, $telegramService);
                }
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/bk', 'bk'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendRekapBK($chatId, $subscriber->teacher_id, $telegramService);
                }
                return response()->json(['status' => 'ok']);
            }

            // (Lanjutkan perutean ke method/service lainnya...)

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Telegram Webhook Exception: " . $e->getMessage());
        }

        return response()->json(['status' => 'ok']);
    }
}