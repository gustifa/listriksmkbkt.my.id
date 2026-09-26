<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Jobs\SendWhatsappJob; // Import Job yang sudah dibuat
use Carbon\Carbon;

class SendScheduleNotification extends Command
{
    /**
     * Nama dan signature command.
     * Jalankan di terminal: php artisan schedule:notify-teachers
     */
    protected $signature = 'schedule:notify-teachers';

    /**
     * Deskripsi command.
     */
    protected $description = 'Kirim notifikasi WA ke guru yang memiliki jadwal mengajar saat ini (via Queue).';

    /**
     * Eksekusi command.
     */
    public function handle()
    {
        // 1. Ambil Waktu Sekarang
        $now = Carbon::now();
        $currentDay = $now->translatedFormat('l'); // Senin, Selasa, dst (Sesuai locale ID)
        $currentTime = $now->format('H:i'); // 07:30

        $this->info("Memeriksa jadwal untuk: $currentDay, Jam: $currentTime");

        // 2. Cari Jadwal yang dimulai pada menit ini
        $schedules = Schedule::with(['teacher', 'classroom', 'subject'])
            ->where('day', $currentDay)
            // Mencocokkan jam mulai. Gunakan 'like' untuk mengabaikan detik (07:30:00)
            ->where('start_time', 'like', $currentTime . '%') 
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('Tidak ada jadwal yang dimulai saat ini.');
            return;
        }

        // 3. Loop dan Dispatch Job
        foreach ($schedules as $schedule) {
            $teacher = $schedule->teacher;

            // Validasi: Guru ada dan punya No HP
            if ($teacher && $teacher->phone) {
                
                $message = "*PENGINGAT JADWAL MENGAJAR*\n\n"
                    . "Halo *{$teacher->name}*,\n"
                    . "Jadwal mengajar Anda telah dimulai.\n\n"
                    . "📚 Mapel: *{$schedule->subject->name}*\n"
                    . "🏫 Kelas: *{$schedule->classroom->name}*\n"
                    . "⏰ Waktu: {$schedule->start_time} - {$schedule->end_time}\n\n"
                    . "Silakan menuju kelas dan melakukan absensi.\n"
                    . "Semangat mengajar! 💪\n\n"
                    . "- Sistem Absensi Sekolah -";

                $this->info("Menambahkan ke antrian WA: {$teacher->name} ({$teacher->phone})...");

                // Ganti Fonnte::send dengan Dispatch Job
                try {
                    SendWhatsappJob::dispatch($teacher->phone, $message);
                    $this->info("✅ Job dikirim ke antrian.");
                } catch (\Exception $e) {
                    $this->error("❌ Gagal dispatch job: " . $e->getMessage());
                }

            } else {
                $this->warn("⚠️ Guru untuk jadwal ID {$schedule->id} tidak memiliki Nomor HP.");
            }
        }

        $this->info("Selesai memproses jadwal.");
    }
}