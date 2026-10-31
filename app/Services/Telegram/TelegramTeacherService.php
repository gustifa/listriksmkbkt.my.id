<?php

namespace App\Services\Telegram;

use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Student;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TelegramTeacherService
{
    public function sendJadwalGuru(string $chatId, $teacherId, TelegramService $telegramService): void
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

    public function sendRekapWalas(string $chatId, $teacherId, TelegramService $telegramService): void
    {
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

    public function sendRekapKabeng(string $chatId, $teacherId, TelegramService $telegramService): void
    {
        $major = Major::where('workshop_teacher_id', $teacherId)->first() ?? Major::first();

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

    public function sendRekapProka(string $chatId, $teacherId, TelegramService $telegramService): void
    {
        $program = Program::where('program_teacher_id', $teacherId)->first();

        if (!$program) {
            $telegramService->sendMessage($chatId, "⚠️ ID Guru Anda belum dihubungkan sebagai Ketua Program Keahlian di data Program.");
            return;
        }

        $today = Carbon::today()->format('Y-m-d');
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

    public function sendRekapBK(string $chatId, $teacherId, TelegramService $telegramService): void
    {
        $today = Carbon::today()->format('Y-m-d');
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
}