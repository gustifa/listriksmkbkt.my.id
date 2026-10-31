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
use App\Models\Holiday; // <-- Import Model Holiday
use App\Services\TelegramService;
use App\Models\TelegramSubscriber;
use App\Models\IzinSiswa;
use Illuminate\Support\Str;


class DashboardStudentController extends Controller
{
    /**
     * Helper Private untuk Pengecekan Hari Libur (Minggu / Kalender Merah Admin)
     */
    private function isHoliday($date = null)
    {
        $now = $date ? Carbon::parse($date)->timezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');
        $today = $now->format('Y-m-d');

        // 1. Cek Hari Minggu
        if ($now->isSunday()) {
            return [
                'is_holiday' => true,
                'reason'     => 'Hari Minggu adalah hari libur akhir pekan.'
            ];
        }

        // 2. Cek Kalender Merah / Libur Sekolah dari DB (PostgreSQL Compatible)
        $dbHoliday = Holiday::where(function($q) {
                                $q->where('is_active', true)
                                  ->orWhere('is_active', 1)
                                  ->orWhere('is_active', 't');
                            })
                            ->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today)
                            ->first();

        if ($dbHoliday) {
            return [
                'is_holiday' => true,
                'reason'     => "Hari ini libur sekolah: {$dbHoliday->title}"
            ];
        }

        return [
            'is_holiday' => false,
            'reason'     => ''
        ];
    }

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

        // Cek Pengecekan Libur
        $holidayCheck = $this->isHoliday($today);

        // 1. Kehadiran Hari Ini (Absensi Gerbang/Harian)
        $todayAttendance = DailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        // 1. Kehadiran Hari Ini (Absensi Gerbang/Harian)
        $todayIzin = IzinSiswa::where('student_id', $student->id)
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

            $todaySchedules = $schedules->map(function ($schedule) use ($student, $today, $currentTime, $todayAttendance, $holidayCheck, &$activeSchedule, &$upcomingSchedule) {
                $attendance = Attendance::where('student_id', $student->id)
                    ->where('schedule_id', $schedule->id)
                    ->whereDate('date', $today)
                    ->first();

                // Hanya aktif jika sedang jam pelajaran DAN BUKAN HARI LIBUR
                $schedule->is_active = ($currentTime >= $schedule->start_time && $currentTime <= $schedule->end_time) && !$holidayCheck['is_holiday'];
                $schedule->student_attendance = $attendance;

                // Pop-Up Mapel Aktif HANYA dipicu jika tidak libur & siswa SUDAH absen gerbang
                if (!$holidayCheck['is_holiday'] && $todayAttendance && in_array($todayAttendance->status, ['hadir', 'terlambat'])) {
                    if ($schedule->is_active && !$attendance && !$activeSchedule) {
                        $activeSchedule = $schedule;
                    }
                }

                // Tangkap jadwal yang akan dimulai dalam 30 menit ke depan
                $startTimeCarbon = Carbon::parse($schedule->start_time);
                $diffInMinutes = Carbon::parse($currentTime)->diffInMinutes($startTimeCarbon, false);

                if (!$holidayCheck['is_holiday'] && $currentTime < $schedule->start_time && $diffInMinutes >= 0 && $diffInMinutes <= 30 && !$upcomingSchedule) {
                    $upcomingSchedule = $schedule;
                }

                return $schedule;
            });
        }

        // 3. Set Flash Session SweetAlert
        if (!session()->has('swal_shown')) {
            session()->flash('swal_shown', true);

            // Kondisi Khusus: Jika Hari Ini Libur
            if ($holidayCheck['is_holiday']) {
                session()->flash('swal_alert', [
                    'type'    => 'holiday',
                    'title'   => 'Hari Libur Sekolah!',
                    'message' => $holidayCheck['reason'],
                ]);
            }
            // Kondisi A: Belum Absen Gerbang
            elseif (!$todayAttendance) {
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

        // 7. Histori Sholat / Ibadah Terakhir berdasarkan tabel prayer_attendances
        $prayerHistory = PrayerAttendance::where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->take(5)
            ->get();

        // Ambil 5 histori absensi gerbang (DailyAttendance) terakhir
        $gateHistory = \App\Models\DailyAttendance::where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Tambahkan relasi/pengecekan telegram subscriber untuk siswa yang sedang login
        $telegramSubscriber = \App\Models\TelegramSubscriber::where('student_id', $student->id)
            ->where('user_type', 'student')
            ->where('is_active', true)
            ->first();

        return view('siswa.siswa_dashboard.index', compact(
            'student',
            'todayAttendance',
            'todaySchedules',
            'attendanceStats',
            'gateAttendanceStats',
            'subjectAttendanceHistory',
            'activeExams',
            'timelines',
            'prayerHistory',
            'gateHistory',
            'telegramSubscriber',
            'todayIzin'
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

        // --- VALIDASI HARI LIBUR (SABTU/MINGGU ATAU KALENDER MERAH) ---
        $holidayCheck = $this->isHoliday($today);
        if ($holidayCheck['is_holiday']) {
            return redirect()->back()->with('error', 'Gagal Presensi! ' . $holidayCheck['reason']);
        }

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
    // public function storeLeavePermit(Request $request)
    // {
    //     $request->validate([
    //         'status'              => 'required|in:sakit,izin',
    //         'reason'              => 'required|string|max:500',
    //         'medical_certificate' => 'required_if:status,sakit|file|mimes:pdf,jpg,jpeg,png|max:2048',
    //     ], [
    //         'status.required'                 => 'Jenis pengajuan wajib dipilih.',
    //         'reason.required'                 => 'Alasan/keterangan wajib diisi.',
    //         'medical_certificate.required_if' => 'Surat keterangan sakit wajib diunggah!',
    //         'medical_certificate.mimes'       => 'Format surat harus berupa PDF, JPG, JPEG, atau PNG.',
    //         'medical_certificate.max'         => 'Ukuran file maksimal 2MB.',
    //     ]);

    //     $student = Student::where('user_id', Auth::id())->firstOrFail();
    //     $today = Carbon::today()->toDateString();

    //     // --- VALIDASI HARI LIBUR ---
    //     $holidayCheck = $this->isHoliday($today);
    //     if ($holidayCheck['is_holiday']) {
    //         return redirect()->back()->with('error', 'Gagal Pengajuan! ' . $holidayCheck['reason']);
    //     }

    //     // Cek jika siswa sudah ada entri absensi harian
    //     $existing = DailyAttendance::where('student_id', $student->id)
    //         ->whereDate('date', $today)
    //         ->first();

    //     if ($existing) {
    //         return redirect()->back()->with('error', 'Kamu sudah mencatatkan absensi/pengajuan untuk hari ini!');
    //     }

    //     // Handle upload surat sakit jika memilih 'sakit'
    //     $filePath = null;
    //     if ($request->hasFile('medical_certificate')) {
    //         $file = $request->file('medical_certificate');
    //         $filename = 'surat_sakit_' . ($student->nis ?? $student->id) . '_' . time() . '.' . $file->getClientOriginalExtension();
    //         $filePath = $file->storeAs('surat_sakit', $filename, 'public');
    //     }

    //     // Catat pengajuan izin/sakit ke tabel DailyAttendance
    //     DailyAttendance::create([
    //         'student_id'  => $student->id,
    //         'date'        => $today,
    //         'status'      => $request->status,
    //         'notes'       => $request->reason,
    //         'attachment'  => $filePath,
    //         'recorded_by' => Auth::user()->name . ' (Siswa)',
    //     ]);

    //     return redirect()->back()->with('success', 'Pengajuan ' . ucfirst($request->status) . ' berhasil dikirim!');
    // }

    /**
     * Simpan Pengajuan Sakit / Izin dari Siswa (Mendukung Rentang Tanggal)
     */
    // public function storeLeavePermit(Request $request)
    // {
    //     $request->validate([
    //         'status'              => 'required|in:sakit,izin',
    //         'start_date'          => 'required|date',
    //         'end_date'            => 'required|date|after_or_equal:start_date',
    //         'reason'              => 'required|string|max:500',
    //         'medical_certificate' => 'required_if:status,sakit|file|mimes:pdf,jpg,jpeg,png|max:2048',
    //     ], [
    //         'status.required'                 => 'Jenis pengajuan wajib dipilih.',
    //         'start_date.required'             => 'Tanggal mulai wajib diisi.',
    //         'end_date.required'               => 'Tanggal selesai wajib diisi.',
    //         'end_date.after_or_equal'         => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.',
    //         'reason.required'                 => 'Alasan/keterangan wajib diisi.',
    //         'medical_certificate.required_if' => 'Surat keterangan sakit wajib diunggah!',
    //         'medical_certificate.mimes'       => 'Format surat harus berupa PDF, JPG, JPEG, atau PNG.',
    //         'medical_certificate.max'         => 'Ukuran file maksimal 2MB.',
    //     ]);

    //     $student = Student::where('user_id', Auth::id())->firstOrFail();

    //     // Upload berkas jika status 'sakit'
    //     $filePath = null;
    //     if ($request->hasFile('medical_certificate')) {
    //         $file = $request->file('medical_certificate');
    //         $filename = 'surat_sakit_' . ($student->nis ?? $student->id) . '_' . time() . '.' . $file->getClientOriginalExtension();
    //         $filePath = $file->storeAs('surat_sakit', $filename, 'public');
    //     }

    //     // Loop setiap tanggal dari start_date hingga end_date
    //     $startDate = Carbon::parse($request->start_date);
    //     $endDate   = Carbon::parse($request->end_date);

    //     $insertedCount = 0;
    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         $currentDateStr = $date->toDateString();

    //         // Lewati jika tanggal jatuh di Hari Minggu
    //         if ($date->isSunday()) {
    //             continue;
    //         }

    //         // Cek jika siswa sudah ada entri absensi harian di tanggal tersebut
    //         $existing = DailyAttendance::where('student_id', $student->id)
    //             ->whereDate('date', $currentDateStr)
    //             ->exists();

    //         if (!$existing) {
    //             DailyAttendance::create([
    //                 'student_id'  => $student->id,
    //                 'date'        => $currentDateStr,
    //                 'status'      => $request->status,
    //                 'notes'       => $request->reason,
    //                 'attachment'  => $filePath,
    //                 'recorded_by' => Auth::user()->name . ' (Siswa)',
    //             ]);
    //             $insertedCount++;
    //         }
    //     }

    //     if ($insertedCount === 0) {
    //         return redirect()->back()->with('error', 'Pengajuan gagal. Tanggal yang dipilih sudah memiliki catatan presensi/izin!');
    //     }

    //     return redirect()->back()->with('success', 'Pengajuan ' . ucfirst($request->status) . ' berhasil dikirim untuk ' . $insertedCount . ' hari!');
    // }

//     public function storeLeavePermit(Request $request)
// {
//     $request->validate([
//         'status'              => 'required|in:sakit,izin',
//         'start_date'          => 'required|date|after_or_equal:today', // Minimal hari ini, tidak bisa tanggal lampau
//         'end_date'            => 'required|date|after_or_equal:start_date',
//         'reason'              => 'required|string|max:500',
//         'medical_certificate' => 'required_if:status,sakit|file|mimes:pdf,jpg,jpeg,png|max:2048',
//     ], [
//         'status.required'                 => 'Jenis pengajuan wajib dipilih.',
//         'start_date.required'             => 'Tanggal mulai wajib diisi.',
//         'start_date.after_or_equal'       => 'Pengajuan tidak bisa dibuat untuk tanggal sebelum hari ini.',
//         'end_date.required'               => 'Tanggal selesai wajib diisi.',
//         'end_date.after_or_equal'         => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.',
//         'reason.required'                 => 'Alasan/keterangan wajib diisi.',
//         'medical_certificate.required_if' => 'Surat keterangan sakit wajib diunggah!',
//         'medical_certificate.mimes'       => 'Format surat harus berupa PDF, JPG, JPEG, atau PNG.',
//         'medical_certificate.max'         => 'Ukuran file maksimal 2MB.',
//     ]);

//     $student = Student::where('user_id', Auth::id())->firstOrFail();

//     // Upload berkas jika status 'sakit'
//     $filePath = null;
//     if ($request->hasFile('medical_certificate')) {
//         $file = $request->file('medical_certificate');
//         $filename = 'surat_sakit_' . ($student->nis ?? $student->id) . '_' . time() . '.' . $file->getClientOriginalExtension();
//         $filePath = $file->storeAs('surat_sakit', $filename, 'public');
//     }

//     // Loop tanggal dari start_date hingga end_date
//     $startDate = Carbon::parse($request->start_date);
//     $endDate   = Carbon::parse($request->end_date);

//     $insertedCount = 0;
//     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
//         $currentDateStr = $date->toDateString();

//         // Lewati jika tanggal jatuh di Hari Minggu
//         if ($date->isSunday()) {
//             continue;
//         }

//         // Cek jika siswa sudah memiliki catatan absensi/pengajuan di tanggal tersebut
//         $existing = DailyAttendance::where('student_id', $student->id)
//             ->whereDate('date', $currentDateStr)
//             ->exists();

//         if (!$existing) {
//             DailyAttendance::create([
//                 'student_id'  => $student->id,
//                 'date'        => $currentDateStr,
//                 'status'      => $request->status,
//                 'notes'       => $request->reason,
//                 'attachment'  => $filePath,
//                 'recorded_by' => Auth::user()->name . ' (Siswa)',
//             ]);
//             $insertedCount++;
//         }
//     }

//     if ($insertedCount === 0) {
//         return redirect()->back()->with('error', 'Pengajuan gagal. Tanggal yang dipilih sudah memiliki catatan presensi/izin!');
//     }

//     return redirect()->back()->with('success', 'Pengajuan ' . ucfirst($request->status) . ' berhasil dikirim untuk ' . $insertedCount . ' hari!');
// }

public function storeLeavePermit(Request $request)
{
    $request->validate([
        'status'              => 'required|in:sakit,izin',
        'start_date'          => 'required|date|after_or_equal:today',
        'end_date'            => 'required|date|after_or_equal:start_date',
        'reason'              => 'required|string|max:500',
        'medical_certificate' => 'required_if:status,sakit|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ], [
        'status.required'                 => 'Jenis pengajuan wajib dipilih.',
        'start_date.required'             => 'Tanggal mulai wajib diisi.',
        'start_date.after_or_equal'       => 'Pengajuan tidak bisa dibuat untuk tanggal sebelum hari ini.',
        'end_date.required'               => 'Tanggal selesai wajib diisi.',
        'end_date.after_or_equal'         => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.',
        'reason.required'                 => 'Alasan/keterangan wajib diisi.',
        'medical_certificate.required_if' => 'Surat keterangan sakit wajib diunggah!',
        'medical_certificate.mimes'       => 'Format surat harus berupa PDF, JPG, JPEG, atau PNG.',
        'medical_certificate.max'         => 'Ukuran file maksimal 2MB.',
    ]);

    $student = Student::where('user_id', Auth::id())->firstOrFail();

    // 1. Cek Duplikasi Pengajuan pada Rentang Tanggal yang Sama (Mencegah Double Submit)
    $existingLeave = IzinSiswa::where('student_id', $student->id)
        ->where(function ($query) use ($request) {
            $query->whereBetween('date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function ($q) use ($request) {
                      $q->where('date', '<=', $request->start_date)
                        ->where('end_date', '>=', $request->end_date);
                  });
        })
        ->exists();

    if ($existingLeave) {
        return redirect()->back()->with('error', 'Gagal mengajukan! Anda sudah memiliki pengajuan izin/sakit yang aktif di rentang tanggal tersebut.');
    }

    // 2. Upload Berkas Lampiran jika ada
    $filePath = null;
    if ($request->hasFile('medical_certificate')) {
        $file = $request->file('medical_certificate');
        $filename = 'surat_' . $request->status . '_' . ($student->nis ?? $student->id) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('surat_izin', $filename, 'public');
    }

    // 3. Simpan ke Tabel 'izin_siswas'
    IzinSiswa::create([
        'id'               => (string) Str::uuid(),
        'student_id'       => $student->id,
        'type'             => $request->status, // sakit / izin
        'date'             => $request->start_date,
        'end_date'         => $request->end_date,
        'reason'           => $request->reason,
        'status'           => 'pending', // Menunggu persetujuan Wali Kelas / BK
        'attachment_path'  => $filePath,
        'wa_number'        => $student->phone,
        'telegram_chat_id' => $student->telegram_chat_id,
    ]);

    $totalDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

    return redirect()->back()->with('success', 'Pengajuan ' . ucfirst($request->status) . ' berhasil dikirim untuk ' . $totalDays . ' hari! Silakan menunggu konfirmasi.');
}


/**
 * Membatalkan / Memutuskan Sambungan Telegram dari Akun Siswa
 */
// public function disconnectTelegram(TelegramService $telegramService)
// {
//     $student = Student::where('user_id', Auth::id())->firstOrFail();

//     if ($student->telegram_chat_id) {
//         $chatId = $student->telegram_chat_id;

//         // 1. Kosongkan telegram_chat_id di tabel students
//         $student->update(['telegram_chat_id' => null]);

//         // 2. Nonaktifkan subscriber siswa di tabel telegram_subscribers
//         TelegramSubscriber::where('student_id', $student->id)
//             ->where('user_type', 'student')
//             ->update([
//                 'is_active'       => false,
//                 'approval_status' => 'rejected',
//             ]);

//         // 3. Kirim notifikasi konfirmasi ke Telegram siswa
//         $pesan = "⚠️ *Akses Telegram Diputuskan*\n\n" .
//                  "Akun Telegram Anda telah dilepaskan dari portal SISFO melalui Dashboard Siswa.\n\n" .
//                  "Anda tidak akan lagi menerima notifikasi presensi harian. Ketik /start atau hubungkan ulang dari web jika ingin mendaftar kembali.";

//         try {
//             $telegramService->sendMessageWithRemoveKeyboard($chatId, $pesan);
//         } catch (\Exception $e) {
//             // Abaikan jika bot diblokir oleh pengguna
//         }

//         return redirect()->back()->with('success', 'Berhasil memutuskan sambungan dengan Bot Telegram!');
//     }

//     return redirect()->back()->with('info', 'Akun Anda belum terhubung dengan Telegram.');
// }

public function disconnectTelegram(TelegramService $telegramService)
{
    $student = Student::where('user_id', Auth::id())->firstOrFail();

    // 1. Ambil semua subscriber terkait siswa ini (baik berdasarkan chat_id maupun student_id)
    $subscribers = TelegramSubscriber::where('student_id', $student->id)
        ->orWhere(function ($query) use ($student) {
            if ($student->telegram_chat_id) {
                $query->where('chat_id', $student->telegram_chat_id);
            }
        })
        ->get();

    // 2. Kirim notifikasi ke Telegram sebelum sambungan diputus
    if ($student->telegram_chat_id) {
        $pesan = "⚠️ *Akses Telegram Diputuskan*\n\n" .
                 "Akun Telegram Anda telah dilepaskan dari portal SISFO melalui Dashboard Siswa.\n\n" .
                 "Anda tidak akan lagi menerima notifikasi presensi harian. Hubungkan ulang dari web jika ingin mendaftar kembali.";

        try {
            $telegramService->sendMessageWithRemoveKeyboard($student->telegram_chat_id, $pesan);
        } catch (\Exception $e) {
            // Abaikan jika bot diblokir oleh pengguna
        }
    }

    // 3. Hapus / Nonaktifkan record subscriber di tabel telegram_subscribers
    foreach ($subscribers as $sub) {
        $sub->update([
            'student_id'      => null,
            'is_active'       => false,
            'approval_status' => 'rejected',
        ]);
    }

    // 4. Kosongkan telegram_chat_id di tabel students
    $student->update([
        'telegram_chat_id' => null
    ]);

    return redirect()->back()->with('success', 'Berhasil memutuskan sambungan akun Telegram!');
}



}