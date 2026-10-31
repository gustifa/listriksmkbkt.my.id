<?php

namespace App\Services\Telegram;

use App\Models\Teacher;
use App\Models\Student;
use App\Models\TelegramSubscriber;
use App\Services\TelegramService;

class TelegramRegistrationService
{
    public function handleContact(array $message, string $chatId, string $groupLink, TelegramService $telegramService)
    {
        $contact    = $message['contact'];
        $phone      = $contact['phone_number'];
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        $firstName  = $contact['first_name'] ?? '';
        $lastName   = $contact['last_name'] ?? '';
        $parentName = trim($firstName . ' ' . $lastName) ?: 'Pengguna';
        $username   = isset($message['from']['username']) ? (string)$message['from']['username'] : null;

        $lastDigits = strlen($cleanPhone) >= 9 ? substr($cleanPhone, -9) : $cleanPhone;

        // CEK 0: JIKA AKUN INI SUDAH TERDAFTAR SEBAGAI GURU SEBELUMNYA
        $existingSub = TelegramSubscriber::where('chat_id', $chatId)->first();
        if ($existingSub && $existingSub->user_type === 'teacher' && $existingSub->teacher_id) {
            $existingSub->update([
                'phone_number' => (string) $cleanPhone,
                'username'     => $username,
                'is_active'    => true,
            ]);

            $teacher = Teacher::find($existingSub->teacher_id);
            $teacherName = $teacher ? $teacher->name : $parentName;

            $reply = "👨‍🏫 *Selamat Datang Kembali, Bapak/Ibu Guru!*\n\n" .
                     "Akun Telegram Anda tetap terverifikasi sebagai Guru (*{$teacherName}*).\n\n" .
                     "📌 *Menu Perintah Guru & Tugas Tambahan:*\n" .
                     "🔹 `/jadwal` : Jadwal Mengajar Hari Ini\n" .
                     "🔹 `/walas` : Rekapitulasi Presensi Kelas Bimbingan\n" .
                     "🔹 `/kabeng` : Rekap Presensi Konsentrasi Keahlian / Bengkel\n" .
                     "🔹 `/proka` : Rekapitulasi Program Keahlian\n" .
                     "🔹 `/bk` : Daftar Siswa Terlambat Hari Ini (Guru BK)\n" .
                     "🔹 `/unsubscribe` : Berhenti Berlangganan Bot";

            $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
            $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
            return;
        }

        // A. CEK JIKA KONTAK TERDAFTAR SEBAGAI GURU
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

            $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
            $telegramService->sendMessageWithInlineKeyboard($chatId, $reply, $keyboard);
            return;
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
            $student->update(['telegram_chat_id' => $chatId]);

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
                    "🔹 `/unsubscribe` : Berhenti Berlangganan Bot";

            $keyboard = [[['text' => '👥 Gabung Grup Telegram Admin', 'url' => $groupLink]]];
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
    }
}