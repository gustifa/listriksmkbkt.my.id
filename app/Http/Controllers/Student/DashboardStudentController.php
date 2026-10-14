<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Exam;
use App\Models\InternshipTimeline;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\PrayerAttendance;

class DashboardStudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userAktif = $user->status;

        $student = Student::where('user_id', $user->id)->first();

        if (!$student || $userAktif != 1) {
            return view('siswa.siswa_dashboard.empty');
        }

        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $dayOfWeek = $now->locale('id')->dayName;

        // 1. Kehadiran Hari Ini (Absensi Gerbang/Harian)
        $todayAttendance = DailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        // 2. Jadwal Pelajaran Hari Ini & Penentuan Peringatan SweetAlert
        $todaySchedules = collect();
        $activeSchedule = null;
        $upcomingSchedule = null;

        if ($student->classroom_id) {
            $schedules = Schedule::with(['subject', 'teacher'])
                ->where('classroom_id', $student->classroom_id)
                ->where('day', $dayOfWeek)
                ->orderBy('start_time', 'asc')
                ->get();

            $todaySchedules = $schedules->map(function ($schedule) use ($student, $today, $currentTime, $todayAttendance, &$activeSchedule, &$upcomingSchedule) {
                $attendance = Attendance::where('student_id', $student->id)
                    ->where('schedule_id', $schedule->id)
                    ->whereDate('date', $today)
                    ->first();

                $schedule->is_active = ($currentTime >= $schedule->start_time && $currentTime <= $schedule->end_time);
                $schedule->student_attendance = $attendance;

                // Pop-Up Mapel Aktif HANYA dipicu jika siswa SUDAH absen gerbang (Status HADIR / TERLAMBAT)
                if ($todayAttendance && in_array($todayAttendance->status, ['hadir', 'terlambat'])) {
                    if ($schedule->is_active && !$attendance && !$activeSchedule) {
                        $activeSchedule = $schedule;
                    }
                }

                // Tangkap jadwal yang akan dimulai dalam 30 menit ke depan
                $startTimeCarbon = Carbon::parse($schedule->start_time);
                $diffInMinutes = Carbon::parse($currentTime)->diffInMinutes($startTimeCarbon, false);

                if ($currentTime < $schedule->start_time && $diffInMinutes >= 0 && $diffInMinutes <= 30 && !$upcomingSchedule) {
                    $upcomingSchedule = $schedule;
                }

                return $schedule;
            });
        }

        // 3. Set Flash Session SweetAlert
        if (!session()->has('swal_shown')) {
            session()->flash('swal_shown', true);

            // Kondisi A: Belum Absen Gerbang
            if (!$todayAttendance) {
                session()->flash('swal_alert', [
                    'type'    => 'no_gate_attendance',
                    'title'   => 'Belum Absen Gerbang!',
                    'message' => 'Kamu belum melakukan absensi kedatangan hari ini. Silakan lakukan absensi gerbang terlebih dahulu!',
                ]);
            }
            // Kondisi B: Sudah Absen Gerbang (Hadir/Terlambat), Cek Jadwal Mapel
            elseif (in_array($todayAttendance->status, ['hadir', 'terlambat'])) {
                if ($activeSchedule) {
                    session()->flash('swal_alert', [
                        'type'        => 'active',
                        'title'       => 'Pelajaran Sedang Berlangsung!',
                        'subject'     => $activeSchedule->subject->name ?? '-',
                        'teacher'     => $activeSchedule->teacher->name ?? '-',
                        'time'        => Carbon::parse($activeSchedule->start_time)->format('H:i') . ' - ' . Carbon::parse($activeSchedule->end_time)->format('H:i'),
                        'schedule_id' => $activeSchedule->id,
                    ]);
                } elseif ($upcomingSchedule) {
                    session()->flash('swal_alert', [
                        'type'    => 'upcoming',
                        'title'   => 'Pelajaran Segera Dimulai!',
                        'subject' => $upcomingSchedule->subject->name ?? '-',
                        'teacher' => $upcomingSchedule->teacher->name ?? '-',
                        'time'    => Carbon::parse($upcomingSchedule->start_time)->format('H:i') . ' - ' . Carbon::parse($upcomingSchedule->end_time)->format('H:i'),
                    ]);
                }
            }
        }

        // 4. Statistik Kehadiran Gerbang (Harian, Mingguan, Bulanan)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $gateAttendanceStats = [
            'harian'   => $todayAttendance,
            'mingguan' => [
                'hadir' => DailyAttendance::where('student_id', $student->id)->whereBetween('date', [$startOfWeek, $endOfWeek])->where('status', 'hadir')->count(),
                'izin'  => DailyAttendance::where('student_id', $student->id)->whereBetween('date', [$startOfWeek, $endOfWeek])->where('status', 'izin')->count(),
                'sakit' => DailyAttendance::where('student_id', $student->id)->whereBetween('date', [$startOfWeek, $endOfWeek])->where('status', 'sakit')->count(),
                'alpa'  => DailyAttendance::where('student_id', $student->id)->whereBetween('date', [$startOfWeek, $endOfWeek])->where('status', 'alpa')->count(),
            ],
            'bulanan'  => [
                'hadir' => DailyAttendance::where('student_id', $student->id)->whereMonth('date', $now->month)->whereYear('date', $now->year)->where('status', 'hadir')->count(),
                'izin'  => DailyAttendance::where('student_id', $student->id)->whereMonth('date', $now->month)->whereYear('date', $now->year)->where('status', 'izin')->count(),
                'sakit' => DailyAttendance::where('student_id', $student->id)->whereMonth('date', $now->month)->whereYear('date', $now->year)->where('status', 'sakit')->count(),
                'alpa'  => DailyAttendance::where('student_id', $student->id)->whereMonth('date', $now->month)->whereYear('date', $now->year)->where('status', 'alpa')->count(),
            ],
        ];

        // 5. Histori Pembelajaran / Presensi Mapel Terakhir
        $subjectAttendanceHistory = Attendance::with(['subject', 'schedule'])
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->take(5)
            ->get();

        $attendanceStats = $gateAttendanceStats['bulanan'];

        // 6. Ujian & Timeline
        $activeExams = [];
        if ($student->classroom_id) {
            $activeExams = Exam::where('is_active', 1)
                ->whereHas('classrooms', function ($query) use ($student) {
                    $query->where('classroom_id', $student->classroom_id);
                })
                ->get();
        }

        $timelines = InternshipTimeline::orderBy('start_date', 'asc')->get();

        // 5. Histori Sholat / Ibadah Terakhir berdasarkan tabel prayer_attendances
        $prayerHistory = PrayerAttendance::where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->take(5)
            ->get();

        return view('siswa.siswa_dashboard.index', compact(
            'student',
            'todayAttendance',
            'todaySchedules',
            'attendanceStats',
            'gateAttendanceStats',
            'subjectAttendanceHistory',
            'activeExams',
            'timelines',
            'prayerHistory' // <--- Variabel histori sholat
        ));
    }

    public function storeSubjectAttendance(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|uuid',
        ]);

        $student = Student::where('user_id', Auth::id())->firstOrFail();
        $now = Carbon::now();
        $today = $now->toDateString();

        // 1. VALIDASI ABSENSI GERBANG (HARIAN)
        $dailyAttendance = DailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if (!$dailyAttendance) {
            return redirect()->back()->with('error', 'Kamu belum mengisi absensi gerbang hari ini. Silakan lakukan absensi gerbang terlebih dahulu!');
        }

        if (!in_array($dailyAttendance->status, ['hadir', 'terlambat'])) {
            return redirect()->back()->with('error', 'Kamu tidak dapat mengisi presensi mapel karena status absensi gerbang hari ini adalah ' . strtoupper($dailyAttendance->status) . '.');
        }

        // 2. VALIDASI JAM PELAJARAN AKTIF
        $schedule = Schedule::findOrFail($request->schedule_id);
        $currentTime = $now->format('H:i:s');

        if ($currentTime < $schedule->start_time || $currentTime > $schedule->end_time) {
            return redirect()->back()->with('error', 'Jam pelajaran ini tidak sedang berlangsung!');
        }

        // 3. CEK APAKAH SUDAH ABSEN MAPEL INI
        $existing = Attendance::where('student_id', $student->id)
            ->where('schedule_id', $schedule->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return redirect()->back()->with('info', 'Kamu sudah melakukan presensi pada mata pelajaran ini.');
        }

        // 4. SIMPAN PRESENSI MAPEL
        Attendance::create([
            'student_id'    => $student->id,
            'schedule_id'   => $schedule->id,
            'subject_id'    => $schedule->subject_id,
            'date'          => $today,
            'check_in_time' => $currentTime,
            'status'        => 'hadir',
            'recorded_by'   => Auth::user()->name . ' (Siswa)',
        ]);

        return redirect()->back()->with('success', 'Presensi mata pelajaran berhasil dicatat!');
    }

    /**
     * Simpan Pengajuan Sakit / Izin dari Siswa
     */
    public function storeLeavePermit(Request $request)
    {
        $request->validate([
            'status'              => 'required|in:sakit,izin',
            'reason'              => 'required|string|max:500',
            'medical_certificate' => 'required_if:status,sakit|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'status.required'                 => 'Jenis pengajuan wajib dipilih.',
            'reason.required'                 => 'Alasan/keterangan wajib diisi.',
            'medical_certificate.required_if' => 'Surat keterangan sakit wajib diunggah!',
            'medical_certificate.mimes'       => 'Format surat harus berupa PDF, JPG, JPEG, atau PNG.',
            'medical_certificate.max'         => 'Ukuran file maksimal 2MB.',
        ]);

        $student = Student::where('user_id', Auth::id())->firstOrFail();
        $today = Carbon::today()->toDateString();

        // Cek jika siswa sudah ada entri absensi harian
        $existing = DailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Kamu sudah mencatatkan absensi/pengajuan untuk hari ini!');
        }

        // Handle upload surat sakit jika memilih 'sakit'
        $filePath = null;
        if ($request->hasFile('medical_certificate')) {
            $file = $request->file('medical_certificate');
            $filename = 'surat_sakit_' . ($student->nis ?? $student->id) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('surat_sakit', $filename, 'public');
        }

        // Catat pengajuan izin/sakit ke tabel DailyAttendance
        DailyAttendance::create([
            'student_id'  => $student->id,
            'date'        => $today,
            'status'      => $request->status,
            'notes'       => $request->reason,
            'attachment'  => $filePath,
            'recorded_by' => Auth::user()->name . ' (Siswa)',
        ]);

        return redirect()->back()->with('success', 'Pengajuan ' . ucfirst($request->status) . ' berhasil dikirim!');
    }
}