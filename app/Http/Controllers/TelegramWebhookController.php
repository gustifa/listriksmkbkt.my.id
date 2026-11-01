<?php

namespace App\Http\Controllers;

use App\Http\Controllers\TelegramAiController;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TelegramSubscriber;
use App\Models\AttendanceSetting;
use App\Services\TelegramService;
use App\Services\Telegram\TelegramTeacherService;
use App\Services\Telegram\TelegramRegistrationService;
use App\Services\Telegram\TelegramStudentService;
use App\Services\Telegram\TelegramApprovalService;
use App\Services\Telegram\GeminiAiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramWebhookController extends Controller
{
    public function handle(
        Request $request, 
        TelegramService $telegramService,
        TelegramTeacherService $teacherService,
        TelegramRegistrationService $registrationService,
        TelegramStudentService $studentService,
        TelegramApprovalService $approvalService,
        TelegramAiController $aiController,
        GeminiAiService $aiService
    ) {
        if ($request->isMethod('get')) {
            return response()->json(['status' => true, 'message' => 'Endpoint Webhook Telegram SISFO Aktif!']);
        }

        try {
            $update = $request->all();

            // 1. HANDLE CALLBACK QUERY (TOMBOL INLINE REKAP & APPROVAL)
            if (isset($update['callback_query'])) {
                $approvalService->handleCallback($update['callback_query'], $telegramService, $studentService);
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

            // =========================================================================
            // HANDLER FOTO MASUK (Proses Rekap Absensi via Gemini Vision OCR dari Guru)
            // =========================================================================
            if (isset($message['photo'])) {
                $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
                    $q->where('chat_id', (string)$chatId)->orWhere('chat_id', (int)$chatId);
                })->first();

                // Fitur ini khusus untuk akun Guru terdaftar
                if (!$subscriber || !$subscriber->teacher_id) {
                    $telegramService->sendMessage($chatId, "⚠️ Fitur upload rekap kehadiran via foto hanya tersedia untuk akun Guru.");
                    return response()->json(['status' => 'ok']);
                }

                // Ambil foto kualitas tertinggi dari Telegram
                $photos = $message['photo'];
                $highestResPhoto = end($photos);
                $fileId = $highestResPhoto['file_id'];

                // Download foto dari Telegram Server
                $botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
                $fileUrlResponse = Http::get("https://api.telegram.org/bot{$botToken}/getFile?file_id={$fileId}");
                
                if ($fileUrlResponse->successful()) {
                    $filePathOnTelegram = $fileUrlResponse->json('result.file_path');
                    $imageContents = Http::get("https://api.telegram.org/file/bot{$botToken}/{$filePathOnTelegram}")->body();

                    // Simpan sementara di storage
                    $tempPath = "temp_rekap/" . uniqid() . ".jpg";
                    Storage::disk('public')->put($tempPath, $imageContents);

                    $telegramService->sendMessage($chatId, "🔍 *Sedang Menganalisis Foto Rekap...*\nMohon tunggu beberapa detik.");

                    // Panggil Gemini Vision Parsing
                    $parsedData = $aiService->parseAttendanceImage($tempPath);

                    if (!$parsedData || empty($parsedData['attendances'])) {
                        $telegramService->sendMessage($chatId, "❌ Gagal membaca data dari foto. Pastikan tulisan/tabel pada foto terlihat jelas dan terang.");
                        return response()->json(['status' => 'ok']);
                    }

                    // Masukkan Hasil Parsing ke Database PostgreSQL
                    $targetDate = !empty($parsedData['date']) ? $parsedData['date'] : date('Y-m-d');
                    $successCount = 0;
                    $teacherName = optional($subscriber->teacher)->name ?? 'Guru';

                    foreach ($parsedData['attendances'] as $row) {
                        $studentName = trim($row['student_name'] ?? '');
                        $studentNis  = trim($row['nis'] ?? '');
                        $status      = strtolower(trim($row['status'] ?? 'hadir'));

                        // Normalisasi fleksibel berbagai format tulisan status presensi
                        if (in_array($status, ['h', 'hadir', 'v', '✓', 'check'])) {
                            $status = 'hadir';
                        } elseif (in_array($status, ['i', 'iz', 'izin', 'ijin'])) {
                            $status = 'izin';
                        } elseif (in_array($status, ['s', 'skt', 'sakit'])) {
                            $status = 'sakit';
                        } elseif (in_array($status, ['a', 'alp', 'alpha', 'alpa', 'tk'])) {
                            $status = 'alpha';
                        } else {
                            $status = 'hadir';
                        }

                        // 1. Cari siswa berdasarkan NIS/NISN dahulu
                        $student = null;
                        if (!empty($studentNis)) {
                            $student = DB::table('students')
                                ->where('nis', $studentNis)
                                ->orWhere('nisn', $studentNis)
                                ->first();
                        }

                        // 2. Jika tidak ketemu NIS, cari berdasarkan NAMA (ILIKE PostgreSQL)
                        if (!$student && !empty($studentName)) {
                            $student = DB::table('students')
                                ->where('name', 'ILIKE', "%{$studentName}%")
                                ->first();
                        }

                        if ($student) {
                            DB::table('daily_attendances')->updateOrInsert(
                                [
                                    'student_id' => $student->id,
                                    'date'       => $targetDate,
                                ],
                                [
                                    'status'      => $status,
                                    'recorded_by' => 'Telegram Bot OCR (' . $teacherName . ')',
                                    'updated_at'  => now(),
                                    'created_at'  => now(),
                                    'is_synced'   => true,
                                ]
                            );
                            $successCount++;
                        } else {
                            Log::warning("OCR Student Not Found in DB: Nama={$studentName}, NIS={$studentNis}");
                        }
                    }

                    $telegramService->sendMessage(
                        $chatId, 
                        "✅ *Berhasil Menginput Rekap Presensi!*\n\n" .
                        "📅 Tanggal: *{$targetDate}*\n" .
                        "📊 Total Siswa Terinput: *{$successCount} Siswa*"
                    );
                }

                return response()->json(['status' => 'ok']);
            }

            $text      = trim($message['text'] ?? '');
            $lowerText = strtolower($text);

            // 3. HANDLE COMMAND BATAL / STOP / UNSUBSCRIBE
            if (in_array($lowerText, ['/batal', 'batal', '/batal_izin', 'stop', '/stop'])) {
                Cache::forget("telegram_permit_step_{$chatId}");
                $telegramService->sendMessage($chatId, "❌ *Proses pengajuan/interaksi telah dihentikan.*");
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/unsubscribe', 'unsubscribe', '/keluar', 'keluar', '/berhenti'])) {
                Cache::forget("telegram_permit_step_{$chatId}");
                $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
                    $q->where('chat_id', (string)$chatId)->orWhere('chat_id', (int)$chatId);
                })->first();

                if ($subscriber) {
                    if ($subscriber->student_id) {
                        try { Student::where('id', $subscriber->student_id)->update(['telegram_chat_id' => null]); } catch (\Exception $ex) {}
                    }
                    $subscriber->delete();
                    $telegramService->sendMessageWithRemoveKeyboard($chatId, "👋 *Berhasil Berhenti Berlangganan!*\n\nAkun Telegram Anda telah dilepas dari sistem SISFO. Ketik `/start` jika ingin mendaftar kembali.");
                } else {
                    $telegramService->sendMessage($chatId, "⚠️ *Akun Telegram Anda belum terdaftar dalam sistem langganan SISFO.*");
                }
                return response()->json(['status' => 'ok']);
            }

            // =========================================================================
            // 4. HANDLE COMMAND PERINTAH GURU
            // =========================================================================
            $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
                $q->where('chat_id', (string)$chatId)->orWhere('chat_id', (int)$chatId);
            })->first();

            if (in_array($lowerText, ['/jadwal', 'jadwal'])) {
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendJadwalGuru($chatId, $subscriber->teacher_id, $telegramService);
                } else {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/jadwal` khusus untuk akun Guru yang telah terdaftar.");
                }
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/jadwal_minggu', '/jadwalminggu', 'jadwal minggu', '/jadwal_pekanan'])) {
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendJadwalMingguan($chatId, $subscriber->teacher_id, $telegramService);
                } else {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/jadwal_minggu` khusus untuk akun Guru yang telah terdaftar.");
                }
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/walas', 'walas'])) {
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendRekapWalas($chatId, $subscriber->teacher_id, $telegramService);
                } else {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/walas` khusus untuk akun Wali Kelas.");
                }
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/kabeng', 'kabeng'])) {
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendRekapKabeng($chatId, $subscriber->teacher_id, $telegramService);
                } else {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/kabeng` khusus untuk Kepala Bengkel.");
                }
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/proka', 'proka'])) {
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendRekapProka($chatId, $subscriber->teacher_id, $telegramService);
                } else {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/proka` khusus untuk Ketua Program Keahlian.");
                }
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/bk', 'bk'])) {
                if ($subscriber && $subscriber->teacher_id) {
                    $teacherService->sendRekapBK($chatId, $subscriber->teacher_id, $telegramService);
                } else {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/bk` khusus untuk Guru BK.");
                }
                return response()->json(['status' => 'ok']);
            }

            // 5. HANDLE COMMAND PERINTAH SISWA / ORANG TUA
            if (in_array($lowerText, ['/izin', 'izin', '/sakit', 'sakit'])) {
                $studentService->handleIzinCommand($chatId, $lowerText, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            $permitSession = Cache::get("telegram_permit_step_{$chatId}");
            if ($permitSession) {
                $studentService->handlePermitConversation($chatId, $message, $permitSession, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/rekap', '/menu', 'rekap', 'menu'])) {
                $studentService->sendRekapMenu($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/gerbang', 'gerbang'])) {
                $studentService->sendRekapGerbang($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/pembelajaran', '/kelas', 'pembelajaran'])) {
                $studentService->sendRekapPembelajaran($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/sholat', 'sholat'])) {
                $studentService->sendRekapSholat($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/semua', 'semua', '/all', 'all'])) {
                $studentService->sendRekapSemua($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            // 6. HANDLE COMMAND /start & /daftar (DENGAN DUKUNGAN DEEP LINKING TAUTAN WEB)
            if (str_starts_with($lowerText, '/start') || $lowerText === '/daftar') {
                $parts = explode(' ', $text);
                
                // Jika diakses via tombol "Tautkan ke Telegram" dari Dashboard Web
                if (isset($parts[1]) && str_starts_with($parts[1], 'link_')) {
                    $teacherId = str_replace('link_', '', $parts[1]);
                    $teacher = Teacher::find($teacherId);

                    if ($teacher) {
                        TelegramSubscriber::updateOrCreate(
                            ['chat_id' => $chatId],
                            [
                                'teacher_id'      => $teacher->id,
                                'student_id'      => null,
                                'parent_name'     => $teacher->name,
                                'phone_number'    => $teacher->phone ?? '-',
                                'username'        => $update['message']['from']['username'] ?? null,
                                'user_type'       => 'teacher',
                                'is_active'       => true,
                                'approval_status' => 'approved',
                                'subscribed_at'   => now(),
                            ]
                        );

                        $reply = "✅ *Berhasil Menautkan Akun!*\n\n" .
                                 "Akun Telegram Anda sukses terhubung sebagai Guru: *{$teacher->name}*.\n\n" .
                                 "📌 *Menu Perintah Guru:*\n" .
                                 "🔹 `/jadwal` : Jadwal Mengajar Hari Ini\n" .
                                 "🔹 `/jadwal_minggu` : Jadwal Mengajar Seminggu Penuh\n" .
                                 "🔹 `/walas` : Rekapitulasi Presensi Walas\n" .
                                 "🔹 `/kabeng` : Rekapitulasi Bengkel / Konsentrasi\n" .
                                 "🔹 `/proka` : Rekapitulasi Program Keahlian\n" .
                                 "🔹 `/bk` : Daftar Terlambat Guru BK";

                        $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
                        $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                        return response()->json(['status' => 'ok']);
                    }
                }

                $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
                    $q->where('chat_id', (string)$chatId)
                      ->orWhere('chat_id', (int)$chatId);
                })->first();

                if ($subscriber && $subscriber->approval_status === 'approved' && $subscriber->is_active) {
                    if ($subscriber->user_type === 'teacher' || !empty($subscriber->teacher_id)) {
                        $teacher = Teacher::find($subscriber->teacher_id);
                        $teacherName = $teacher ? $teacher->name : $subscriber->parent_name;

                        $reply = "✅ *Akun Anda Sudah Terdaftar & Aktif!*\n\n" .
                                 "👤 Peran: *Guru / Pegawai*\n" .
                                 "👨‍🏫 Nama: *{$teacherName}*\n\n" .
                                 "📌 *Menu Perintah Guru & Tugas Tambahan:*\n" .
                                 "🔹 `/jadwal` : Jadwal Mengajar Hari Ini\n" .
                                 "🔹 `/jadwal_minggu` : Jadwal Mengajar Seminggu\n" .
                                 "🔹 `/walas` : Rekapitulasi Presensi Walas\n" .
                                 "🔹 `/kabeng` : Rekapitulasi Bengkel / Konsentrasi\n" .
                                 "🔹 `/proka` : Rekapitulasi Program Keahlian\n" .
                                 "🔹 `/bk` : Daftar Terlambat Guru BK\n" .
                                 "🔹 `/unsubscribe` : Berhenti Berlangganan Bot";

                        $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
                        $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                        return response()->json(['status' => 'ok']);
                    }

                    $student     = Student::with('classroom')->find($subscriber->student_id);
                    $studentName = $student ? $student->name : '-';
                    $className   = $student && $student->classroom ? $student->classroom->name : '-';
                    $roleLabel   = ($subscriber->user_type ?? 'parent') === 'student' ? 'Siswa' : 'Orang Tua/Wali';

                    $reply = "✅ *Akun Anda Sudah Terdaftar & Aktif!*\n\n" .
                             "👤 Peran: *{$roleLabel}*\n" .
                             "🎯 Siswa Terhubung: *{$studentName}*\n" .
                             "🏫 Kelas: *{$className}*\n\n" .
                             "📌 *Layanan Fitur Bot:*\n" .
                             "🔹 `/izin` : Form Pengajuan Izin / Sakit\n" .
                             "🔹 `/rekap` : Menu Rekap Interaktif\n" .
                             "🔹 `/gerbang` : Rekap Presensi Gerbang\n" .
                             "🔹 `/pembelajaran` : Rekap Presensi Kelas\n" .
                             "🔹 `/sholat` : Rekap Presensi Sholat\n" .
                             "🔹 `/semua` : Rekap Semua Jenis Presensi\n" .
                             "🔹 `/unsubscribe` : Berhenti Berlangganan Bot";

                    $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
                    $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                    return response()->json(['status' => 'ok']);
                }

                if ($subscriber && $subscriber->approval_status === 'pending' && !$subscriber->is_active) {
                    $reply = "⏳ *Pendaftaran Anda Sedang Menunggu Verifikasi Admin*\n\n" .
                             "Mohon tunggu konfirmasi persetujuan dari Admin SISFO.";
                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                    return response()->json(['status' => 'ok']);
                }

                $prompt = "🤖 *SELAMAT DATANG DI BOT SISFO Listrik SMKN 1 BUKITTINGGI*\n\n" .
                          "Bot ini menyediakan layanan absensi dan notifikasi untuk Siswa, Orang Tua, serta Guru/Pegawai.\n\n" .
                          "Silakan tekan tombol *'📱 Bagikan Nomor Kontak Saya'* di bawah untuk mendaftar.";
                
                $telegramService->sendContactButton($chatId, $prompt);
                return response()->json(['status' => 'ok']);
            }

            // 7. HANDLE COMMAND /help
            if ($lowerText === '/help') {
                $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
                    $q->where('chat_id', (string)$chatId)->orWhere('chat_id', (int)$chatId);
                })->first();

                if ($subscriber && ($subscriber->user_type === 'teacher' || !empty($subscriber->teacher_id))) {
                    $reply = "ℹ️ *BANTUAN BOT SISFO (GURU & PEGAWAI)*\n\n" .
                             "📌 *Perintah Guru:*\n" .
                             "• `/jadwal` : Cek jadwal mengajar hari ini\n" .
                             "• `/jadwal_minggu` : Cek jadwal mengajar seminggu penuh\n" .
                             "• `/walas` : Rekap presensi gerbang kelas bimbingan\n" .
                             "• `/kabeng` : Rekap presensi di bengkel/jurusan\n" .
                             "• `/proka` : Rekap kehadiran program keahlian\n" .
                             "• `/bk` : Daftar terlambat guru BK\n" .
                             "• `/unsubscribe` : Berhenti berlangganan bot";
                } else {
                    $reply = "ℹ️ *BANTUAN BOT SISFO*\n\n" .
                             "📌 *Perintah Utama:*\n" .
                             "• `/izin` : Pengajuan Izin / Sakit Tidak Masuk\n" .
                             "• `/rekap` : Tampilkan menu rekap lengkap\n" .
                             "• `/gerbang` : Cek absensi di gerbang sekolah\n" .
                             "• `/pembelajaran` : Cek absensi di jam kelas\n" .
                             "• `/sholat` : Cek absensi ibadah sholat\n" .
                             "• `/semua` : Tampilkan rekap semua presensi\n" .
                             "• `/unsubscribe` : Berhenti berlangganan bot\n" .
                             "• `stop` / `/batal` : Batal pengajuan/interaksi";
                }

                $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
                $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                return response()->json(['status' => 'ok']);
            }

            // 8. HANDLE INPUT NISN/NIS PENDING SISWA
            $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
                $q->where('chat_id', (string)$chatId)->orWhere('chat_id', (int)$chatId);
            })->first();

            if ($subscriber && $subscriber->approval_status === 'pending' && !$subscriber->student_id && !$subscriber->teacher_id && !empty($text)) {
                $student = Student::where('nisn', $text)->orWhere('nis', $text)->first();

                if (!$student) {
                    $reply = "❌ *Siswa tidak ditemukan.*\n\n" .
                             "NISN/NIS *{$text}* tidak cocok dengan database sekolah. Silakan ketik ulang nomor NISN/NIS yang benar:";
                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                    return response()->json(['status' => 'ok']);
                }

                $subscriber->update(['student_id' => $student->id]);
                $className = optional($student->classroom)->name ?? '-';

                $reply = "⏳ *Pengajuan Diteruskan ke Admin!*\n\n" .
                         "Data Siswa Ditemukan:\n" .
                         "👤 Nama: *{$student->name}*\n" .
                         "🏫 Kelas: *{$className}*\n\n" .
                         "Mohon tunggu konfirmasi persetujuan dari Admin.";
                
                $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                return response()->json(['status' => 'ok']);
            }

            // =========================================================================
            // 9. HYBRID INTENT ROUTER & FALLBACK AI (DISINI TEMPATNYA)
            // =========================================================================
            if (!empty($text) && !str_starts_with($text, '/')) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

                // Jalankan Handler Hybrid
                $aiController->handleHybridRequest($chatId, $text, $subscriber);
                
                return response()->json(['status' => 'ok']);
            }

        } catch (\Exception $e) {
            Log::error("Telegram Webhook Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        }

        return response()->json(['status' => 'ok']);
    }
}