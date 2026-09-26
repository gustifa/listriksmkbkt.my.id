<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Jobs\SendWhatsappJob; 
use Carbon\Carbon;

class SendScheduleNotification extends Command
{
    /**
     * Nama dan signature command.
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
        
        // FIX: Mapping Manual Hari Inggris ke Indonesia 
        // (Mengatasi masalah jika server menggunakan Bahasa Inggris)
        $daysMap = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        
        $englishDay = $now->format('l'); 
        $currentDay = $daysMap[$englishDay] ?? $englishDay; 
        
        $currentTime = $now->format('H:i'); // Contoh: 07:30

        // LOG DEBUGGING (Agar terlihat di terminal)
        $this->info("------------------------------------------------");
        $this->info("Waktu Server : " . $now->toDateTimeString());
        $this->info("Mencari Jadwal : Hari = $currentDay, Jam Mulai = $currentTime");
        $this->info("------------------------------------------------");

        // 2. Cari Jadwal yang dimulai pada menit ini
        $schedules = Schedule::with(['teacher', 'classroom', 'subject'])
            ->where('day', $currentDay)
            // Mencocokkan jam mulai (menggunakan LIKE agar detik 00 tercover)
            ->where('start_time', 'like', $currentTime . '%') 
            ->get();

        if ($schedules->isEmpty()) {
            $this->warn("❌ Tidak ada jadwal yang dimulai tepat pada jam $currentTime hari $currentDay.");
            return;
        }

        $this->info("✅ Ditemukan " . $schedules->count() . " jadwal. Memproses pengiriman...");

        // 3. Loop dan Dispatch Job
        foreach ($schedules as $schedule) {
            $teacher = $schedule->teacher;

            // KASUS 1: Data Guru Hilang (Relasi Null)
            if (!$teacher) {
                $this->error("   ❌ Data Inkosisten: Jadwal ID {$schedule->id} memiliki teacher_id '{$schedule->teacher_id}', tapi data Guru tidak ditemukan di database.");
                continue; // Lanjut ke jadwal berikutnya
            }

            // KASUS 2: Guru Ada tapi Tidak Punya No HP
            if (empty($teacher->phone)) {
                $this->warn("   ⚠ Gagal Kirim: Guru '{$teacher->name}' untuk mapel '{$schedule->subject->name}' tidak memiliki Nomor HP.");
                continue; // Lanjut ke jadwal berikutnya
            }

            // KASUS 3: Valid, Kirim Pesan
            $message = "*PENGINGAT JADWAL MENGAJAR*\n\n"
                . "Halo *{$teacher->name}*,\n"
                . "Jadwal mengajar Anda telah dimulai.\n\n"
                . "📚 Mapel: *{$schedule->subject->name}*\n"
                . "🏫 Kelas: *{$schedule->classroom->name}*\n"
                . "⏰ Waktu: {$schedule->start_time} - {$schedule->end_time}\n\n"
                . "Silakan menuju kelas dan melakukan absensi.\n"
                . "Semangat mengajar! 💪\n\n"
                . "- Sistem Absensi Sekolah -";

            $this->info("   ➜ Mengirim ke: {$teacher->name} ({$teacher->phone})");

            try {
                // Masukkan ke Antrian (Queue)
                SendWhatsappJob::dispatch($teacher->phone, $message);
                $this->info("      [OK] Masuk antrian.");
            } catch (\Exception $e) {
                $this->error("      [ERROR] Gagal dispatch: " . $e->getMessage());
            }
        }

        $this->info("------------------------------------------------");
        $this->info("Selesai. Pastikan 'php artisan queue:work' sedang berjalan.");
    }
}