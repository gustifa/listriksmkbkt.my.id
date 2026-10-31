<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Schedule;
use App\Models\IzinSiswa;
use App\Services\TelegramService;
use App\Models\TelegramSubscriber;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        try {
            $update = $request->all();

            // =========================================================================
            // 1. HANDLE CALLBACK QUERY (APPROVAL IZIN, PEMBATALAN, REKAP & ADMIN)
            // =========================================================================
            if (isset($update['callback_query'])) {
                $callback = $update['callback_query'];
                $data     = $callback['data'];

                if (str_starts_with($data, 'rekap_')) {
                    $this->handleRekapCallback($callback, $telegramService);
                } elseif (str_starts_with($data, 'cancel_user_izin_')) {
                    $this->handleUserCancelIzinCallback($callback, $telegramService);
                } elseif (str_starts_with($data, 'appizin_') || str_starts_with($data, 'rejizin_')) {
                    $this->handleIzinApprovalCallback($callback, $telegramService);
                } else {
                    $this->handleAdminCallback($callback, $telegramService);
                }

                return response()->json(['status' => 'ok']);
            }

            if (!isset($update['message'])) {
                return response()->json(['status' => 'ok']);
            }

            $chatId  = (string) $update['message']['chat']['id'];
            $message = $update['message'];

            $setting   = AttendanceSetting::first();
            $groupLink = $setting?->telegram_admin_group_link 
                ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

            // =========================================================================
            // 2. HANDLE BAGIKAN KONTAK (VERIFIKASI SISWA, ORANG TUA, & GURU)
            // =========================================================================
            if (isset($message['contact'])) {
                try {
                    $contact    = $message['contact'];
                    $phone      = $contact['phone_number'];
                    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

                    $firstName  = $contact['first_name'] ?? '';
                    $lastName   = $contact['last_name'] ?? '';
                    $parentName = trim($firstName . ' ' . $lastName) ?: 'Pengguna';
                    $username   = isset($message['from']['username']) ? (string)$message['from']['username'] : null;

                    $lastDigits = strlen($cleanPhone) >= 9 ? substr($cleanPhone, -9) : $cleanPhone;

                    // A. CEK JIKA KONTAK TERDAFTAR SEBAGAI GURU / PEGAWAI
                    $teacher = Teacher::where('phone', 'like', '%' . $lastDigits)->first();

                    if ($teacher) {
                        TelegramSubscriber::updateOrCreate(
                            ['chat_id' => $chatId],
                            [
                                'teacher_id'      => $teacher->id,
                                'student_id'      => null,
                                'parent_name'     => $teacher->name,
                                'phone_number'    => (string) $cleanPhone,
                                'username'        => $username,
                                'user_type'       => 'teacher',
                                'is_active'       => true,
                                'approval_status' => 'approved',
                                'subscribed_at'   => now()->format('Y-m-d H:i:s'),
                            ]
                        );

                        $reply = "👨‍🏫 *Selamat Datang, Bapak/Ibu Guru!*\n\n" .
                                 "Akun Telegram Anda berhasil terverifikasi sebagai *{$teacher->name}*.\n\n" .
                                 "📌 *Menu Perintah Guru & Tugas Tambahan:*\n" .
                                 "🔹 `/jadwal` : Jadwal Mengajar Hari Ini\n" .
                                 "🔹 `/walas` : Rekapitulasi Presensi Kelas Bimbingan (Khusus Walas)\n" .
                                 "🔹 `/kabeng` : Rekap Presensi Konsentrasi Keahlian / Bengkel (Khusus Kabeng)\n" .
                                 "🔹 `/proka` : Rekapitulasi Program Keahlian (Khusus Proka)\n" .
                                 "🔹 `/bk` : Daftar Siswa Terlambat Hari Ini (Khusus Guru BK)\n" .
                                 "🔹 `/unsubscribe` : Berhenti Berlangganan Bot";

                        $keyboard = [
                            [
                                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                            ]
                        ];

                        $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                        return response()->json(['status' => 'ok']);
                    }

                    // B. CEK JIKA KONTAK TERDAFTAR SEBAGAI SISWA ATAU ORANG TUA
                    $studentByParent  = Student::where('phone', 'like', '%' . $lastDigits)->first();
                    $studentByStudent = Student::where('student_phone', 'like', '%' . $lastDigits)->first();

                    if ($studentByParent) {
                        $student  = $studentByParent;
                        $userType = 'parent';
                    } elseif ($studentByStudent) {
                        $student  = $studentByStudent;
                        $userType = 'student';
                    } else {
                        $student  = null;
                        $userType = 'parent';
                    }

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
                                'teacher_id'      => null,
                                'parent_name'     => (string) $parentName,
                                'phone_number'    => (string) $cleanPhone,
                                'username'        => $username,
                                'user_type'       => $userType,
                                'is_active'       => true,
                                'approval_status' => 'approved',
                                'subscribed_at'   => now()->format('Y-m-d H:i:s'),
                            ]
                        );

                        $className = optional($student->classroom)->name ?? '-';
                        $roleLabel = $userType === 'student' ? 'Siswa' : 'Orang Tua/Wali';

                        $reply = "✅ *Pendaftaran Berhasil!*\n\n" .
                                "Akun Telegram Anda telah terhubung sebagai *{$roleLabel}*:\n" .
                                "👤 Nama Siswa: *{$student->name}*\n" .
                                "🏫 Kelas: *{$className}*\n\n" .
                                "📌 *Menu Layanan Bot:*\n" .
                                "🔹 `/izin` : Pengajuan Izin / Sakit\n" .
                                "🔹 `/rekap` : Menu Pilihan Rekap Absensi\n" .
                                "🔹 `/gerbang` : Rekap Kehadiran Gerbang\n" .
                                "🔹 `/pembelajaran` : Rekap Jam Belajar\n" .
                                "🔹 `/sholat` : Rekap Ibadah Sholat\n" .
                                "🔹 `/semua` : Rekap Semua Jenis Presensi\n" .
                                "🔹 `/unsubscribe` : Berhenti Berlangganan Bot\n\n" .
                                "Silakan klik tombol di bawah untuk bergabung ke Grup Telegram Admin SISFO:";

                        $keyboard = [
                            [
                                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                            ]
                        ];

                        $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                    } else {
                        TelegramSubscriber::updateOrCreate(
                            ['chat_id' => $chatId],
                            [
                                'student_id'      => null,
                                'teacher_id'      => null,
                                'parent_name'     => (string) $parentName,
                                'phone_number'    => (string) $cleanPhone,
                                'username'        => $username,
                                'user_type'       => 'parent',
                                'is_active'       => false,
                                'approval_status' => 'pending',
                                'subscribed_at'   => now()->format('Y-m-d H:i:s'),
                            ]
                        );

                        $reply = "⚠️ *Nomor HP Anda (`+{$cleanPhone}`) belum terdaftar di database sekolah.*\n\n" .
                                "Silakan *ketik nomor NISN atau NIS* siswa di bawah ini untuk mengajukan pendaftaran ke Admin SISFO:";

                        $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                    }
                } catch (\Exception $e) {
                    Log::error("Error Telegram Contact Webhook: " . $e->getMessage());
                    $telegramService->sendMessageWithRemoveKeyboard($chatId, "⚠️ Terjadi kendala sistem. Silakan coba beberapa saat lagi.");
                }

                return response()->json(['status' => 'ok']);
            }

            // =========================================================================
            // 3. HANDLE PESAN TEKS & COMMAND BOT
            // =========================================================================
            $text      = trim($message['text'] ?? '');
            $lowerText = strtolower($text);
            $photo     = $message['photo'] ?? null;

            // A. COMMAND BATAL PROSES / STOP
            if (in_array($lowerText, ['/batal', 'batal', '/batal_izin', 'stop', '/stop', 'batal!'])) {
                Cache::forget("telegram_permit_step_{$chatId}");

                $student = $this->getSubscriberStudent($chatId, $telegramService);

                if ($student) {
                    $today = Carbon::today()->format('Y-m-d');
                    $pendingIzin = IzinSiswa::where('student_id', $student->id)
                        ->whereDate('date', $today)
                        ->where('status', 'pending')
                        ->first();

                    if ($pendingIzin) {
                        $pendingIzin->delete();

                        $telegramService->sendMessage(
                            $chatId, 
                            "🗑️ *Pengajuan Izin/Sakit Dibatalkan & Dihapus!*\n\nData pengajuan untuk siswa *{$student->name}* pada hari ini telah berhasil dihapus dari sistem database.", 
                            'info', 
                            'Pengajuan Dibatalkan'
                        );

                        return response()->json(['status' => 'ok']);
                    }
                }

                $telegramService->sendMessage($chatId, "❌ *Proses pengajuan/interaksi telah dihentikan.*", 'info', 'Proses Dihentikan');
                return response()->json(['status' => 'ok']);
            }

            // B. COMMAND BERHENTI BERLANGGANAN (UNSUBSCRIBE / KELUAR BOT)
            if (in_array($lowerText, ['/unsubscribe', 'unsubscribe', '/keluar', 'keluar', '/berhenti', 'berhenti'])) {
                Cache::forget("telegram_permit_step_{$chatId}");

                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

                if ($subscriber) {
                    if ($subscriber->student_id) {
                        try {
                            Student::where('id', $subscriber->student_id)->update(['telegram_chat_id' => null]);
                        } catch (\Exception $ex) {
                            Log::warning("Gagal reset telegram_chat_id di student: " . $ex->getMessage());
                        }
                    }

                    $subscriber->delete();

                    $reply = "👋 *Berhasil Berhenti Berlangganan!*\n\n" .
                             "Akun Telegram Anda telah dilepas dari sistem SISFO SMKN 1 Bukittinggi dan tidak akan menerima notifikasi absensi lagi.\n\n" .
                             "Jika ingin mendaftar kembali di kemudian hari, silakan ketik `/start`.";

                    $telegramService->sendMessageWithRemoveKeyboard($chatId, $reply);
                } else {
                    $telegramService->sendMessage($chatId, "⚠️ *Akun Telegram Anda belum terdaftar dalam sistem langganan SISFO.*");
                }

                return response()->json(['status' => 'ok']);
            }

            // C. COMMAND JADWAL MENGAJAR GURU (/jadwal)
            if (in_array($lowerText, ['/jadwal', 'jadwal', '/jadwal_mengajar'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();

                if (!$subscriber || !$subscriber->teacher_id) {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/jadwal` khusus untuk akun Guru yang telah terdaftar.");
                    return response()->json(['status' => 'ok']);
                }

                $this->sendJadwalGuru($chatId, $subscriber->teacher_id, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            // D. COMMAND REKAP WALI KELAS (/walas)
            if (in_array($lowerText, ['/walas', 'walas', '/rekap_walas'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();

                if (!$subscriber || !$subscriber->teacher_id) {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/walas` khusus untuk akun Wali Kelas.");
                    return response()->json(['status' => 'ok']);
                }

                $this->sendRekapWalas($chatId, $subscriber->teacher_id, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            // E. COMMAND REKAP KEPALA BENGKEL / KABENG (/kabeng)
            if (in_array($lowerText, ['/kabeng', 'kabeng', '/rekap_bengkel'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();

                if (!$subscriber || !$subscriber->teacher_id) {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/kabeng` khusus untuk Kepala Bengkel / Konsentrasi Keahlian.");
                    return response()->json(['status' => 'ok']);
                }

                $this->sendRekapKabeng($chatId, $subscriber->teacher_id, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            // F. COMMAND REKAP KETUA PROGRAM KEAHLIAN (/proka)
            if (in_array($lowerText, ['/proka', 'proka'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();

                if (!$subscriber || !$subscriber->teacher_id) {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/proka` khusus untuk Ketua Program Keahlian.");
                    return response()->json(['status' => 'ok']);
                }

                $this->sendRekapProka($chatId, $subscriber->teacher_id, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            // G. COMMAND REKAP GURU BK (/bk)
            if (in_array($lowerText, ['/bk', 'bk', '/terlambat_bk'])) {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->where('user_type', 'teacher')->first();

                if (!$subscriber || !$subscriber->teacher_id) {
                    $telegramService->sendMessage($chatId, "⚠️ Perintah `/bk` khusus untuk Guru BK.");
                    return response()->json(['status' => 'ok']);
                }

                $this->sendRekapBK($chatId, $subscriber->teacher_id, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            // H. COMMAND /izin ATAU /sakit
            if (in_array($lowerText, ['/izin', 'izin', '/sakit', 'sakit'])) {
                Cache::forget("telegram_permit_step_{$chatId}");

                $student = $this->getSubscriberStudent($chatId, $telegramService);
                if (!$student) {
                    return response()->json(['status' => 'ok']);
                }

                $type = str_contains($lowerText, 'sakit') ? 'sakit' : 'izin';

                Cache::put("telegram_permit_step_{$chatId}", [
                    'type'       => $type,
                    'step'       => 'WAITING_REASON',
                    'student_id' => $student->id
                ], now()->addMinutes(10));

                $jenisText = strtoupper($type);
                $prompt = "📝 *FORM PENGAJUAN " . $jenisText . "*\n\n" .
                          "👤 Siswa: *{$student->name}*\n" .
                          "🏫 Kelas: *" . (optional($student->classroom)->name ?? '-') . "*\n\n" .
                          "Silakan ketikkan *alasan/keterangan lengkap* mengapa siswa tidak dapat hadir hari ini.\n\n" .
                          "_(Ketik `stop` atau `/batal` untuk membatalkan)_";

                $telegramService->sendMessage($chatId, $prompt, 'info', 'Pengajuan Izin');
                return response()->json(['status' => 'ok']);
            }

            // I. PROSES MULTI-STEP CONVERSATION PENGAJUAN IZIN/SAKIT
            $permitSession = Cache::get("telegram_permit_step_{$chatId}");

            if ($permitSession) {
                $student = Student::find($permitSession['student_id']);

                if ($student) {
                    if ($permitSession['step'] === 'WAITING_REASON' && !empty($text) && !str_starts_with($text, '/')) {
                        $permitSession['reason'] = $text;

                        if ($permitSession['type'] === 'sakit') {
                            $permitSession['step'] = 'WAITING_PHOTO';
                            Cache::put("telegram_permit_step_{$chatId}", $permitSession, now()->addMinutes(10));

                            $prompt = "📸 *UNGGAH SURAT DOKTER / KETERANGAN SAKIT*\n\n" .
                                      "Silakan *kirimkan foto surat dokter* Anda ke chat ini.\n\n" .
                                      "_(Ketik *'tanpa surat'* jika tidak ada foto, atau ketik *'stop'* untuk membatalkan)_";

                            $telegramService->sendMessage($chatId, $prompt, 'info', 'Upload Surat');
                            return response()->json(['status' => 'ok']);
                        } else {
                            $phone = $student->phone ?? $student->student_phone;
                            $this->saveIzinToDatabase($student, 'izin', $permitSession['reason'], null, $phone, $chatId, $telegramService);
                            Cache::forget("telegram_permit_step_{$chatId}");
                            return response()->json(['status' => 'ok']);
                        }
                    }

                    if ($permitSession['step'] === 'WAITING_PHOTO') {
                        $filePath = null;

                        if ($photo) {
                            $fileId = end($photo)['file_id'];
                            $filePath = $this->downloadTelegramFile($fileId, $student->nis ?? 'bot');
                        }

                        $phone = $student->phone ?? $student->student_phone;
                        $this->saveIzinToDatabase($student, 'sakit', $permitSession['reason'], $filePath, $phone, $chatId, $telegramService);
                        Cache::forget("telegram_permit_step_{$chatId}");
                        return response()->json(['status' => 'ok']);
                    }
                }
            }

            // J. COMMAND PERINTAH REKAP SISWA
            if (in_array($lowerText, ['/rekap', '/menu', 'rekap', 'menu'])) {
                $this->sendRekapMenu($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/gerbang', 'gerbang'])) {
                $this->sendRekapGerbang($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/pembelajaran', '/kelas', 'pembelajaran'])) {
                $this->sendRekapPembelajaran($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/sholat', 'sholat'])) {
                $this->sendRekapSholat($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            if (in_array($lowerText, ['/semua', 'semua', '/all', 'all'])) {
                $this->sendRekapSemua($chatId, $telegramService);
                return response()->json(['status' => 'ok']);
            }

            // K. PERINTAH /start ATAU /daftar
            if (str_starts_with($lowerText, '/start') || $lowerText === '/daftar') {

                if (preg_match('/^\/start\s+student_([a-f0-9\-]+)$/i', $text, $matches)) {
                    $studentId = $matches[1];
                    $student   = Student::find($studentId);

                    if ($student) {
                        $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

                        if (empty($student->student_phone) && (!$subscriber || empty($subscriber->phone_number))) {
                            TelegramSubscriber::updateOrCreate(
                                ['chat_id' => $chatId],
                                [
                                    'student_id'      => $student->id,
                                    'teacher_id'      => null,
                                    'parent_name'     => $student->name,
                                    'user_type'       => 'student',
                                    'is_active'       => false,
                                    'approval_status' => 'pending',
                                ]
                            );

                            $prompt = "📱 *Verifikasi Nomor Telepon Siswa*\n\n" .
                                      "Halo *{$student->name}*, nomor telepon Anda belum terdata di sistem sekolah.\n\n" .
                                      "Silakan tekan tombol *'📱 Bagikan Nomor Kontak Saya'* di bawah untuk mendaftarkan nomor HP Telegram Anda.";

                            $telegramService->sendContactButton($chatId, $prompt);
                            return response()->json(['status' => 'ok']);
                        }

                        if (empty($student->student_phone) && $subscriber && !empty($subscriber->phone_number)) {
                            try {
                                $student->update([
                                    'student_phone'    => $subscriber->phone_number,
                                    'telegram_chat_id' => $chatId,
                                ]);
                            } catch (\Exception $ex) {}
                        } else {
                            try {
                                $student->update(['telegram_chat_id' => $chatId]);
                            } catch (\Exception $ex) {}
                        }

                        $firstName   = $message['from']['first_name'] ?? '';
                        $lastName    = $message['from']['last_name'] ?? '';
                        $studentName = trim($firstName . ' ' . $lastName) ?: $student->name;
                        $username    = $message['from']['username'] ?? null;

                        TelegramSubscriber::updateOrCreate(
                            ['chat_id' => $chatId],
                            [
                                'student_id'      => $student->id,
                                'teacher_id'      => null,
                                'parent_name'     => $studentName,
                                'phone_number'    => $student->student_phone ?? ($subscriber->phone_number ?? null),
                                'username'        => $username,
                                'user_type'       => 'student',
                                'is_active'       => true,
                                'approval_status' => 'approved',
                                'subscribed_at'   => now()->format('Y-m-d H:i:s'),
                            ]
                        );

                        $className = optional($student->classroom)->name ?? '-';

                        $reply = "🎉 *Pendaftaran Berhasil!*\n\n" .
                                 "Akun Telegram Anda telah terhubung sebagai *Siswa*:\n" .
                                 "👤 Nama Siswa: *{$student->name}*\n" .
                                 "🏫 Kelas: *{$className}*\n" .
                                 "🆔 NISN: *{$student->nisn}*\n\n" .
                                 "📌 *Gunakan perintah bot berikut:*\n" .
                                 "🔹 `/izin` : Ajukan Izin / Sakit\n" .
                                 "🔹 `/rekap` : Cek Riwayat Absensi\n" .
                                 "🔹 `/semua` : Rekap Semua Jenis Presensi\n" .
                                 "🔹 `/unsubscribe` : Berhenti Berlangganan";

                        $keyboard = [
                            [
                                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                            ]
                        ];

                        $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                        return response()->json(['status' => 'ok']);
                    }
                }

                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

                if ($subscriber && $subscriber->approval_status === 'approved' && $subscriber->is_active) {
                    if ($subscriber->user_type === 'teacher' && $subscriber->teacher_id) {
                        $teacher = Teacher::find($subscriber->teacher_id);
                        $reply = "✅ *Akun Anda Sudah Terdaftar & Aktif!*\n\n" .
                                 "👤 Peran: *Guru / Pegawai*\n" .
                                 "👨‍🏫 Nama: *{$teacher?->name}*\n\n" .
                                 "📌 *Menu Perintah Guru:*\n" .
                                 "🔹 `/jadwal` : Jadwal Mengajar Hari Ini\n" .
                                 "🔹 `/walas` : Rekapitulasi Presensi Walas\n" .
                                 "🔹 `/kabeng` : Rekapitulasi Bengkel / Konsentrasi\n" .
                                 "🔹 `/proka` : Rekapitulasi Program Keahlian\n" .
                                 "🔹 `/bk` : Daftar Terlambat Guru BK\n" .
                                 "🔹 `/unsubscribe` : Berhenti Berlangganan Bot";
                    } else {
                        $student     = Student::find($subscriber->student_id);
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
                    }

                    $keyboard = [
                        [
                            ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                        ]
                    ];

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

            // L. COMMAND /help
            if ($lowerText === '/help') {
                $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

                if ($subscriber && $subscriber->user_type === 'teacher') {
                    $reply = "ℹ️ *BANTUAN BOT SISFO (GURU & PEGAWAI)*\n\n" .
                             "📌 *Perintah Guru:*\n" .
                             "• `/jadwal` : Cek jadwal mengajar hari ini\n" .
                             "• `/walas` : Rekap presensi gerbang kelas bimbingan\n" .
                             "• `/kabeng` : Rekap presensi di bengkel/jurusan\n" .
                             "• `/proka` : Rekap kehadiran program keahlian\n" .
                             "• `/bk` : Daftar siswa terlambat hari ini\n" .
                             "• `/unsubscribe` : Berhenti berlangganan bot\n\n" .
                             "Grup koordinasi Admin:";
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
                             "• `stop` / `/batal` : Batal pengajuan/interaksi\n\n" .
                             "Butuh bantuan lebih lanjut? Bergabunglah dengan Grup Admin kami:";
                }

                $keyboard = [
                    [
                        ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                    ]
                ];

                $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
                return response()->json(['status' => 'ok']);
            }

            // M. HANDLE INPUT NISN/NIS PENDING SISWA
            $subscriber = TelegramSubscriber::where('chat_id', $chatId)->first();

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
                $this->notifyAdminGroup($subscriber, $student, $telegramService);

                return response()->json(['status' => 'ok']);
            }

        } catch (\Exception $e) {
            Log::error("Telegram Webhook Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        }

        return response()->json(['status' => 'ok']);
    }

    // =========================================================================
    // HELPER LOGIKA FUNGSI PERAN GURU & TUGAS TAMBAHAN
    // =========================================================================

    // private function sendJadwalGuru(string $chatId, $teacherId, TelegramService $telegramService): void
    // {
    //     $teacher = Teacher::find($teacherId);
    //     if (!$teacher) return;

    //     $daysMap = [
    //         'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    //         'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    //     ];
    //     $todayIndo = $daysMap[Carbon::now()->format('l')];

    //     $schedules = Schedule::with(['classroom', 'subject'])
    //         ->where('teacher_id', $teacherId)
    //         ->where('day', $todayIndo)
    //         ->orderBy('start_time', 'asc')
    //         ->get();

    //     $text = "📖 *JADWAL MENGAJAR HARI INI ({$todayIndo})*\n\n" .
    //             "👨‍🏫 Guru: *{$teacher->name}*\n" .
    //             "--------------------------------------\n";

    //     if ($schedules->isEmpty()) {
    //         $text .= "_\nTidak ada jadwal mengajar untuk hari {$todayIndo}._\n";
    //     } else {
    //         foreach ($schedules as $s) {
    //             $jamMasuk   = substr($s->start_time, 0, 5);
    //             $jamSelesai = substr($s->end_time, 0, 5);
    //             $kelas      = optional($s->classroom)->name ?? '-';
    //             $mapel      = optional($s->subject)->name ?? '-';

    //             $text .= "⏰ *{$jamMasuk} - {$jamSelesai} WIB*\n" .
    //                      " ├ 🏫 Kelas : *{$kelas}*\n" .
    //                      " └ 📚 Mapel : {$mapel}\n\n";
    //         }
    //     }

    //     $telegramService->sendMessage($chatId, $text);
    // }

    // private function sendRekapWalas(string $chatId, $teacherId, TelegramService $telegramService): void
    // {
    //     $classroom = Classroom::where('homeroom_teacher_id', $teacherId)->first();

    //     if (!$classroom) {
    //         $telegramService->sendMessage($chatId, "⚠️ Anda tidak tercatat sebagai Wali Kelas di sistem SISFO.");
    //         return;
    //     }

    //     $today = Carbon::today()->format('Y-m-d');
    //     $studentsCount = Student::where('classroom_id', $classroom->id)->count();

    //     $attendances = DB::table('daily_attendances')
    //         ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //         ->where('students.classroom_id', $classroom->id)
    //         ->whereDate('daily_attendances.date', $today)
    //         ->select('daily_attendances.status', DB::raw('count(*) as total'))
    //         ->groupBy('daily_attendances.status')
    //         ->pluck('total', 'status')
    //         ->toArray();

    //     $hadir     = $attendances['hadir'] ?? 0;
    //     $terlambat = $attendances['terlambat'] ?? 0;
    //     $izin      = $attendances['izin'] ?? 0;
    //     $sakit     = $attendances['sakit'] ?? 0;
    //     $alfa      = $studentsCount - ($hadir + $terlambat + $izin + $sakit);

    //     $text = "🏫 *REKAP PRESENSI GERBANG (WALI KELAS)*\n\n" .
    //             "📌 Kelas : *{$classroom->name}*\n" .
    //             "📅 Tanggal : *" . date('d/m/Y') . "*\n" .
    //             "👥 Total Siswa : *{$studentsCount} Siswa*\n" .
    //             "--------------------------------------\n" .
    //             "✅ Hadir : `{$hadir}`\n" .
    //             "⚠️ Terlambat : `{$terlambat}`\n" .
    //             "ℹ️ Izin : `{$izin}`\n" .
    //             "🏥 Sakit : `{$sakit}`\n" .
    //             "❌ Belum Scan/Alfa : `{$alfa}`\n\n" .
    //             "--- *Listrik SMKN 1 Bukittinggi* ---";

    //     $telegramService->sendMessage($chatId, $text);
    // }

    // private function sendRekapKabeng(string $chatId, $teacherId, TelegramService $telegramService): void
    // {
    //     $major = Major::where('workshop_teacher_id', $teacherId)->first();

    //     if (!$major) {
    //         $telegramService->sendMessage($chatId, "⚠️ Anda tidak tercatat sebagai Kepala Bengkel / Kepala Konsentrasi Keahlian.");
    //         return;
    //     }

    //     $today = Carbon::today()->format('Y-m-d');

    //     $rekapKelas = DB::table('daily_attendances')
    //         ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //         ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
    //         ->where('classrooms.major_id', $major->id)
    //         ->whereDate('daily_attendances.date', $today)
    //         ->select(
    //             'classrooms.name as nama_kelas',
    //             DB::raw('count(daily_attendances.arrival_time) as total_datang'),
    //             DB::raw('count(daily_attendances.departure_time) as total_pulang')
    //         )
    //         ->groupBy('classrooms.id', 'classrooms.name')
    //         ->get();

    //     $text = "🛠️ *REKAP PRESENSI KONSENTRASI KEAHLIAN*\n\n" .
    //             "🏭 Bengkel / Jurusan : *{$major->name}*\n" .
    //             "📅 Tanggal : *" . date('d/m/Y') . "*\n" .
    //             "======================================\n\n";

    //     if ($rekapKelas->isEmpty()) {
    //         $text .= "_Belum ada data kehadiran siswa di bengkel hari ini._\n";
    //     } else {
    //         foreach ($rekapKelas as $rk) {
    //             $text .= "🏫 *{$rk->nama_kelas}*\n" .
    //                      " ├ Hadir Datang : `{$rk->total_datang}` Siswa\n" .
    //                      " └ Sudah Pulang : `{$rk->total_pulang}` Siswa\n\n";
    //         }
    //     }

    //     $telegramService->sendMessage($chatId, $text);
    // }

    // private function sendRekapProka(string $chatId, $teacherId, TelegramService $telegramService): void
    // {
    //     $major = Major::where('program_head_teacher_id', $teacherId)->first();

    //     if (!$major) {
    //         $telegramService->sendMessage($chatId, "⚠️ Anda tidak tercatat sebagai Ketua Program Keahlian (Proka).");
    //         return;
    //     }

    //     $today = Carbon::today()->format('Y-m-d');

    //     $totalSiswa = Student::whereHas('classroom', function($q) use ($major) {
    //         $q->where('major_id', $major->id);
    //     })->count();

    //     $totalHadir = DB::table('daily_attendances')
    //         ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //         ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
    //         ->where('classrooms.major_id', $major->id)
    //         ->whereDate('daily_attendances.date', $today)
    //         ->count();

    //     $persentase = $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100, 1) : 0;

    //     $text = "📊 *REKAPITULASI PROGRAM KEAHLIAN*\n\n" .
    //             "🎯 Program : *{$major->name}*\n" .
    //             "📅 Tanggal : *" . date('d/m/Y') . "*\n" .
    //             "👥 Total Siswa Program : *{$totalSiswa}*\n" .
    //             "✅ Hadir Hari Ini : *{$totalHadir} ({$persentase}%)*\n";

    //     $telegramService->sendMessage($chatId, $text);
    // }

    // private function sendRekapBK(string $chatId, $teacherId, TelegramService $telegramService): void
    // {
    //     $today = Carbon::today()->format('Y-m-d');

    //     $lateStudents = DB::table('daily_attendances')
    //         ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //         ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
    //         ->whereDate('daily_attendances.date', $today)
    //         ->where('daily_attendances.status', 'terlambat')
    //         ->select('students.name', 'classrooms.name as nama_kelas', 'daily_attendances.arrival_time')
    //         ->orderBy('daily_attendances.arrival_time', 'asc')
    //         ->limit(15)
    //         ->get();

    //     $text = "📋 *DAFTAR SISWA TERLAMBAT HARI INI (GURU BK)*\n\n" .
    //             "📅 Tanggal : *" . date('d/m/Y') . "*\n" .
    //             "--------------------------------------\n";

    //     if ($lateStudents->isEmpty()) {
    //         $text .= "_\nTidak ada siswa terlambat hari ini._\n";
    //     } else {
    //         foreach ($lateStudents as $ls) {
    //             $jam = substr($ls->arrival_time, 0, 5);
    //             $text .= "⚠️ *{$ls->name}* ({$ls->nama_kelas})\n" .
    //                      " └ Jam Datang: `{$jam}` WIB\n\n";
    //         }
    //     }

    //     $telegramService->sendMessage($chatId, $text);
    // }

    /**
     * 1. Menampilkan Jadwal Mengajar Guru Hari Ini
     */
    private function sendJadwalGuru(string $chatId, $teacherId, TelegramService $telegramService): void
    {
        $teacher = Teacher::find($teacherId);
        if (!$teacher) return;

        $daysMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $todayIndo = $daysMap[Carbon::now()->format('l')];

        $schedules = Schedule::with(['classroom', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('day', $todayIndo)
            ->orderBy('start_time', 'asc')
            ->get();

        $text = "📖 *JADWAL MENGAJAR HARI INI ({$todayIndo})*\n\n" .
                "👨‍🏫 Guru: *{$teacher->name}*\n" .
                "--------------------------------------\n";

        if ($schedules->isEmpty()) {
            $text .= "_\nTidak ada jadwal mengajar untuk hari {$todayIndo}._\n";
        } else {
            foreach ($schedules as $s) {
                $jamMasuk   = substr($s->start_time, 0, 5);
                $jamSelesai = substr($s->end_time, 0, 5);
                $kelas      = optional($s->classroom)->name ?? '-';
                $mapel      = optional($s->subject)->name ?? '-';

                $text .= "⏰ *{$jamMasuk} - {$jamSelesai} WIB*\n" .
                         " ├ 🏫 Kelas : *{$kelas}*\n" .
                         " └ 📚 Mapel : {$mapel}\n\n";
            }
        }

        $telegramService->sendMessage($chatId, $text);
    }

    /**
     * 2. Menampilkan Rekapitulasi Kelas Bimbingan Wali Kelas (/walas)
     */
    private function sendRekapWalas(string $chatId, $teacherId, TelegramService $telegramService): void
    {
        // Cari kelas yang Wali Kelasnya adalah guru ini (homeroom_teacher_id)
        $classrooms = Classroom::where('homeroom_teacher_id', $teacherId)->get();

        if ($classrooms->isEmpty()) {
            $telegramService->sendMessage($chatId, "⚠️ ID Guru Anda belum dihubungkan sebagai Wali Kelas di data Master Kelas.");
            return;
        }

        $today = Carbon::today()->format('Y-m-d');
        $text  = "🏫 *REKAP PRESENSI GERBANG (WALI KELAS)*\n" .
                 "📅 Tanggal : *" . date('d/m/Y') . "*\n" .
                 "======================================\n\n";

        foreach ($classrooms as $classroom) {
            $studentsCount = Student::where('classroom_id', $classroom->id)->count();

            $attendances = DB::table('daily_attendances')
                ->join('students', 'daily_attendances.student_id', '=', 'students.id')
                ->where('students.classroom_id', $classroom->id)
                ->whereDate('daily_attendances.date', $today)
                ->select('daily_attendances.status', DB::raw('count(*) as total'))
                ->groupBy('daily_attendances.status')
                ->pluck('total', 'status')
                ->toArray();

            $hadir     = $attendances['hadir'] ?? 0;
            $terlambat = $attendances['terlambat'] ?? 0;
            $izin      = $attendances['izin'] ?? 0;
            $sakit     = $attendances['sakit'] ?? 0;
            $alfa      = max(0, $studentsCount - ($hadir + $terlambat + $izin + $sakit));

            $text .= "📌 Kelas : *{$classroom->name}* ({$studentsCount} Siswa)\n" .
                     " ├ ✅ Hadir : `{$hadir}`\n" .
                     " ├ ⚠️ Terlambat : `{$terlambat}`\n" .
                     " ├ ℹ️ Izin : `{$izin}`\n" .
                     " ├ 🏥 Sakit : `{$sakit}`\n" .
                     " └ ❌ Belum Scan/Alfa : `{$alfa}`\n\n";
        }

        $text .= "--- *SISFO SMKN 1 Bukittinggi* ---";
        $telegramService->sendMessage($chatId, $text);
    }

    /**
     * 3. Menampilkan Rekapitulasi Konsentrasi Keahlian / Bengkel (/kabeng)
     */
    private function sendRekapKabeng(string $chatId, $teacherId, TelegramService $telegramService): void
    {
        // Cari Major (Konsentrasi Keahlian/Bengkel) yang diampu guru
        $major = Major::where('workshop_teacher_id', $teacherId)->first();

        if (!$major) {
            // Fallback: Ambil major pertama jika belum diisi spesifik
            $major = Major::first();
        }

        if (!$major) {
            $telegramService->sendMessage($chatId, "⚠️ Data Konsentrasi Keahlian / Bengkel belum dikonfigurasi.");
            return;
        }

        $today = Carbon::today()->format('Y-m-d');

        $rekapKelas = DB::table('daily_attendances')
            ->join('students', 'daily_attendances.student_id', '=', 'students.id')
            ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.major_id', $major->id)
            ->whereDate('daily_attendances.date', $today)
            ->select(
                'classrooms.name as nama_kelas',
                DB::raw('count(daily_attendances.arrival_time) as total_datang'),
                DB::raw('count(daily_attendances.departure_time) as total_pulang')
            )
            ->groupBy('classrooms.id', 'classrooms.name')
            ->get();

        $text = "🛠️ *REKAP PRESENSI KONSENTRASI KEAHLIAN*\n\n" .
                "🏭 Bengkel / Jurusan : *{$major->name}*\n" .
                "📅 Tanggal : *" . date('d/m/Y') . "*\n" .
                "======================================\n\n";

        if ($rekapKelas->isEmpty()) {
            $text .= "_Belum ada data kehadiran siswa di bengkel hari ini._\n";
        } else {
            foreach ($rekapKelas as $rk) {
                $text .= "🏫 *{$rk->nama_kelas}*\n" .
                         " ├ Hadir Datang : `{$rk->total_datang}` Siswa\n" .
                         " └ Sudah Pulang : `{$rk->total_pulang}` Siswa\n\n";
            }
        }

        $telegramService->sendMessage($chatId, $text);
    }

    /**
     * 4. Menampilkan Rekap Program Keahlian (/proka)
     */
    private function sendRekapProka(string $chatId, $teacherId, TelegramService $telegramService): void
    {
        // Cari Program Keahlian menggunakan relasi program_teacher_id sesuai Program.php
        $program = \App\Models\Program::where('program_teacher_id', $teacherId)->first();

        if (!$program) {
            $telegramService->sendMessage($chatId, "⚠️ ID Guru Anda belum dihubungkan sebagai Ketua Program Keahlian di data Program.");
            return;
        }

        $today = Carbon::today()->format('Y-m-d');

        // Ambil semua major di bawah program keahlian ini
        $majorIds = Major::where('program_id', $program->id)->pluck('id');

        $totalSiswa = Student::whereHas('classroom', function($q) use ($majorIds) {
            $q->whereIn('major_id', $majorIds);
        })->count();

        $totalHadir = DB::table('daily_attendances')
            ->join('students', 'daily_attendances.student_id', '=', 'students.id')
            ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->whereIn('classrooms.major_id', $majorIds)
            ->whereDate('daily_attendances.date', $today)
            ->count();

        $persentase = $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100, 1) : 0;

        $text = "📊 *REKAPITULASI PROGRAM KEAHLIAN*\n\n" .
                "🎯 Program : *{$program->name}* (`{$program->code}`)\n" .
                "📅 Tanggal : *" . date('d/m/Y') . "*\n" .
                "👥 Total Siswa Program : *{$totalSiswa} Siswa*\n" .
                "✅ Total Hadir Hari Ini : *{$totalHadir} ({$persentase}%)*\n";

        $telegramService->sendMessage($chatId, $text);
    }

    /**
     * 5. Menampilkan Rekap Guru BK (/bk)
     */
    private function sendRekapBK(string $chatId, $teacherId, TelegramService $telegramService): void
    {
        $today = Carbon::today()->format('Y-m-d');

        // Ambil kelas yang diampu oleh Guru BK ini (counseling_teacher_id)
        $classroomIds = Classroom::where('counseling_teacher_id', $teacherId)->pluck('id');

        $query = DB::table('daily_attendances')
            ->join('students', 'daily_attendances.student_id', '=', 'students.id')
            ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->whereDate('daily_attendances.date', $today)
            ->where('daily_attendances.status', 'terlambat');

        if ($classroomIds->isNotEmpty()) {
            $query->whereIn('classrooms.id', $classroomIds);
        }

        $lateStudents = $query->select('students.name', 'classrooms.name as nama_kelas', 'daily_attendances.arrival_time')
            ->orderBy('daily_attendances.arrival_time', 'asc')
            ->limit(15)
            ->get();

        $text = "📋 *DAFTAR SISWA TERLAMBAT HARI INI (GURU BK)*\n\n" .
                "📅 Tanggal : *" . date('d/m/Y') . "*\n" .
                "--------------------------------------\n";

        if ($lateStudents->isEmpty()) {
            $text .= "_\nTidak ada siswa terlambat pada kelas binaan Anda hari ini._\n";
        } else {
            foreach ($lateStudents as $ls) {
                $jam = substr($ls->arrival_time, 0, 5);
                $text .= "⚠️ *{$ls->name}* ({$ls->nama_kelas})\n" .
                         " └ Jam Datang: `{$jam}` WIB\n\n";
            }
        }

        $telegramService->sendMessage($chatId, $text);
    }

    // =========================================================================
    // METHOD LAINNYA (DITERUSKAN TANPA PERUBAHAN)
    // =========================================================================

    private function saveIzinToDatabase(Student $student, string $type, string $reason, ?string $filePath, ?string $phone, string $chatId, TelegramService $telegramService): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $existing = IzinSiswa::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            $statusText = $existing->status === 'approved' ? 'DISETUJUI' : 'PENDING VERIFIKASI';
            $telegramService->sendMessage($chatId, "⚠️ *Pengajuan Ditolak!* Siswa atas nama *{$student->name}* sudah memiliki pengajuan hari ini ({$today}) dengan status *{$statusText}*.", 'info', 'Sudah Ada Pengajuan');
            return;
        }

        $izin = IzinSiswa::create([
            'id'               => (string) Str::uuid(),
            'student_id'       => $student->id,
            'type'             => $type,
            'date'             => $today,
            'end_date'         => $today,
            'reason'           => $reason,
            'status'           => 'pending',
            'attachment_path'  => $filePath,
            'wa_number'        => $phone ?? $student->phone,
            'telegram_chat_id' => $chatId,
        ]);

        $className = optional($student->classroom)->name ?? '-';
        $jenisText = strtoupper($type);

        $replyMsg = "✅ *PENGAJUAN {$jenisText} BERHASIL DIKIRIM!*\n\n" .
                    "👤 Nama Siswa : *{$student->name}*\n" .
                    "🏫 Kelas : *{$className}*\n" .
                    "📅 Tanggal : *{$today}*\n" .
                    "📝 Alasan : _{$reason}_\n" .
                    "⏳ Status : *PENDING* (Menunggu Verifikasi Walas/BK/Proka/Kabeng/Admin)\n\n" .
                    "_Jika terdapat kesalahan data, silakan tekan tombol di bawah ini untuk membatalkan:_";

        $userKeyboard = [
            [
                ['text' => '❌ Batalkan Pengajuan Saya', 'callback_data' => "cancel_user_izin_{$izin->id}"]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $replyMsg, $userKeyboard);
        $this->notifyIzinToApprovers($izin, $student, $telegramService);
    }

    private function notifyIzinToApprovers(IzinSiswa $izin, Student $student, TelegramService $telegramService): void
    {
        $setting = AttendanceSetting::first();
        $classroomGroupChatId = optional($student->classroom)->telegram_chat_id;

        $approverChatId = !empty($classroomGroupChatId) 
            ? $classroomGroupChatId 
            : ($setting?->telegram_admin_chat_id ?? config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID')));

        if (empty($approverChatId)) return;

        $className = optional($student->classroom)->name ?? '-';
        $jenisText = strtoupper($izin->type ?? 'IZIN');

        $text = "📩 *PENGAJUAN " . $jenisText . " SISWA BARU*\n\n" .
                "👤 Nama Siswa : *{$student->name}*\n" .
                "🏫 Kelas : *{$className}*\n" .
                "📅 Tanggal : *{$izin->date}*\n" .
                "📝 Alasan : _{$izin->reason}_\n" .
                "⏳ Status : *PENDING VERIFIKASI*\n\n" .
                "Mohon Wali Kelas, BK, Proka, Kabeng, atau Admin untuk memverifikasi pengajuan ini:";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Setujui (Approve)', 'callback_data' => "appizin_{$izin->id}"],
                    ['text' => '❌ Tolak / Batalkan', 'callback_data' => "rejizin_{$izin->id}"]
                ]
            ]
        ];

        if (!empty($izin->attachment_path) && Storage::disk('public')->exists($izin->attachment_path)) {
            $token   = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
            $baseUrl = "https://api.telegram.org/bot{$token}/";
            $fullPath = Storage::disk('public')->path($izin->attachment_path);

            try {
                Http::attach(
                    'photo', file_get_contents($fullPath), basename($fullPath)
                )->post($baseUrl . 'sendPhoto', [
                    'chat_id'      => $approverChatId,
                    'caption'      => $text,
                    'parse_mode'   => 'Markdown',
                    'reply_markup' => json_encode($keyboard)
                ]);
                return;
            } catch (\Exception $e) {
                Log::error("Gagal kirim foto surat ke grup: " . $e->getMessage());
            }
        }

        $telegramService->sendMessageWithInlineKeyboard($approverChatId, $text, $keyboard['inline_keyboard']);
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

    private function downloadTelegramFile(string $fileId, string $nis): ?string
    {
        try {
            $token = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
            if (empty($token)) return null;
            
            $res = Http::get("https://api.telegram.org/bot{$token}/getFile?file_id={$fileId}");
            if (!$res->successful()) return null;

            $filePathOnTelegram = $res->json('result.file_path');
            $fileContent = Http::get("https://api.telegram.org/file/bot{$token}/{$filePathOnTelegram}")->body();

            $folder = 'surat_sakit/' . date('Y-m-d');
            $filename = "surat_telegram_{$nis}_" . time() . ".jpg";
            $fullPath = "{$folder}/{$filename}";

            Storage::disk('public')->put($fullPath, $fileContent);

            return $fullPath;
        } catch (\Exception $e) {
            Log::error("Download Telegram File Error: " . $e->getMessage());
            return null;
        }
    }

    private function getSubscriberStudent($chatId, TelegramService $telegramService)
    {
        $subscriber = TelegramSubscriber::where(function($q) use ($chatId) {
                $q->where('chat_id', (string)$chatId)
                  ->orWhere('chat_id', (int)$chatId);
            })
            ->where('approval_status', 'approved')
            ->where('is_active', true)
            ->first();

        if (!$subscriber || !$subscriber->student_id) {
            $telegramService->sendMessageWithRemoveKeyboard(
                $chatId, 
                "⚠️ *Akun Anda belum terverifikasi.*\n\nKetik `/start` untuk mendaftar dan menghubungkan akun dengan data siswa."
            );
            return null;
        }

        return Student::with('classroom')->find($subscriber->student_id);
    }

    private function sendRekapMenu($chatId, TelegramService $telegramService)
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $className = optional($student->classroom)->name ?? '-';

        $text = "📊 *MENU PILIHAN REKAP ABSENSI*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *{$className}*\n\n" .
                "Silakan pilih jenis rekapitulasi presensi yang ingin ditayangkan di bawah ini:";

        $keyboard = [
            [
                ['text' => '🚪 Presensi Gerbang', 'callback_data' => 'rekap_gerbang'],
                ['text' => '📚 Presensi Kelas', 'callback_data' => 'rekap_pembelajaran']
            ],
            [
                ['text' => '🕌 Presensi Sholat', 'callback_data' => 'rekap_sholat'],
                ['text' => '🌐 Semua Presensi', 'callback_data' => 'rekap_semua']
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function sendRekapGerbang($chatId, TelegramService $telegramService)
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $logs = DB::table('daily_attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        $text = "🚪 *REKAP PRESENSI GERBANG (7 HARI TERAKHIR)*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *" . (optional($student->classroom)->name ?? '-') . "*\n" .
                "--------------------------------------\n";

        if ($logs->isEmpty()) {
            $text .= "_\nBelum ada catatan presensi gerbang._\n\n";
        } else {
            foreach ($logs as $log) {
                $dateFormatted = date('d/m/Y', strtotime($log->date));
                $masuk  = $log->arrival_time ? substr($log->arrival_time, 0, 5) : '-';
                $pulang = $log->departure_time ? substr($log->departure_time, 0, 5) : '-';

                $rawStatus = strtolower(trim($log->status ?? ''));
                if (empty($rawStatus) || in_array($rawStatus, ['alfa', 'alpha', 'a', 'tanpa keterangan'])) {
                    $status = '❌ ALFA (Tanpa Keterangan)';
                } elseif (in_array($rawStatus, ['izin', 'i'])) {
                    $status = 'ℹ️ IZIN';
                } elseif (in_array($rawStatus, ['sakit', 's'])) {
                    $status = '🏥 SAKIT';
                } else {
                    $status = '✅ ' . strtoupper($rawStatus);
                }

                $text .= "📅 *{$dateFormatted}* [{$status}]\n" .
                         " ├ Datang : `{$masuk}` WIB\n" .
                         " └ Pulang : `{$pulang}` WIB\n\n";
            }
        }

        $text .= "--- *Listrik SMKN 1 Bukittinggi* ---";

        $setting   = AttendanceSetting::first();
        $groupLink = $setting?->telegram_admin_group_link 
            ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

        $keyboard = [
            [
                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function sendRekapPembelajaran($chatId, TelegramService $telegramService)
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $logs = DB::table('attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        $text = "📚 *REKAP PRESENSI PEMBELAJARAN (7 TERAKHIR)*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *" . (optional($student->classroom)->name ?? '-') . "*\n" .
                "--------------------------------------\n";

        if ($logs->isEmpty()) {
            $text .= "_\nBelum ada catatan presensi pembelajaran kelas._\n\n";
        } else {
            foreach ($logs as $log) {
                $dateFormatted = date('d/m/Y', strtotime($log->date));
                $masuk = $log->check_in_time ? substr($log->check_in_time, 0, 5) : '-';

                $rawStatus = strtolower(trim($log->status ?? ''));
                if (empty($rawStatus) || in_array($rawStatus, ['alfa', 'alpha', 'a', 'tanpa keterangan'])) {
                    $status = '❌ ALFA (Tanpa Keterangan)';
                } elseif (in_array($rawStatus, ['izin', 'i'])) {
                    $status = 'ℹ️ IZIN';
                } elseif (in_array($rawStatus, ['sakit', 's'])) {
                    $status = '🏥 SAKIT';
                } else {
                    $status = '✅ ' . strtoupper($rawStatus);
                }

                $text .= "📅 *{$dateFormatted}* | Jam: `{$masuk}` WIB\n" .
                         " └ Status: *{$status}*\n\n";
            }
        }

        $text .= "--- *Listrik SMKN 1 Bukittinggi* ---";

        $setting   = AttendanceSetting::first();
        $groupLink = $setting?->telegram_admin_group_link 
            ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

        $keyboard = [
            [
                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function sendRekapSholat($chatId, TelegramService $telegramService)
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $logs = DB::table('prayer_attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        $text = "🕌 *REKAP PRESENSI IBADAH SHOLAT (7 TERAKHIR)*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *" . (optional($student->classroom)->name ?? '-') . "*\n" .
                "--------------------------------------\n";

        if ($logs->isEmpty()) {
            $text .= "_\nBelum ada catatan presensi sholat._\n\n";
        } else {
            foreach ($logs as $log) {
                $dateFormatted = date('d/m/Y', strtotime($log->date));
                $sholat = ucfirst($log->prayer_name ?? 'Sholat');
                $masuk  = $log->check_in_time ? substr($log->check_in_time, 0, 5) : '-';

                $rawStatus = strtolower(trim($log->status ?? ''));
                if (empty($rawStatus) || in_array($rawStatus, ['alfa', 'alpha', 'a', 'tanpa keterangan'])) {
                    $status = '❌ ALFA (Tanpa Keterangan)';
                } elseif (in_array($rawStatus, ['izin', 'i'])) {
                    $status = 'ℹ️ IZIN';
                } elseif (in_array($rawStatus, ['sakit', 's'])) {
                    $status = '🏥 SAKIT';
                } else {
                    $status = '✅ ' . strtoupper($rawStatus);
                }

                $text .= "📅 *{$dateFormatted}* ({$sholat})\n" .
                         " ├ Waktu  : `{$masuk}` WIB\n" .
                         " └ Status : *{$status}*\n\n";
            }
        }

        $text .= "--- *Listrik SMKN 1 Bukittinggi* ---";

        $setting   = AttendanceSetting::first();
        $groupLink = $setting?->telegram_admin_group_link 
            ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

        $keyboard = [
            [
                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function sendRekapSemua($chatId, TelegramService $telegramService)
    {
        $student = $this->getSubscriberStudent($chatId, $telegramService);
        if (!$student) return;

        $className = optional($student->classroom)->name ?? '-';

        $text = "🌐 *REKAP LENGKAP SEMUA PRESENSI (7 HARI TERAKHIR)*\n\n" .
                "👤 Siswa: *{$student->name}*\n" .
                "🏫 Kelas: *{$className}*\n" .
                "======================================\n\n";

        // 1. GERBANG
        $text .= "🚪 *[ PRESENSI GERBANG ]*\n";
        $gerbang = DB::table('daily_attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')->limit(3)->get();

        if ($gerbang->isEmpty()) {
            $text .= "_Belum ada data gerbang._\n\n";
        } else {
            foreach ($gerbang as $g) {
                $tgl = date('d/m/Y', strtotime($g->date));
                $m = $g->arrival_time ? substr($g->arrival_time, 0, 5) : '-';
                $p = $g->departure_time ? substr($g->departure_time, 0, 5) : '-';
                $st = strtoupper($g->status ?? 'HADIR');
                $text .= "• {$tgl} [{$st}] - Datang: `{$m}`, Pulang: `{$p}`\n";
            }
            $text .= "\n";
        }

        // 2. PEMBELAJARAN
        $text .= "📚 *[ PRESENSI KELAS ]*\n";
        $pembelajaran = DB::table('attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')->limit(3)->get();

        if ($pembelajaran->isEmpty()) {
            $text .= "_Belum ada data kelas._\n\n";
        } else {
            foreach ($pembelajaran as $p) {
                $tgl = date('d/m/Y', strtotime($p->date));
                $m = $p->check_in_time ? substr($p->check_in_time, 0, 5) : '-';
                $st = strtoupper($p->status ?? 'HADIR');
                $text .= "• {$tgl} [{$st}] - Jam: `{$m}` WIB\n";
            }
            $text .= "\n";
        }

        // 3. SHOLAT
        $text .= "🕌 *[ PRESENSI SHOLAT ]*\n";
        $sholat = DB::table('prayer_attendances')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')->limit(3)->get();

        if ($sholat->isEmpty()) {
            $text .= "_Belum ada data sholat._\n\n";
        } else {
            foreach ($sholat as $s) {
                $tgl = date('d/m/Y', strtotime($s->date));
                $sh = ucfirst($s->prayer_name ?? 'Sholat');
                $m = $s->check_in_time ? substr($s->check_in_time, 0, 5) : '-';
                $st = strtoupper($s->status ?? 'HADIR');
                $text .= "• {$tgl} ({$sh}) [{$st}] - Jam: `{$m}` WIB\n";
            }
            $text .= "\n";
        }

        $text .= "--- *Listrik SMKN 1 Bukittinggi* ---";

        $setting   = AttendanceSetting::first();
        $groupLink = $setting?->telegram_admin_group_link 
            ?? config('services.telegram.admin_group_link', 'https://t.me/+p_yx7_McCC5jN2U1');

        $keyboard = [
            [
                ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($chatId, $text, $keyboard);
    }

    private function handleRekapCallback(array $callbackQuery, TelegramService $telegramService): void
    {
        $chatId = (string) $callbackQuery['message']['chat']['id'];
        $data   = $callbackQuery['data'];

        if ($data === 'rekap_gerbang') {
            $this->sendRekapGerbang($chatId, $telegramService);
        } elseif ($data === 'rekap_pembelajaran') {
            $this->sendRekapPembelajaran($chatId, $telegramService);
        } elseif ($data === 'rekap_sholat') {
            $this->sendRekapSholat($chatId, $telegramService);
        } elseif ($data === 'rekap_semua') {
            $this->sendRekapSemua($chatId, $telegramService);
        }
    }

    private function notifyAdminGroup(TelegramSubscriber $subscriber, Student $student, TelegramService $telegramService): void
    {
        $setting          = AttendanceSetting::first();
        $adminGroupChatId = $setting?->telegram_admin_chat_id 
            ?? config('services.telegram.admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID'));

        if (empty($adminGroupChatId)) return;

        $className = optional($student->classroom)->name ?? '-';
        $roleLabel = ($subscriber->user_type ?? 'parent') === 'student' ? 'Siswa' : 'Orang Tua';

        $text = "📩 *Pengajuan Pendaftaran Telegram*\n\n" .
                "👤 Pemohon: *{$subscriber->parent_name}* ({$roleLabel})\n" .
                "📞 No HP: `+{$subscriber->phone_number}`\n" .
                "🎯 Siswa: *{$student->name}* ({$className})\n" .
                "🆔 NISN: `{$student->nisn}`\n\n" .
                "Apakah Anda ingin menyetujui penghubungan akun ini?";

        $keyboard = [
            [
                ['text' => '✅ Setujui', 'callback_data' => "approve_{$subscriber->id}"],
                ['text' => '❌ Tolak', 'callback_data' => "reject_{$subscriber->id}"]
            ]
        ];

        $telegramService->sendMessageWithInlineKeyboard($adminGroupChatId, $text, $keyboard);
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

            $keyboard = [
                [
                    ['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]
                ]
            ];

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