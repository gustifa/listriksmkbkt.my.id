<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Schedule; // Import Schedule
use App\Models\Classroom; // <--- Import Model Classroom
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Teacher;
// ... Jangan lupa import Model Setting di paling atas
use App\Models\Setting;
use App\Models\TeachingJournal; // Import Model Jurnal
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        // Ambil data kelas untuk Dropdown Filter
        $classrooms = Classroom::orderBy('name')->get();
        // Ambil data siswa untuk Dropdown Pencarian Siswa
        $students = Student::with('classroom')->orderBy('name')->get();

        return view('report.index', compact('classrooms', 'students'));
    }

     /**
     * Proses Cetak Laporan Umum (Periode & Kelas)
     * Route: POST /report/print
     */

    // public function print(Request $request)
    // {
    //     $startDate = null;
    //     $endDate = null;
    //     $labelPeriode = "";

    //     // LOGIKA PENENTUAN TANGGAL
    //     switch ($request->periode) {
    //         case 'harian':
    //             $startDate = $request->tanggal;
    //             $endDate = $request->tanggal;
    //             $labelPeriode = "Harian (" . Carbon::parse($startDate)->translatedFormat('d F Y') . ")";
    //             break;

    //         case 'mingguan':
    //             $request->validate([
    //                 'start_date' => 'required|date',
    //                 'end_date'   => 'required|date|after_or_equal:start_date',
    //             ]);
    //             $startDate = $request->start_date;
    //             $endDate = $request->end_date;
    //             $labelPeriode = "Mingguan (" . Carbon::parse($startDate)->format('d/m') . " - " . Carbon::parse($endDate)->format('d/m/Y') . ")";
    //             break;

    //         case 'bulanan':
    //             $month = $request->bulan;
    //             $year = $request->tahun_bulan;

    //             $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
    //             $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

    //             $labelPeriode = "Bulan " . Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
    //             break;

    //         case 'semester':
    //             $year = $request->tahun_semester;
    //             if ($request->semester == 'ganjil') {
    //                 // Ganjil: Juli Tahun Ini - Desember Tahun Ini
    //                 $startDate = $year . '-07-01';
    //                 $endDate   = $year . '-12-31';
    //                 $labelPeriode = "Semester Ganjil T.A $year/" . ($year+1);
    //             } else {
    //                 // Genap: Januari Tahun Depan - Juni Tahun Depan
    //                 $startDate = ($year + 1) . '-01-01';
    //                 $endDate   = ($year + 1) . '-06-30';
    //                 $labelPeriode = "Semester Genap T.A $year/" . ($year+1);
    //             }
    //             break;
    //     }


    //     // 2. AMBIL DATA SEKOLAH (KOP SURAT)
    //     $school = $this->getSchoolData();

    //      // 3. QUERY DATA ABSENSI (DENGAN FILTER KELAS)
    //     $query = Attendance::with(['student', 'schedule'])
    //                     ->whereBetween('date', [$startDate, $endDate])
    //                     ->orderBy('date', 'asc')
    //                     ->orderBy('check_in_time', 'asc');

    //     $labelTambahan = null;

    //     // --- TAMBAHAN: Filter Per Kelas ---
    //     if ($request->filled('classroom_id')) {
    //         // Filter hanya siswa yang berada di kelas yang dipilih
    //         $query->whereHas('student', function($q) use ($request) {
    //             $q->where('classroom_id', $request->classroom_id);
    //         });

    //         // Ambil nama kelas untuk judul PDF
    //         $kelas = Classroom::find($request->classroom_id);
    //         if ($kelas) {
    //             $labelTambahan = "Kelas: " . $kelas->name;
    //         }
    //     }
    //     // ----------------------------------

    //     $attendances = $query->get();

    //     // GENERATE PDF
    //     $pdf = Pdf::loadView('report.pdf_view_admin', compact(
    //         'attendances',
    //         'labelPeriode',
    //         'startDate',
    //         'endDate',
    //         'school'));
    //     $pdf->setPaper($school['paper_size'], $school['paper_orientation']);

    //     return $pdf->stream('Laporan-Absensi.pdf');


    // }

    // public function print(Request $request)
    // {
    //     // 1. VALIDASI INPUT (PENTING)
    //     // $request->validate([
    //     //     'periode'      => 'required|in:harian,mingguan,bulanan,semester',
    //     //     'classroom_id' => 'nullable|exists:classrooms,id',
    //     //     // Validasi bersyarat
    //     //     'tanggal'        => 'required_if:periode,harian',
    //     //     'start_date'     => 'required_if:periode,mingguan|date',
    //     //     'end_date'       => 'required_if:periode,mingguan|date|after_or_equal:start_date',
    //     //     'bulan'          => 'required_if:periode,bulanan',
    //     //     'tahun_bulan'    => 'required_if:periode,bulanan',
    //     //     'semester'       => 'required_if:periode,semester',
    //     //     'tahun_semester' => 'required_if:periode,semester',
    //     // ]);

    //     $startDate = null;
    //     $endDate = null;
    //     $labelPeriode = "";

    //     // LOGIKA TANGGAL (Sama seperti sebelumnya, tapi lebih aman karena sudah divalidasi)
    //     switch ($request->periode) {
    //         case 'harian':
    //             $startDate = $request->tanggal;
    //             $endDate = $request->tanggal;
    //             $labelPeriode = "Harian (" . Carbon::parse($startDate)->translatedFormat('d F Y') . ")";
    //             break;
    //         case 'mingguan':
    //             $startDate = $request->start_date;
    //             $endDate = $request->end_date;
    //             $labelPeriode = "Mingguan (" . Carbon::parse($startDate)->format('d/m') . " - " . Carbon::parse($endDate)->format('d/m/Y') . ")";
    //             break;
    //         case 'bulanan':
    //             $startDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
    //             $endDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
    //             $labelPeriode = "Bulan " . Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->translatedFormat('F Y');
    //             break;
    //         case 'semester':
    //             if ($request->semester == 'ganjil') {
    //                 $startDate = $request->tahun_semester . '-07-01';
    //                 $endDate   = $request->tahun_semester . '-12-31';
    //                 $labelPeriode = "Semester Ganjil T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
    //             } else {
    //                 $startDate = ($request->tahun_semester + 1) . '-01-01';
    //                 $endDate   = ($request->tahun_semester + 1) . '-06-30';
    //                 $labelPeriode = "Semester Genap T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
    //             }
    //             break;
    //     }

    //     $school = $this->getSchoolData();

    //     $query = Attendance::with(['student', 'schedule.subject', 'classroom']) // Eager load diperbaiki
    //         ->whereBetween('date', [$startDate, $endDate])
    //         ->orderBy('date', 'asc')
    //         ->orderBy('check_in_time', 'asc');

    //     $labelTambahan = null;

    //     if ($request->filled('classroom_id')) {
    //         $query->whereHas('student', function ($q) use ($request) {
    //             $q->where('classroom_id', $request->classroom_id);
    //         });
    //         $kelas = Classroom::find($request->classroom_id);
    //         if ($kelas) {
    //             $labelTambahan = "Kelas: " . $kelas->name;
    //         }
    //     }

    //     $attendances = $query->get();

    //     // ==========================================
    //     //  TAMBAHKAN VALIDASI INI
    //     // ==========================================
    //     if ($attendances->isEmpty()) {
    //         return redirect()->back()->with('error', 'Data absensi tidak ditemukan pada periode/filter yang dipilih.');
    //     }
    //     // ==========================================

    //     // LOGIKA TANDA TANGAN UNTUK LAPORAN UMUM
    //     // Jika admin mencetak laporan umum, biasanya TTD Kepala Sekolah ($isTeacher = false)
    //     $isTeacher = false;

    //     $pdf = Pdf::loadView('report.pdf_view_admin', compact(
    //         'attendances', 'labelPeriode', 'labelTambahan', 'startDate', 'endDate', 'school', 'isTeacher'
    //     ));

    //     $pdf->setPaper($school['paper_size'], $school['paper_orientation']);
    //     return $pdf->stream('Laporan-Absensi.pdf');
    // }

    public function print(Request $request)
    // {
    //     // 1. VALIDASI INPUT (PENTING)
    //     // $request->validate([
    //     //     'periode'      => 'required|in:harian,mingguan,bulanan,semester',
    //     //     'classroom_id' => 'nullable|exists:classrooms,id',
    //     //     // Validasi bersyarat
    //     //     'tanggal'        => 'required_if:periode,harian',
    //     //     'start_date'     => 'required_if:periode,mingguan|date',
    //     //     'end_date'       => 'required_if:periode,mingguan|date|after_or_equal:start_date',
    //     //     'bulan'          => 'required_if:periode,bulanan',
    //     //     'tahun_bulan'    => 'required_if:periode,bulanan',
    //     //     'semester'       => 'required_if:periode,semester',
    //     //     'tahun_semester' => 'required_if:periode,semester',
    //     // ]);

    //     $startDate = null;
    //     $endDate = null;
    //     $labelPeriode = "";

    //     // LOGIKA TANGGAL (Sama seperti sebelumnya, tapi lebih aman karena sudah divalidasi)
    //     switch ($request->periode) {
    //         case 'harian':
    //             $startDate = $request->tanggal;
    //             $endDate = $request->tanggal;
    //             $labelPeriode = "Harian (" . Carbon::parse($startDate)->translatedFormat('d F Y') . ")";
    //             break;
    //         case 'mingguan':
    //             $startDate = $request->start_date;
    //             $endDate = $request->end_date;
    //             $labelPeriode = "Mingguan (" . Carbon::parse($startDate)->format('d/m') . " - " . Carbon::parse($endDate)->format('d/m/Y') . ")";
    //             break;
    //         case 'bulanan':
    //             $startDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
    //             $endDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
    //             $labelPeriode = "Bulan " . Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->translatedFormat('F Y');
    //             break;
    //         case 'semester':
    //             if ($request->semester == 'ganjil') {
    //                 $startDate = $request->tahun_semester . '-07-01';
    //                 $endDate   = $request->tahun_semester . '-12-31';
    //                 $labelPeriode = "Semester Ganjil T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
    //             } else {
    //                 $startDate = ($request->tahun_semester + 1) . '-01-01';
    //                 $endDate   = ($request->tahun_semester + 1) . '-06-30';
    //                 $labelPeriode = "Semester Genap T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
    //             }
    //             break;
    //     }

    //     $school = $this->getSchoolData();

    //     $query = Attendance::with(['student', 'schedule.subject', 'classroom']) // Eager load diperbaiki
    //         ->whereBetween('date', [$startDate, $endDate])
    //         ->orderBy('date', 'asc')
    //         ->orderBy('check_in_time', 'asc');

    //     $labelTambahan = null;

    //     if ($request->filled('classroom_id')) {
    //         $query->whereHas('student', function ($q) use ($request) {
    //             $q->where('classroom_id', $request->classroom_id);
    //         });
    //         $kelas = Classroom::find($request->classroom_id);
    //         if ($kelas) {
    //             $labelTambahan = "Kelas: " . $kelas->name;
    //         }
    //     }

    //     $attendances = $query->get();

    //     // ==========================================
    //     //  TAMBAHKAN VALIDASI INI
    //     // ==========================================
    //     if ($attendances->isEmpty()) {
    //         return redirect()->back()->with('error', 'Data absensi tidak ditemukan pada periode/filter yang dipilih.');
    //     }
    //     // ==========================================

    //     // --- FITUR JURNAL (BARU) ---
    //     $journal = null;
    //     if ($attendances->isNotEmpty()) {
    //         // Ambil daftar ID Jadwal dari data absensi yang ditemukan
    //         $scheduleIds = $attendances->pluck('schedule_id')->unique()->filter();

    //         if ($scheduleIds->isNotEmpty()) {
    //             // Ambil jurnal yang terkait dengan jadwal tersebut pada rentang tanggal ini
    //             $journal = TeachingJournal::whereIn('schedule_id', $scheduleIds)
    //                         ->whereBetween('created_at', [
    //                             Carbon::parse($startDate)->startOfDay(),
    //                             Carbon::parse($endDate)->endOfDay()
    //                         ])
    //                         ->latest() // Ambil yang paling baru jika ada lebih dari satu
    //                         ->first();
    //         }
    //     }

    //     // LOGIKA TANDA TANGAN UNTUK LAPORAN UMUM
    //     // Jika admin mencetak laporan umum, biasanya TTD Kepala Sekolah ($isTeacher = false)
    //     $isTeacher = false;

    //     $pdf = Pdf::loadView('report.pdf_view_admin', compact(
    //         'attendances', 'labelPeriode', 'labelTambahan', 'startDate', 'endDate', 'school', 'isTeacher', 'journal'
    //     ));

    //     $pdf->setPaper($school['paper_size'], $school['paper_orientation']);
    //     return $pdf->stream('Laporan-Absensi.pdf');
    // }

    // {
    //     $startDate = null;
    //     $endDate = null;
    //     $labelPeriode = "";

    //     // 1. TENTUKAN PERIODE
    //     switch ($request->periode) {
    //         case 'harian':
    //             $startDate = $request->tanggal;
    //             $endDate = $request->tanggal;
    //             $labelPeriode = "Harian (" . Carbon::parse($startDate)->translatedFormat('d F Y') . ")";
    //             break;
    //         case 'mingguan':
    //             $startDate = $request->start_date;
    //             $endDate = $request->end_date;
    //             $labelPeriode = "Mingguan (" . Carbon::parse($startDate)->format('d/m') . " - " . Carbon::parse($endDate)->format('d/m/Y') . ")";
    //             break;
    //         case 'bulanan':
    //             $startDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
    //             $endDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
    //             $labelPeriode = "Bulan " . Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->translatedFormat('F Y');
    //             break;
    //         case 'semester':
    //             if ($request->semester == 'ganjil') {
    //                 $startDate = $request->tahun_semester . '-07-01';
    //                 $endDate   = $request->tahun_semester . '-12-31';
    //                 $labelPeriode = "Semester Ganjil T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
    //             } else {
    //                 $startDate = ($request->tahun_semester + 1) . '-01-01';
    //                 $endDate   = ($request->tahun_semester + 1) . '-06-30';
    //                 $labelPeriode = "Semester Genap T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
    //             }
    //             break;
    //     }

    //     $school = $this->getSchoolData();

    //     // 2. QUERY ABSENSI
    //     $query = Attendance::with(['student', 'schedule.subject', 'classroom'])
    //         ->whereBetween('date', [$startDate, $endDate])
    //         ->orderBy('date', 'asc')
    //         ->orderBy('check_in_time', 'asc');

    //     $labelTambahan = null;
    //     if ($request->filled('classroom_id')) {
    //         $query->whereHas('student', function ($q) use ($request) {
    //             $q->where('classroom_id', $request->classroom_id);
    //         });
    //         $kelas = Classroom::find($request->classroom_id);
    //         if ($kelas) $labelTambahan = "Kelas: " . $kelas->name;
    //     }

    //     $attendances = $query->get();

    //     if ($attendances->isEmpty()) {
    //         return redirect()->back()->with('error', 'Data absensi tidak ditemukan pada periode/filter yang dipilih.');
    //     }

    //     // 3. QUERY JURNAL PEMBELAJARAN (FIXED)
    //     $journals = collect(); // Default collection kosong
    //     $journal = null;       // Default null untuk backward compatibility view lama

    //     if ($attendances->isNotEmpty()) {
    //         $scheduleIds = $attendances->pluck('schedule_id')->unique()->filter();

    //         if ($scheduleIds->isNotEmpty()) {
    //             // Ambil jurnal berdasarkan jadwal dan TANGGAL yang sesuai
    //             $journals = TeachingJournal::with(['schedule.subject', 'schedule.classroom'])
    //                         ->whereIn('schedule_id', $scheduleIds)
    //                         ->whereBetween('date', [$startDate, $endDate]) // Filter by DATE, bukan created_at
    //                         ->orderBy('date', 'asc')
    //                         ->get();

    //             // Ambil satu jurnal untuk view harian (opsional)
    //             $journal = $journals->first();
    //         }
    //     }

    //     // 4. CEK USER LOGIN (GURU / ADMIN)
    //     $user = Auth::user();
    //     $isTeacher = false;

    //     if ($user->teacher) {
    //         $isTeacher = true;
    //     }

    //     // 5. LOAD VIEW PDF
    //     $pdf = Pdf::loadView('report.pdf_view_admin', compact(
    //         'attendances',
    //         'journals', // Kirim collection jurnal (untuk loop)
    //         'journal',  // Kirim single jurnal (untuk view lama)
    //         'labelPeriode', 'labelTambahan',
    //         'startDate', 'endDate', 'school', 'isTeacher', 'user'
    //     ));

    //     $pdf->setPaper($school['paper_size'], $school['paper_orientation']);
    //     $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

    //     return $pdf->stream('Laporan-Absensi-Jurnal.pdf');
    // }

    // {
    //     $startDate = null;
    //     $endDate = null;
    //     $labelPeriode = "";

    //     // 1. TENTUKAN PERIODE
    //     switch ($request->periode) {
    //         case 'harian':
    //             $startDate = $request->tanggal;
    //             $endDate = $request->tanggal;
    //             $labelPeriode = "Harian (" . Carbon::parse($startDate)->translatedFormat('d F Y') . ")";
    //             break;
    //         case 'mingguan':
    //             $startDate = $request->start_date;
    //             $endDate = $request->end_date;
    //             $labelPeriode = "Mingguan (" . Carbon::parse($startDate)->format('d/m') . " - " . Carbon::parse($endDate)->format('d/m/Y') . ")";
    //             break;
    //         case 'bulanan':
    //             $startDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
    //             $endDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
    //             $labelPeriode = "Bulan " . Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->translatedFormat('F Y');
    //             break;
    //         case 'semester':
    //             if ($request->semester == 'ganjil') {
    //                 $startDate = $request->tahun_semester . '-07-01';
    //                 $endDate   = $request->tahun_semester . '-12-31';
    //                 $labelPeriode = "Semester Ganjil T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
    //             } else {
    //                 $startDate = ($request->tahun_semester + 1) . '-01-01';
    //                 $endDate   = ($request->tahun_semester + 1) . '-06-30';
    //                 $labelPeriode = "Semester Genap T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
    //             }
    //             break;
    //     }

    //     $school = $this->getSchoolData();

    //     // 2. QUERY ABSENSI
    //     $query = Attendance::with(['student', 'schedule.subject', 'classroom'])
    //         ->whereBetween('date', [$startDate, $endDate])
    //         ->orderBy('date', 'asc')
    //         ->orderBy('check_in_time', 'asc');

    //     $labelTambahan = null;
    //     if ($request->filled('classroom_id')) {
    //         $query->whereHas('student', function ($q) use ($request) {
    //             $q->where('classroom_id', $request->classroom_id);
    //         });
    //         $kelas = Classroom::find($request->classroom_id);
    //         if ($kelas) $labelTambahan = "Kelas: " . $kelas->name;
    //     }

    //     $attendances = $query->get();

    //     if ($attendances->isEmpty()) {
    //         return redirect()->back()->with('error', 'Data absensi tidak ditemukan pada periode/filter yang dipilih.');
    //     }

    //     // 3. QUERY JURNAL PEMBELAJARAN (FIXED)
    //     // Ambil semua jurnal yang sesuai dengan jadwal dan rentang tanggal yang dipilih
    //     $journals = collect(); // Default collection kosong

    //     if ($attendances->isNotEmpty()) {
    //         // Ambil daftar ID Jadwal dari data absensi yang ditemukan
    //         $scheduleIds = $attendances->pluck('schedule_id')->unique()->filter();

    //         if ($scheduleIds->isNotEmpty()) {
    //             // Ambil jurnal berdasarkan jadwal dan RENTANG TANGGAL yang sesuai
    //             // Menggunakan whereBetween pada kolom 'date' jurnal agar sesuai periode laporan
    //             $journals = TeachingJournal::with(['schedule.subject', 'schedule.classroom'])
    //                         ->whereIn('schedule_id', $scheduleIds)
    //                         ->whereBetween('date', [$startDate, $endDate]) // Filter by DATE range
    //                         ->orderBy('date', 'asc')
    //                         ->get();
    //         }
    //     }

    //     // Ambil satu jurnal untuk view harian (opsional, untuk kompatibilitas jika view lama pakai $journal)
    //     $journal = $journals->first();

    //     // 4. CEK USER LOGIN (GURU / ADMIN)
    //     $user = Auth::user();
    //     $isTeacher = false;

    //     if ($user->teacher) {
    //         $isTeacher = true;
    //     }

    //     // 5. LOAD VIEW PDF
    //     $pdf = Pdf::loadView('report.pdf_view_admin', compact(
    //         'attendances',
    //         'journals', // Kirim collection jurnal (untuk loop semua jurnal dalam periode)
    //         'journal',  // Kirim single jurnal (untuk view harian/lama)
    //         'labelPeriode', 'labelTambahan',
    //         'startDate', 'endDate', 'school', 'isTeacher', 'user'
    //     ));

    //     $pdf->setPaper($school['paper_size'], $school['paper_orientation']);
    //     $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

    //     return $pdf->stream('Laporan-Absensi-Jurnal.pdf');
    // }

        {
        $startDate = null;
        $endDate = null;
        $labelPeriode = "";

        // 1. TENTUKAN PERIODE
        switch ($request->periode) {
            case 'harian':
                $startDate = $request->tanggal;
                $endDate = $request->tanggal;
                $labelPeriode = "Harian (" . Carbon::parse($startDate)->translatedFormat('d F Y') . ")";
                break;
            case 'mingguan':
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $labelPeriode = "Mingguan (" . Carbon::parse($startDate)->format('d/m') . " - " . Carbon::parse($endDate)->format('d/m/Y') . ")";
                break;
            case 'bulanan':
                $startDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
                $labelPeriode = "Bulan " . Carbon::createFromDate($request->tahun_bulan, $request->bulan, 1)->translatedFormat('F Y');
                break;
            case 'semester':
                if ($request->semester == 'ganjil') {
                    $startDate = $request->tahun_semester . '-07-01';
                    $endDate   = $request->tahun_semester . '-12-31';
                    $labelPeriode = "Semester Ganjil T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
                } else {
                    $startDate = ($request->tahun_semester + 1) . '-01-01';
                    $endDate   = ($request->tahun_semester + 1) . '-06-30';
                    $labelPeriode = "Semester Genap T.A " . $request->tahun_semester . "/" . ($request->tahun_semester + 1);
                }
                break;
        }

        $school = $this->getSchoolData();

        // 2. QUERY ABSENSI
        $query = Attendance::with(['student', 'schedule.subject', 'classroom'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->orderBy('check_in_time', 'asc');

        $labelTambahan = null;
        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
            $kelas = Classroom::find($request->classroom_id);
            if ($kelas) $labelTambahan = "Kelas: " . $kelas->name;
        }

        $attendances = $query->get();

        if ($attendances->isEmpty()) {
            return redirect()->back()->with('error', 'Data absensi tidak ditemukan pada periode/filter yang dipilih.');
        }

        // 3. QUERY JURNAL PEMBELAJARAN (PERBAIKAN)
        // Mengambil semua jurnal dalam rentang tanggal & filter kelas yang sama
        $journalQuery = TeachingJournal::with(['schedule.subject', 'schedule.classroom'])
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'asc');

        // Terapkan filter kelas yang sama ke Jurnal (via relasi schedule->classroom)
        if ($request->filled('classroom_id')) {
            $journalQuery->whereHas('schedule', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        $journals = $journalQuery->get();

        // Ambil satu jurnal untuk view harian (opsional, untuk kompatibilitas jika view lama pakai variable $journal)
        $journal = $journals->first();

        // 4. CEK USER LOGIN (GURU / ADMIN)
        $user = Auth::user();
        $isTeacher = false;

        if ($user->teacher) {
            $isTeacher = true;
        }

        // 5. LOAD VIEW PDF
        $pdf = Pdf::loadView('report.pdf_view_admin', compact(
            'attendances',
            'journals', // Kirim list jurnal lengkap
            'journal',  // Kirim single jurnal (fallback)
            'labelPeriode', 'labelTambahan',
            'startDate', 'endDate', 'school', 'isTeacher', 'user'
        ));

        $pdf->setPaper($school['paper_size'], $school['paper_orientation']);
        $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

        return $pdf->stream('Laporan-Absensi-Jurnal.pdf');
    }


    /**
     * METHOD BARU: Cetak Laporan Spesifik Jadwal/Mapel
     * Diakses dari tombol PDF di halaman Jadwal Mengajar
     */
    public function printSchedule($id)
    // {
    //     // 1. Ambil Data Jadwal
    //     $schedule = Schedule::with('classroom')->findOrFail($id);

    //     // 2. Ambil Data Absensi Jadwal Tersebut
    //     // Kita ambil data semester ini (opsional) atau semua history
    //     $attendances = Attendance::with(['student', 'schedule'])
    //                     ->where('schedule_id', $id)
    //                     ->orderBy('date', 'desc') // Tanggal terbaru di atas
    //                     ->orderBy('check_in_time', 'desc')
    //                     ->get();

    //     // 3. Siapkan Variabel untuk Header PDF
    //     // Karena view PDF kita butuh variable startDate/endDate, kita ambil dari data pertama & terakhir
    //     if ($attendances->count() > 0) {
    //         $startDate = $attendances->last()->date; // Tanggal terlama
    //         $endDate = $attendances->first()->date;  // Tanggal terbaru
    //     } else {
    //         $startDate = date('Y-m-d');
    //         $endDate = date('Y-m-d');
    //     }

    //     $labelPeriode = "Rekapitulasi Mata Pelajaran";
    //     $labelTambahan = "Mapel: " . $schedule->subject->name . " - Kelas: " . ($schedule->classroom->name ?? '-');


    //     $school = $this->getSchoolData();
    //     // 4. Generate PDF
    //     // Kita reuse (gunakan kembali) view 'report.pdf_view' yang sudah dibuat sebelumnya
    //     $pdf = Pdf::loadView('report.pdf_view', compact(
    //         'school',
    //         'attendances',
    //         'labelPeriode',
    //         'labelTambahan',
    //         'startDate',
    //         'endDate'
    //     ));

    //     // 2. LEWATKAN data $school ke view
    //     // Menggunakan compact() adalah cara yang ringkas

    //     // Jika Anda menggunakan Dompdf (barryvdh/laravel-dompdf):
    //     //$pdf = PDF::loadView('report.pdf_view', $data);

    //     $pdf->setPaper($school['paper_size'], $school['paper_orientation']);

    //     return $pdf->stream('Laporan-' . $schedule->subject_name . '.pdf');


    // }

    {
        // 1. Ambil Data Jadwal & Relasi
        $schedule = Schedule::with(['classroom', 'subject', 'teacher.user'])->findOrFail($id);

        // 2. Ambil Data Absensi pada Jadwal Tersebut
        $attendances = Attendance::with(['student'])
                        ->where('schedule_id', $id)
                        ->orderBy('date', 'asc') // Urutkan tanggal secara kronologis (bukan desc)
                        ->orderBy('check_in_time', 'asc')
                        ->get();

        // 3. Ambil Data Jurnal pada Jadwal Tersebut
        $journals = TeachingJournal::where('schedule_id', $id)
                        ->orderBy('date', 'asc')
                        ->get();

        // 4. Tentukan Rentang Tanggal (Min & Max) untuk Header
        // Gabungkan tanggal dari absensi dan jurnal untuk akurasi
        $dates = $attendances->pluck('date')
                    ->merge($journals->pluck('date'))
                    ->filter()
                    ->unique()
                    ->sort();

        if ($dates->count() > 0) {
            $startDate = $dates->first(); // Tanggal terlama (awal)
            $endDate = $dates->last();    // Tanggal terbaru (akhir)
        } else {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
        }

        // 5. Siapkan Label Header
        $labelPeriode = "Rekapitulasi Mata Pelajaran";
        $teacherName = $schedule->teacher->user->name ?? 'Guru';

        $labelTambahan = "Mapel: " . $schedule->subject->name .
                         " | Kelas: " . $schedule->classroom->name .
                         " | Guru: " . $teacherName;

        // 6. Data Sekolah & Tanda Tangan
        $school = $this->getSchoolData();
        $user = Auth::user();

        // Logika Tanda Tangan:
        // Jika yang login adalah guru ybs, tampilkan nama dia. Jika admin, default (Kepsek/Admin).
        $isTeacher = false;
        if ($user->teacher && $user->teacher->id == $schedule->teacher_id) {
            $isTeacher = true;
        }

        // 7. Generate PDF
        // Menggunakan view 'report.pdf_view_admin' yang sudah support tabel jurnal
        $pdf = Pdf::loadView('report.pdf_view', compact(
            'attendances',
            'journals',      // Mengirim data jurnal ke view
            'labelPeriode',
            'labelTambahan',
            'startDate',
            'endDate',
            'school',
            'isTeacher',
            'user'
        ));

        $pdf->setPaper($school['paper_size'] ?? 'a4', $school['paper_orientation'] ?? 'portrait');
        $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

        return $pdf->stream('Laporan-Mapel-' . preg_replace('/[^A-Za-z0-9\-]/', '_', $schedule->subject->name) . '.pdf');
    }

    /**
     * Print Student Individual History Report (Transcript)
     * Route: /report/student/{id}
     */
    public function printStudent($id)
    {
        // 1. Fetch Student Data
        $student = Student::with('classroom')->findOrFail($id);

        // 2. Fetch Attendance History
        $attendances = Attendance::with(['schedule.subject', 'schedule.teacher'])
                        ->where('student_id', $id)
                        ->orderBy('date', 'desc')
                        ->orderBy('check_in_time', 'desc')
                        ->get();


        // ==========================================
        //  TAMBAHKAN VALIDASI INI
        // ==========================================
        if ($attendances->isEmpty()) {
            return redirect()->back()->with('error', 'Data absensi tidak ditemukan pada periode/filter yang dipilih.');
        }
        // ==========================================

        // 3. Calculate Statistics
        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
            'total' => $attendances->count()
        ];

        // 4. Determine Date Range for Header
        $startDate = $attendances->last()->date ?? date('Y-m-d');
        $endDate = $attendances->first()->date ?? date('Y-m-d');

        // 5. Get School Settings
        $school = $this->getSchoolData();

        // 6. Generate PDF using specific view
        $pdf = Pdf::loadView('report.student_history', compact(
            'student',
            'attendances',
            'summary',
            'startDate',
            'endDate',
            'school'
        ));

        $pdf->setPaper($school['paper_size'], $school['paper_orientation']);

        return $pdf->stream('Laporan-Siswa-' . $student->name . '.pdf');
    }

    //  public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru & Jadwal
    //     $teacher = Teacher::with(['user', 'schedules.classroom', 'schedules.subject'])
    //                 ->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah (Kop Surat & TTD)
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester & Tahun Ajaran
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Kelompokkan Jadwal (Agar rapi di tabel)
    //     // Group by Hari -> Kelas -> Mapel
    //     $schedules = $teacher->schedules->sortBy(function($schedule) {
    //         // Urutkan hari (Senin=1, dst)
    //         $days = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
    //         return $days[$schedule->day] ?? 8;
    //     });

    //     // Hitung Total Jam
    //     $totalJam = 0;
    //     foreach($schedules as $s) {
    //         // Logika hitung JP: (End Time - Start Time) / 45 menit
    //         try {
    //             if ($s->start_time && $s->end_time) {
    //                 $start = Carbon::parse($s->start_time);
    //                 $end = Carbon::parse($s->end_time);
    //                 $diffInMinutes = $end->diffInMinutes($start);
    //                 $jp = round($diffInMinutes / 45); // Asumsi 1 JP = 45 menit
    //                 $s->calculated_jp = $jp > 0 ? $jp : 1;
    //             } else {
    //                 $s->calculated_jp = 0;
    //             }
    //         } catch (\Exception $e) {
    //             $s->calculated_jp = 0;
    //         }
    //         $totalJam += $s->calculated_jp;
    //     }

    //     // Nomor Surat (Bisa disesuaikan formatnya)
    //     $nomorSurat = "800/..../SMK-G/" . date('m') . "/" . date('Y');

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher',
    //         'school',
    //         'schedules',
    //         'semester',
    //         'tahunAjaran',
    //         'totalJam',
    //         'nomorSurat'
    //     ));

    //     // Setting kertas

    //     $paperSize = $school['paper_size'] ?? 'a4';
    //     $pdf->setPaper($paperSize, 'portrait');

    //     // Options untuk image/asset
    //     $pdf->setOptions([
    //         'isRemoteEnabled' => true,
    //         'isPhpEnabled' => true,
    //         'chroot' => public_path(),
    //     ]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru & Jadwal
    //     // $teacher = Teacher::with(['user', 'schedules.classroom', 'schedules.subject'])
    //                 // ->findOrFail($teacher_id);
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah (Kop Surat & TTD)
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester & Tahun Ajaran
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Kelompokkan Jadwal (Agar rapi di tabel)
    //     // Group by Hari -> Kelas -> Mapel
    //     $schedules = $teacher->schedules->sortBy(function($schedule) {
    //         // Urutkan hari (Senin=1, dst)
    //         $days = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
    //         return $days[$schedule->day] ?? 8;
    //     });

    //     // Hitung Total Jam
    //     $totalJam = 0;
    //     foreach($schedules as $s) {
    //         try {
    //             if ($s->start_time && $s->end_time) {
    //                 $start = Carbon::parse($s->start_time);
    //                 $end = Carbon::parse($s->end_time);
    //                 $diffInMinutes = $end->diffInMinutes($start);
    //                 $jp = round($diffInMinutes / 45); // Asumsi 1 JP = 45 menit
    //                 $s->calculated_jp = $jp > 0 ? $jp : 1;
    //             } else {
    //                 $s->calculated_jp = 0;
    //             }
    //         } catch (\Exception $e) {
    //             $s->calculated_jp = 0;
    //         }
    //         $totalJam += $s->calculated_jp;
    //     }

    //     // dd($school);

    //     // Nomor Surat (Bisa disesuaikan formatnya)
    //     // $nomorSurat = "800/..../SMK-G/" . date('m') . "/" . date('Y');
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/I/2026";

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher',
    //         'school',
    //         'schedules',
    //         'semester',
    //         'tahunAjaran',
    //         'totalJam',
    //         'nomorSurat'
    //     ));

    //     // Setting kertas
    //     // $paperSize = $school['paper_size'] ?? 'a4';
    //     // $pdf->setPaper($paperSize, 'portrait');
    //     $pdf->setPaper($school['paper_size'], $school['paper_orientation']);

    //     // Options untuk image/asset
    //     $pdf->setOptions([
    //         'isRemoteEnabled' => true,
    //         'isPhpEnabled' => true,
    //         'chroot' => public_path(),
    //     ]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah (Kop Surat & TTD)
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester & Tahun Ajaran
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal Mengajar (Direct Query agar lebih aman)
    //     // Pastikan table schedules punya kolom 'teacher_id'
    //     $schedulesQuery = Schedule::with(['classroom', 'subject'])
    //                         ->where('teacher_id', $teacher_id)
    //                         ->get();

    //     // Kelompokkan dan Urutkan Jadwal
    //     $schedules = $schedulesQuery->sortBy(function($schedule) {
    //         // Urutkan hari (Senin=1, dst) - Handle case insensitive
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         return $dayMap[strtolower($schedule->day)] ?? 8;
    //     });

    //     // Hitung Total Jam
    //     $totalJam = 0;
    //     foreach($schedules as $s) {
    //         try {
    //             if ($s->start_time && $s->end_time) {
    //                 $start = Carbon::parse($s->start_time);
    //                 $end = Carbon::parse($s->end_time);

    //                 // Hitung durasi dalam menit
    //                 $diffInMinutes = $end->diffInMinutes($start);

    //                 // Asumsi 1 JP = 45 menit (bisa disesuaikan, misal 40)
    //                 $jp = round($diffInMinutes / 45);

    //                 // Pastikan minimal 1 JP jika ada jadwal
    //                 $s->calculated_jp = $jp > 0 ? $jp : 1;
    //             } else {
    //                 // Jika jam tidak diisi, default 0 atau 1
    //                 $s->calculated_jp = 0;
    //             }
    //         } catch (\Exception $e) {
    //             $s->calculated_jp = 0;
    //         }
    //         $totalJam += $s->calculated_jp;
    //     }

    //     // Nomor Surat (Bisa disesuaikan formatnya)
    //     // $nomorSurat = "800/..../SMK-G/" . date('m') . "/" . date('Y');
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/I/2026" ;

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher',
    //         'school',
    //         'schedules',
    //         'semester',
    //         'tahunAjaran',
    //         'totalJam',
    //         'nomorSurat'
    //     ));

    //     // Setting kertas
    //     $paperSize = $school['paper_size'] ?? 'a4';
    //     $pdf->setPaper($paperSize, 'portrait');

    //     // Options untuk image/asset
    //     $pdf->setOptions([
    //         'isRemoteEnabled' => true,
    //         'isPhpEnabled' => true,
    //         'chroot' => public_path(),
    //     ]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah (Kop Surat & TTD)
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester & Tahun Ajaran
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal Mengajar (Raw Data)
    //     $rawSchedules = Schedule::with(['classroom', 'subject'])
    //                         ->where('teacher_id', $teacher_id)
    //                         ->get();

    //     // 5. LOGIKA BARU: GROUPING JADWAL
    //     // Gabungkan jadwal jika Hari, Kelas, dan Mapel-nya sama
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower($item->day) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         // Ambil item pertama sebagai perwakilan data (Nama Mapel, Kelas, Hari)
    //         $schedule = $group->first();

    //         // Variabel hitung total grup ini
    //         $groupJp = 0;
    //         $minStart = null;
    //         $maxEnd = null;

    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 // Cari jam paling awal dan paling akhir dalam grup ini
    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 // Hitung durasi per item (45 menit = 1 JP)
    //                 $diff = $end->diffInMinutes($start);
    //                 $jp = round($diff / 45);
    //                 if ($jp < 1) $jp = 1; // Minimal 1 JP

    //                 $groupJp += $jp;
    //             }
    //         }

    //         // Update data schedule untuk ditampilkan di View
    //         $schedule->calculated_jp = $groupJp; // Total JP hasil penjumlahan

    //         // Update jam mulai & selesai agar mencakup seluruh sesi (misal 07:00 - 09:15)
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i:s');
    //             $schedule->end_time = $maxEnd->format('H:i:s');
    //         }

    //         $totalJam += $groupJp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan Hasil Akhir berdasarkan Hari
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         return $dayMap[strtolower($schedule->day)] ?? 8;
    //     });

    //     // Nomor Surat
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/I/2026";

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher',
    //         'school',
    //         'schedules',
    //         'semester',
    //         'tahunAjaran',
    //         'totalJam',
    //         'nomorSurat'
    //     ));

    //     // Setting kertas
    //     $paperSize = $school['paper_size'] ?? 'a4';
    //     $pdf->setPaper($paperSize, 'portrait');

    //     // Options untuk image/asset
    //     $pdf->setOptions([
    //         'isRemoteEnabled' => true,
    //         'isPhpEnabled' => true,
    //         'chroot' => public_path(),
    //     ]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah (Kop Surat & TTD)
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester & Tahun Ajaran
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal Mengajar (Raw Data)
    //     $rawSchedules = Schedule::with(['classroom', 'subject'])
    //                         ->where('teacher_id', $teacher_id)
    //                         ->get();

    //     // 5. LOGIKA BARU: GROUPING JADWAL
    //     // Gabungkan jadwal jika Hari, Kelas, dan Mapel-nya sama
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         // Trim dan lowercase untuk memastikan grouping akurat
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         // Ambil item pertama sebagai perwakilan data
    //         $schedule = $group->first();

    //         // --- LOGIKA HITUNG JAM (HYBRID) ---
    //         // 1. Hitung berdasarkan durasi waktu (untuk format data yang digabung, misal 07:00-08:30 = 2 JP)
    //         $jpByDuration = 0;
    //         $minStart = null;
    //         $maxEnd = null;

    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 // Cari rentang waktu total untuk grup ini
    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 // Hitung JP item ini (40-45 menit dianggap 1 JP)
    //                 $minutes = $end->diffInMinutes($start);
    //                 $jpItem = round($minutes / 45);
    //                 if ($jpItem < 1) $jpItem = 1; // Minimal 1 JP per item valid
    //                 $jpByDuration += $jpItem;
    //             }
    //         }

    //         // 2. Hitung berdasarkan jumlah baris data (untuk format data per sesi/row)
    //         $jpByCount = $group->count();

    //         // 3. Cek apakah ada kolom manual 'jp' atau 'sks' di database
    //         $jpManual = $group->sum(function($item) {
    //             return $item->jp ?? $item->sks ?? 0;
    //         });

    //         // --- KEPUTUSAN FINAL JP ---
    //         // Ambil nilai terbesar agar tidak under-estimated
    //         // Prioritas: Manual JP > Durasi Waktu > Jumlah Baris
    //         if ($jpManual > 0) {
    //             $finalJp = $jpManual;
    //         } else {
    //             $finalJp = max($jpByDuration, $jpByCount);
    //         }

    //         // Simpan hasil perhitungan ke object schedule
    //         $schedule->calculated_jp = $finalJp;

    //         // Update jam mulai & selesai agar mencakup seluruh sesi (misal 07:00 - 09:15)
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i:s');
    //             $schedule->end_time = $maxEnd->format('H:i:s');
    //         }

    //         $totalJam += $finalJp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan Hasil Akhir berdasarkan Hari
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         return $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //     });

    //     // Nomor Surat
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/I/2026";

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher',
    //         'school',
    //         'schedules',
    //         'semester',
    //         'tahunAjaran',
    //         'totalJam',
    //         'nomorSurat'
    //     ));

    //     // Setting kertas
    //     $paperSize = $school['paper_size'] ?? 'a4';
    //     $pdf->setPaper($paperSize, 'portrait');

    //     // Options untuk image/asset
    //     $pdf->setOptions([
    //         'isRemoteEnabled' => true,
    //         'isPhpEnabled' => true,
    //         'chroot' => public_path(),
    //     ]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah (Kop Surat & TTD)
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester & Tahun Ajaran
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal Mengajar (Raw Data)
    //     $rawSchedules = Schedule::with(['classroom', 'subject'])
    //                         ->where('teacher_id', $teacher_id)
    //                         ->get();

    //     // 5. LOGIKA BARU: GROUPING JADWAL
    //     // Gabungkan jadwal jika Hari, Kelas, dan Mapel-nya sama
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         // Ambil item pertama sebagai perwakilan data
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;

    //         // Cari waktu mulai paling awal dan selesai paling akhir dalam grup ini
    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;
    //             }
    //         }

    //         // Hitung JP berdasarkan Total Durasi (Max End - Min Start)
    //         // Ini mengatasi masalah data yang terpecah menjadi banyak baris
    //         if ($minStart && $maxEnd) {
    //             $totalMinutes = $maxEnd->diffInMinutes($minStart);
    //             // Asumsi 1 JP = 45 menit
    //             $jp = round($totalMinutes / 45);

    //             // Pastikan minimal 1 JP jika ada jadwal valid
    //             $schedule->calculated_jp = $jp > 0 ? $jp : 1;

    //             // Update jam tampilan agar mencakup rentang total
    //             $schedule->start_time = $minStart->format('H:i:s');
    //             $schedule->end_time = $maxEnd->format('H:i:s');
    //         } else {
    //             // Fallback: Gunakan jumlah baris data jika waktu tidak valid
    //             $schedule->calculated_jp = $group->count();
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan Hasil Akhir berdasarkan Hari dan Jam Mulai
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         // Sort index: Hari + Jam Mulai
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // Nomor Surat
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/I/2026";

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher',
    //         'school',
    //         'schedules',
    //         'semester',
    //         'tahunAjaran',
    //         'totalJam',
    //         'nomorSurat'
    //     ));

    //     // Setting kertas
    //     $paperSize = $school['paper_size'] ?? 'a4';
    //     $pdf->setPaper($paperSize, 'portrait');

    //     // Options untuk image/asset
    //     $pdf->setOptions([
    //         'isRemoteEnabled' => true,
    //         'isPhpEnabled' => true,
    //         'chroot' => public_path(),
    //     ]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah (Kop Surat & TTD)
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester & Tahun Ajaran
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal Mengajar (Raw Data)
    //     $rawSchedules = Schedule::with(['classroom', 'subject'])
    //                         ->where('teacher_id', $teacher_id)
    //                         ->get();

    //     // 5. LOGIKA BARU: GROUPING JADWAL
    //     // Gabungkan jadwal jika Hari, Kelas, dan Mapel-nya sama
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         // Ambil item pertama sebagai perwakilan data
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;

    //         // Cari waktu mulai paling awal dan selesai paling akhir dalam grup ini
    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;
    //             }
    //         }

    //         // Hitung JP berdasarkan Total Durasi (Max End - Min Start)
    //         // Ini mengatasi masalah data yang terpecah menjadi banyak baris
    //         if ($minStart && $maxEnd) {
    //             $totalMinutes = $maxEnd->diffInMinutes($minStart);
    //             // Asumsi 1 JP = 45 menit
    //             $jp = round($totalMinutes / 45);

    //             // Pastikan minimal 1 JP jika ada jadwal valid
    //             $schedule->calculated_jp = $jp > 0 ? $jp : 1;

    //             // Update jam tampilan agar mencakup rentang total
    //             $schedule->start_time = $minStart->format('H:i:s');
    //             $schedule->end_time = $maxEnd->format('H:i:s');
    //         } else {
    //             // Fallback: Gunakan jumlah baris data jika waktu tidak valid
    //             $schedule->calculated_jp = $group->count();
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan Hasil Akhir berdasarkan Hari dan Jam Mulai
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         // Sort index: Hari + Jam Mulai
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // Nomor Surat
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/I/2026";

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher',
    //         'school',
    //         'schedules',
    //         'semester',
    //         'tahunAjaran',
    //         'totalJam',
    //         'nomorSurat'
    //     ));

    //     // Setting kertas
    //     $paperSize = $school['paper_size'] ?? 'a4';
    //     $pdf->setPaper($paperSize, 'portrait');

    //     // Options untuk image/asset
    //     $pdf->setOptions([
    //         'isRemoteEnabled' => true,
    //         'isPhpEnabled' => true,
    //         'chroot' => public_path(),
    //     ]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    //  public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah (Kop Surat & TTD)
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester & Tahun Ajaran
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal Mengajar (Raw Data)
    //     $rawSchedules = Schedule::with(['classroom', 'subject'])
    //                         ->where('teacher_id', $teacher_id)
    //                         ->get();

    //     // 5. LOGIKA BARU: GROUPING JADWAL
    //     // Gabungkan jadwal jika Hari, Kelas, dan Mapel-nya sama
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         // Ambil item pertama sebagai perwakilan data
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;
    //         $totalMinutes = 0;

    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 // Akumulasi menit untuk perhitungan JP yang lebih akurat
    //                 // Ini menangani kasus jika ada jeda istirahat di tengah jam pelajaran
    //                 $totalMinutes += $end->diffInMinutes($start);
    //             }
    //         }

    //         // Hitung JP berdasarkan total menit (45 menit = 1 JP)
    //         if ($totalMinutes > 0) {
    //             $jp = round($totalMinutes / 45);
    //             $schedule->calculated_jp = $jp > 0 ? $jp : 1;
    //         } else {
    //             // Fallback: Gunakan jumlah baris data jika waktu tidak valid
    //             $schedule->calculated_jp = $group->count();
    //         }


    //         // Update jam tampilan agar mencakup rentang total (07:00 - 09:15)
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i');
    //             $schedule->end_time = $maxEnd->format('H:i');
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan Hasil Akhir berdasarkan Hari dan Jam Mulai
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         // Sort index: Hari + Jam Mulai
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // Nomor Surat
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/I/" . date('Y');

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher',
    //         'school',
    //         'schedules',
    //         'semester',
    //         'tahunAjaran',
    //         'totalJam',
    //         'nomorSurat'
    //     ));

    //     // Setting kertas
    //     $paperSize = $school['paper_size'] ?? 'a4';
    //     $pdf->setPaper($paperSize, 'portrait');

    //     // Options untuk image/asset
    //     $pdf->setOptions([
    //         'isRemoteEnabled' => true,
    //         'isPhpEnabled' => true,
    //         'chroot' => public_path(),
    //     ]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }
    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah (Kop Surat & TTD)
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester & Tahun Ajaran
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal Mengajar (Raw Data)
    //     // Pastikan relasi classroom dan subject ter-load
    //     $rawSchedules = Schedule::with(['classroom', 'subject'])
    //                         ->where('teacher_id', $teacher_id)
    //                         ->get();

    //     // 5. LOGIKA GROUPING JADWAL (Solusi untuk Jam = 1)
    //     // Gabungkan jadwal jika Hari, Kelas, dan Mapel-nya sama
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         // Ambil item pertama sebagai perwakilan data
    //         $schedule = $group->first();

    //         // Variabel hitung durasi grup ini
    //         $minStart = null;
    //         $maxEnd = null;
    //         $totalMinutes = 0;

    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 // Cari jam mulai paling awal dan selesai paling akhir
    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 // Akumulasi total menit (agar akurat jika ada jam istirahat di antaranya)
    //                 $totalMinutes += $end->diffInMinutes($start);
    //             }
    //         }

    //         // Hitung JP: Total Menit / 45
    //         if ($totalMinutes > 0) {
    //             $jp = round($totalMinutes / 45);
    //             $schedule->calculated_jp = $jp > 0 ? $jp : 1;
    //         } else {
    //             $schedule->calculated_jp = $group->count(); // Fallback ke jumlah baris
    //         }

    //         // Update jam tampilan agar mencakup rentang total (07:00 - 09:15)
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i');
    //             $schedule->end_time = $maxEnd->format('H:i');
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan Hasil Akhir
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // Nomor Surat Custom
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/I/2026";

    //     // Generate PDF
    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher',
    //         'school',
    //         'schedules',
    //         'semester',
    //         'tahunAjaran',
    //         'totalJam',
    //         'nomorSurat'
    //     ));

    //     // Setting kertas & Options
    //     $pdf->setPaper($school['paper_size'] ?? 'a4', 'portrait');
    //     $pdf->setOptions([
    //         'isRemoteEnabled' => true,
    //         'isPhpEnabled' => true,
    //         'chroot' => public_path(),
    //     ]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal ...
    //     $rawSchedules = Schedule::with(['classroom', 'subject']) // Pastikan relasi 'room' diload jika ada
    //                             ->where('teacher_id', $teacher_id)
    //                             ->get();

    //     // 5. GROUPING JADWAL (UPDATE DI SINI)
    //     // Gabungkan jika Hari, Kelas, dan Mapel sama
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;
    //         $totalMinutesGlobal = 0;

    //         // --- A. LOGIKA HITUNG JAM PER RUANGAN ---
    //         $roomDetails = [];

    //         // Group item berdasarkan nama ruangan
    //         $itemsByRoom = $group->groupBy(function($item) {
    //             // Ambil nama ruangan (dari relasi atau string langsung)
    //             return $item->room->name ?? $item->room ?? 'Tanpa Ruangan';
    //         });

    //         foreach ($itemsByRoom as $roomName => $roomItems) {
    //             $roomMinutes = 0;

    //             foreach ($roomItems as $rItem) {
    //                 if ($rItem->start_time && $rItem->end_time) {
    //                     $s = \Carbon\Carbon::parse($rItem->start_time);
    //                     $e = \Carbon\Carbon::parse($rItem->end_time);
    //                     $roomMinutes += $e->diffInMinutes($s);
    //                 }
    //             }

    //             // Konversi Menit Ruangan ke JP
    //             if ($roomMinutes > 0) {
    //                 $rJp = round($roomMinutes / 45);
    //                 $rJp = $rJp > 0 ? $rJp : 1;
    //             } else {
    //                 $rJp = $roomItems->count(); // Fallback jika jam null
    //             }

    //             // Format: "Bengkel (4 JP)"
    //             $roomDetails[] = $roomName . ' (' . $rJp . ' JP)';
    //         }

    //         // dd($roomMinutes);

    //         // Gabungkan string ruangan: "R.Teori (2 JP), Bengkel (4 JP)"
    //         $schedule->merged_room = implode(', ', $roomDetails);


    //         // --- B. LOGIKA TOTAL JAM & WAKTU (GLOBAL) ---
    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = \Carbon\Carbon::parse($item->start_time);
    //                 $end = \Carbon\Carbon::parse($item->end_time);

    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 $totalMinutesGlobal += $end->diffInMinutes($start);
    //             }
    //         }

    //         // Hitung JP Global untuk kolom "Jumlah Jam"
    //         if ($totalMinutesGlobal > 0) {
    //             $jp = round($totalMinutesGlobal / 45);
    //             $schedule->calculated_jp = $jp > 0 ? $jp : 1;
    //         } else {
    //             $schedule->calculated_jp = $group->count();
    //         }

    //         // Set jam tampilan (Mulai - Selesai)
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i');
    //             $schedule->end_time = $maxEnd->format('H:i');
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // ... (Bagian 6 sorting dan return PDF tetap sama) ...
    //     // 6. Urutkan berdasarkan Hari
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/I/" . date('Y');

    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher', 'school', 'schedules', 'semester', 'tahunAjaran', 'totalJam', 'nomorSurat'
    //     ));

    //     $pdf->setPaper($school['paper_size'] ?? 'a4', 'portrait');
    //     $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal Mengajar
    //     // Eager load 'room' dan 'classroom' serta 'subject'
    //     $rawSchedules = Schedule::with(['classroom', 'subject', 'room'])
    //                         ->where('teacher_id', $teacher_id)
    //                         ->get();

    //     // 5. GROUPING JADWAL
    //     // Gabungkan jika Hari, Kelas, dan Mapel sama
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;
    //         $totalMinutes = 0;

    //         // --- FITUR NAMA RUANGAN ---
    //         // Gabungkan nama ruangan unik dalam grup ini
    //         $rooms = $group->map(function($item) {
    //             // Prioritas 1: Relasi ke tabel rooms (item->room->name)
    //             // Prioritas 2: Kolom string manual (item->room)
    //             return $item->room->name ?? $item->room ?? null;
    //         })
    //         ->filter(function($value) { return !empty($value); })
    //         ->unique()
    //         ->implode(', ');

    //         $schedule->merged_room = $rooms ?: '-';


    //         // --- HITUNG DURASI & JAM ---
    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 // Akumulasi menit
    //                 $totalMinutes += $end->diffInMinutes($start);
    //             }
    //         }

    //         // Konversi Menit ke JP (45 menit = 1 JP)
    //         if ($totalMinutes > 0) {
    //             $jp = round($totalMinutes / 45);
    //             $schedule->calculated_jp = $jp > 0 ? $jp : 1;
    //         } else {
    //             $schedule->calculated_jp = $group->count(); // Fallback ke jumlah sesi
    //         }

    //         // Set jam tampilan
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i');
    //             $schedule->end_time = $maxEnd->format('H:i');
    //         }

    //         $totalJam += $schedule->calculated_jp;

    //         // dd($totalJam);
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan berdasarkan Hari
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // Generate Nomor Surat (Dengan Bulan Romawi)
    //     $bulanRomawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
    //     $currMonth = date('n');
    //     $romawi = $bulanRomawi[$currMonth] ?? 'I';

    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/" . $romawi . "/" . date('Y');

    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher', 'school', 'schedules', 'semester', 'tahunAjaran', 'totalJam', 'nomorSurat'
    //     ));

    //     $pdf->setPaper($school['paper_size'] ?? 'a4', 'portrait');
    //     $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    // public function printSuratTugas($teacher_id)
    // {
    //     // 1. Ambil Data Guru
    //     $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

    //     // 2. Ambil Data Sekolah
    //     $school = $this->getSchoolData();

    //     // 3. Tentukan Semester
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 4. Ambil Jadwal Mengajar
    //     $rawSchedules = Schedule::with(['classroom', 'subject', 'room'])
    //                         ->where('teacher_id', $teacher_id)
    //                         ->get();

    //     // 5. GROUPING JADWAL
    //     // Gabungkan jika Hari, Kelas, dan Mapel sama
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;
    //         $totalMinutes = 0;

    //         // --- FITUR NAMA RUANGAN ---
    //         $rooms = $group->map(function($item) {
    //             return $item->room->name ?? $item->room ?? null;
    //         })
    //         ->filter(function($value) { return !empty($value); })
    //         ->unique()
    //         ->implode(', ');

    //         $schedule->merged_room = $rooms ?: '-';

    //         // --- HITUNG DURASI & JAM ---
    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 // Akumulasi menit dari setiap sesi
    //                 $totalMinutes += $end->diffInMinutes($start);
    //             }
    //         }

    //         // OPSI 1: Hitung JP dari total durasi (45 menit = 1 JP)
    //         // Contoh: 135 menit / 45 = 3 JP
    //         $jpByTime = 0;
    //         if ($totalMinutes > 0) {
    //             $jpByTime = round($totalMinutes / 45);
    //         }

    //         // OPSI 2: Hitung JP dari jumlah baris data
    //         // Contoh: Ada 3 baris jadwal untuk mapel ini = 3 JP
    //         $jpByCount = $group->count();

    //         // SOLUSI UTAMA: AMBIL NILAI TERBESAR
    //         // Ini menangani kasus data per baris maupun data per blok waktu
    //         $finalJP = max($jpByTime, $jpByCount);

    //         // Validasi minimal 1 JP
    //         $schedule->calculated_jp = $finalJP > 0 ? $finalJP : 1;

    //         // Set jam tampilan
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i');
    //             $schedule->end_time = $maxEnd->format('H:i');
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan berdasarkan Hari
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1, 'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3, 'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5, 'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // Nomor Surat
    //     $bulanRomawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
    //     $currMonth = date('n');
    //     $romawi = $bulanRomawi[$currMonth] ?? 'I';
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/" . $romawi . "/" . date('Y');

    //     $pdf = Pdf::loadView('pdf.surat_tugas', compact(
    //         'teacher', 'school', 'schedules', 'semester', 'tahunAjaran', 'totalJam', 'nomorSurat'
    //     ));

    //     $pdf->setPaper($school['paper_size'] ?? 'a4', 'portrait');
    //     $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

    //     return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    // }

    public function printSuratTugas($teacher_id)
    {
        // 1. Ambil Data Guru
        $teacher = Teacher::with(['user'])->findOrFail($teacher_id);

        // 2. Ambil Data Sekolah
        $school = $this->getSchoolData();

        // 3. Tentukan Semester
        $bulan = date('n');
        $tahun = date('Y');
        if ($bulan >= 7) {
            $semester = "Ganjil";
            $tahunAjaran = $tahun . "/" . ($tahun + 1);
        } else {
            $semester = "Genap";
            $tahunAjaran = ($tahun - 1) . "/" . $tahun;
        }

        // 4. Ambil Jadwal Mengajar
        $rawSchedules = Schedule::with(['classroom', 'subject', 'room'])
                            ->where('teacher_id', $teacher_id)
                            ->get();

        // 5. GROUPING JADWAL
        // Gabungkan jika Hari, Kelas, dan Mapel sama (Case Insensitive)
        $groupedSchedules = $rawSchedules->groupBy(function($item) {
            return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
        });

        $finalSchedules = collect();
        $totalJam = 0;

        foreach ($groupedSchedules as $group) {
            $schedule = $group->first();

            $minStart = null;
            $maxEnd = null;
            $totalMinutes = 0;

            // // --- FITUR NAMA RUANGAN ---
            // $rooms = $group->map(function($item) {
            //     return $item->room->name ?? $item->room ?? null;
            // })
            // ->filter(function($value) { return !empty($value); })
            // ->unique()
            // ->implode(', ');

            // $schedule->merged_room = $rooms ?: '-';

            // --- PERBAIKAN FITUR NAMA RUANGAN ---
            $rooms = $group->map(function($item) {
                // 1. Cek Relasi ke Tabel Rooms (Jika pakai ID)
                if (!empty($item->room) && isset($item->room->code)) {
                    return $item->room->code;
                }

                // 2. Cek Kolom String Manual 'room'
                // Gunakan getAttribute() untuk memaksa ambil nilai kolom database
                // (Mencegah konflik jika nama relasi = nama kolom)
                $manualRoom = $item->getAttribute('room');

                return $manualRoom ?? null;
            })
            ->filter(function($value) { return !empty($value); })
            ->unique()
            ->implode(', ');

            // Jika kosong, set string kosong (nanti di view dihandle)
            $schedule->merged_room = $rooms ?: '';

            // --- HITUNG DURASI & JAM ---
            foreach ($group as $item) {
                if ($item->start_time && $item->end_time) {
                    $start = Carbon::parse($item->start_time);
                    $end = Carbon::parse($item->end_time);

                    // Cari rentang waktu total untuk grup ini (untuk tampilan)
                    if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
                    if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

                    // Akumulasi menit dari setiap sesi (Pastikan diffInMinutes positif)
                    // Jika data 07:45 - 12:30, diff = 285 menit
                    $minutes = $end->diffInMinutes($start);
                    $totalMinutes += abs($minutes);
                }
            }

            // OPSI 1: Hitung JP dari total durasi (45 menit = 1 JP)
            // Contoh: 285 menit / 45 = 6.33 -> round jadi 6
            $jpByTime = 0;
            if ($totalMinutes > 0) {
                // Gunakan round() atau floor() tergantung kebijakan sekolah.
                // Biasanya round() cukup aman. Jika ingin pembulatan ke bawah (agar tidak kelebihan), gunakan floor().
                $jpByTime = round($totalMinutes / 45);
            }

            // OPSI 2: Hitung JP dari jumlah baris data
            $jpByCount = $group->count();

            // SOLUSI: Ambil nilai terbesar
            // Jika data tersimpan sebagai "07:45-12:30" (1 baris), jpByCount = 1, jpByTime = 6. Maka diambil 6.
            // Jika data tersimpan per jam (6 baris), jpByCount = 6, jpByTime = 6. Maka diambil 6.
            $finalJP = max($jpByTime, $jpByCount);

            // Validasi minimal 1 JP
            $schedule->calculated_jp = $finalJP > 0 ? $finalJP : 1;

            // Set jam tampilan
            if ($minStart && $maxEnd) {
                $schedule->start_time = $minStart->format('H:i');
                $schedule->end_time = $maxEnd->format('H:i');
            }

            $totalJam += $schedule->calculated_jp;
            $finalSchedules->push($schedule);
        }

        // 6. Urutkan berdasarkan Hari
        $schedules = $finalSchedules->sortBy(function($schedule) {
            $dayMap = [
                'monday' => 1, 'senin' => 1,
                'tuesday' => 2, 'selasa' => 2,
                'wednesday' => 3, 'rabu' => 3,
                'thursday' => 4, 'kamis' => 4,
                'friday' => 5, 'jumat' => 5,
                'saturday' => 6, 'sabtu' => 6,
                'sunday' => 7, 'minggu' => 7
            ];
            $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
            return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
        });

        // Nomor Surat
        $bulanRomawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
        $currMonth = date('n');
        $romawi = $bulanRomawi[$currMonth] ?? 'I';
        $nomorSurat = "800.1.11.1/002/SMKN1 BKT/" . $romawi . "/" . date('Y');

        $pdf = Pdf::loadView('pdf.surat_tugas', compact(
            'teacher', 'school', 'schedules', 'semester', 'tahunAjaran', 'totalJam', 'nomorSurat'
        ));

        $pdf->setPaper($school['paper_size'] ?? 'a4', 'portrait');
        $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

        return $pdf->stream('Surat_Tugas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $teacher->user->name) . '.pdf');
    }

    /**
     * CETAK SURAT TUGAS SEMUA GURU (Batch)
     */
    public function printAllSuratTugas()
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '512M');

        // 1. Ambil Semua Guru
        $teachers = Teacher::with(['user'])->get()->sortBy(function($t) {
            return $t->user->name;
        });

        // 2. Ambil Semua Jadwal sekaligus (Eager Load) lalu Grouping by Teacher ID
        // Ini menghindari masalah jika relasi di model Teacher belum diset dgn benar atau kosong
        $allSchedules = Schedule::with(['classroom', 'subject', 'room'])
                            ->get()
                            ->groupBy('teacher_id');

        $school = $this->getSchoolData();
        $allData = [];

        foreach($teachers as $teacher) {
            // Cek apakah guru ini punya jadwal di collection yang sudah diambil
            if (isset($allSchedules[$teacher->id])) {
                $teacherSchedules = $allSchedules[$teacher->id];

                // Proses data menggunakan helper yang menerima Raw Schedules
                $allData[] = $this->processSuratTugasData($teacher, $teacherSchedules);
            }
        }

        if (empty($allData)) {
            return redirect()->back()->with('error', 'Tidak ada data jadwal ditemukan untuk dicetak.');
        }

        $pdf = Pdf::loadView('pdf.surat_tugas_all', compact('allData', 'school'));

        $pdf->setPaper($school['paper_size'] ?? 'a4', 'portrait');
        $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

        return $pdf->stream('Surat_Tugas_Semua_Guru.pdf');
    }

    private function processSuratTugasData($teacher, $rawSchedules)
{
    // 1. Tentukan Semester
    $bulan = date('n');
    $tahun = date('Y');
    if ($bulan >= 7) {
        $semester = "Ganjil";
        $tahunAjaran = $tahun . "/" . ($tahun + 1);
    } else {
        $semester = "Genap";
        $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    }

    // 2. Grouping Jadwal
    $groupedSchedules = $rawSchedules->groupBy(function($item) {
        return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    });

    $finalSchedules = collect();
    $totalJam = 0;

    foreach ($groupedSchedules as $group) {
        $schedule = $group->first();

        $minStart = null;
        $maxEnd = null;
        $totalMinutes = 0;

        // --- FITUR NAMA RUANGAN ---
        $rooms = $group->map(function($item) {
            return $item->room->code ?? $item->room ?? null;
        })
        ->filter(function($value) { return !empty($value); })
        ->unique()
        ->implode(', ');

        $schedule->merged_room = $rooms ?: '-';

        // --- HITUNG DURASI & JAM ---
        foreach ($group as $item) {
            if ($item->start_time && $item->end_time) {
                $start = Carbon::parse($item->start_time);
                $end = Carbon::parse($item->end_time);

                // Cari rentang waktu total untuk grup ini
                if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
                if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

                $minutes = $end->diffInMinutes($start);
                $totalMinutes += abs($minutes);
            }
        }

        // OPSI 1: Hitung JP dari total durasi (45 menit = 1 JP)
        $jpByTime = 0;
        if ($totalMinutes > 0) {
            $jpByTime = round($totalMinutes / 45);
        }

        // OPSI 2: Hitung JP dari jumlah baris data
        $jpByCount = $group->count();

        // Ambil nilai terbesar
        $finalJP = max($jpByTime, $jpByCount);

        // ==========================================================
        // --- LOGIKA PENGURANGAN JAM ISTIRAHAT (11:45 - 12:30) ---
        // ==========================================================
        if ($minStart && $maxEnd) {
            // Set waktu istirahat pada tanggal yang sama dengan jadwal
            $breakStart = $minStart->copy()->setTime(12, 30, 0);
            $breakEnd   = $minStart->copy()->setTime(13, 15, 0);

            // Logika: Jika jadwal MULAI sebelum/pas 11:45 DAN SELESAI setelah/pas 12:30
            // Artinya jadwal tersebut "menelan" waktu istirahat.
            if ($minStart->lte($breakStart) && $maxEnd->gte($breakEnd)) {
                $finalJP = $finalJP - 1; // Kurangi 1 JP
            }
        }
        // ==========================================================

        // Validasi minimal 1 JP (Mencegah nilai 0 atau negatif jika hasil pengurangan)
        $schedule->calculated_jp = $finalJP > 0 ? $finalJP : 1;

        // Set jam tampilan
        if ($minStart && $maxEnd) {
            $schedule->start_time = $minStart->format('H:i');
            $schedule->end_time = $maxEnd->format('H:i');
        }

        $totalJam += $schedule->calculated_jp;
        $finalSchedules->push($schedule);
    }

    // 6. Urutkan berdasarkan Hari
    $schedules = $finalSchedules->sortBy(function($schedule) {
        $dayMap = [
            'monday' => 1, 'senin' => 1,
            'tuesday' => 2, 'selasa' => 2,
            'wednesday' => 3, 'rabu' => 3,
            'thursday' => 4, 'kamis' => 4,
            'friday' => 5, 'jumat' => 5,
            'saturday' => 6, 'sabtu' => 6,
            'sunday' => 7, 'minggu' => 7
        ];
        $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
        return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    });

    // Nomor Surat
    $bulanRomawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
    $currMonth = date('n');
    $romawi = $bulanRomawi[$currMonth] ?? 'I';
    $nomorSurat = "800.1.11.1/002/SMKN1 BKT/" . $romawi . "/" . date('Y');

    return compact('teacher', 'schedules', 'semester', 'tahunAjaran', 'totalJam', 'nomorSurat');
}

    // private function processSuratTugasData($teacher, $rawSchedules)
    // {
    //     // 1. Tentukan Semester
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 2. Grouping Jadwal
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;



    //     foreach ($groupedSchedules as $group) {
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;
    //         $totalMinutes = 0;

    //         // --- FITUR NAMA RUANGAN ---
    //         $rooms = $group->map(function($item) {
    //             return $item->room->code ?? $item->room ?? null;
    //         })
    //         ->filter(function($value) { return !empty($value); })
    //         ->unique()
    //         ->implode(', ');

    //         $schedule->merged_room = $rooms ?: '-';

    //         // --- HITUNG DURASI & JAM ---
    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 // Cari rentang waktu total untuk grup ini (untuk tampilan)
    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 // Akumulasi menit dari setiap sesi (Pastikan diffInMinutes positif)
    //                 // Jika data 07:45 - 12:30, diff = 285 menit
    //                 $minutes = $end->diffInMinutes($start);
    //                 $totalMinutes += abs($minutes);
    //             }
    //         }

    //         // OPSI 1: Hitung JP dari total durasi (45 menit = 1 JP)
    //         // Contoh: 285 menit / 45 = 6.33 -> round jadi 6
    //         $jpByTime = 0;
    //         if ($totalMinutes > 0) {
    //             // Gunakan round() atau floor() tergantung kebijakan sekolah.
    //             // Biasanya round() cukup aman. Jika ingin pembulatan ke bawah (agar tidak kelebihan), gunakan floor().
    //             $jpByTime = round($totalMinutes / 45);
    //         }

    //         // OPSI 2: Hitung JP dari jumlah baris data
    //         $jpByCount = $group->count();

    //         // SOLUSI: Ambil nilai terbesar
    //         // Jika data tersimpan sebagai "07:45-12:30" (1 baris), jpByCount = 1, jpByTime = 6. Maka diambil 6.
    //         // Jika data tersimpan per jam (6 baris), jpByCount = 6, jpByTime = 6. Maka diambil 6.
    //         $finalJP = max($jpByTime, $jpByCount);

    //         // Validasi minimal 1 JP
    //         $schedule->calculated_jp = $finalJP > 0 ? $finalJP : 1;

    //         // Set jam tampilan
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i');
    //             $schedule->end_time = $maxEnd->format('H:i');
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan berdasarkan Hari
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // Nomor Surat
    //     $bulanRomawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
    //     $currMonth = date('n');
    //     $romawi = $bulanRomawi[$currMonth] ?? 'I';
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/" . $romawi . "/" . date('Y');



    //     return compact('teacher', 'schedules', 'semester', 'tahunAjaran', 'totalJam', 'nomorSurat');
    // }

    // * HELPER: MEMPROSES DATA JADWAL (GROUPING, HITUNG JAM, & PENGURANGAN ISTIRAHAT)
    // private function processSuratTugasData($teacher, $rawSchedules)
    // {
    //     // 1. Tentukan Semester
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 2. Grouping Jadwal
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     // Definisi Jam Istirahat (Start - End)
    //     $breaks = [
    //         ['start' => '10:00:00', 'end' => '10:15:00'], // Istirahat Pagi (15m)
    //         ['start' => '12:30:00', 'end' => '13:15:00'], // Ishoma Siang (45m)
    //         ['start' => '15:30:00', 'end' => '15:45:00'], // Istirahat Sore (15m)
    //     ];

    //     foreach ($groupedSchedules as $group) {
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;

    //         // --- FITUR NAMA RUANGAN ---
    //         $rooms = $group->map(function($item) {
    //             return $item->room->code ?? $item->room ?? null;
    //         })
    //         ->filter(function($value) { return !empty($value); })
    //         ->unique()
    //         ->implode(', ');

    //         $schedule->merged_room = $rooms ?: '-';

    //         // --- CARI DURASI TOTAL (Start Paling Awal - End Paling Akhir) ---
    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;
    //             }
    //         }

    //         // --- HITUNG JAM PELAJARAN (JP) ---
    //         if ($minStart && $maxEnd) {
    //             // 1. Hitung durasi kotor (dalam menit)
    //             $rawMinutes = $maxEnd->diffInMinutes($minStart);
    //             $deductionMinutes = 0;

    //             // 2. Cek Overlap dengan Jam Istirahat
    //             foreach ($breaks as $break) {
    //                 // Buat objek Carbon untuk jam istirahat pada tanggal yang sama dengan jadwal
    //                 $breakStart = $minStart->copy()->setTimeFromTimeString($break['start']);
    //                 $breakEnd   = $minStart->copy()->setTimeFromTimeString($break['end']);

    //                 // Logika Overlap: Max(StartA, StartB) < Min(EndA, EndB)
    //                 $overlapStart = $minStart->greaterThan($breakStart) ? $minStart : $breakStart;
    //                 $overlapEnd   = $maxEnd->lessThan($breakEnd) ? $maxEnd : $breakEnd;

    //                 if ($overlapStart->lessThan($overlapEnd)) {
    //                     // Ada irisan waktu, hitung durasinya
    //                     $deductionMinutes += $overlapEnd->diffInMinutes($overlapStart);
    //                 }
    //             }

    //             // 3. Kurangi durasi istirahat dari durasi total
    //             $netMinutes = $rawMinutes - $deductionMinutes;

    //             // 4. Bagi 45 menit (Asumsi 1 JP = 45 menit)
    //             // Menggunakan round agar pembulatan presisi (contoh: 44 menit dianggap 1 JP)
    //             $jp = round($netMinutes / 45);
    //             $schedule->calculated_jp = $jp > 0 ? $jp : 1;

    //             // Update jam tampilan
    //             $schedule->start_time = $minStart->format('H:i');
    //             $schedule->end_time = $maxEnd->format('H:i');
    //         } else {
    //             // Fallback jika tidak ada waktu
    //             $schedule->calculated_jp = $group->count();
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan berdasarkan Hari
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // Nomor Surat (Format Update)
    //     $bulanRomawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
    //     $currMonth = date('n');
    //     $romawi = $bulanRomawi[$currMonth] ?? 'I';
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/" . $romawi . "/" . date('Y');

    //     return compact('teacher', 'schedules', 'semester', 'tahunAjaran', 'totalJam', 'nomorSurat');
    // }


    // private function processSuratTugasData($teacher, $rawSchedules)
    // {
    //     // 1. Tentukan Semester
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 2. Grouping Jadwal
    //     $groupedSchedules = $rawSchedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     // Definisi Jam Istirahat (Start - End)
    //     $breaks = [
    //         ['start' => '10:00:00', 'end' => '10:15:00'], // Istirahat Pagi (15m)
    //         ['start' => '12:30:00', 'end' => '13:15:00'], // Ishoma Siang (45m)
    //         ['start' => '15:30:00', 'end' => '15:45:00'], // Istirahat Sore (15m)
    //     ];



    //     foreach ($groupedSchedules as $group) {
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;
    //         $totalMinutes = 0;

    //         // --- FITUR NAMA RUANGAN ---
    //         $rooms = $group->map(function($item) {
    //             return $item->room->code ?? $item->room ?? null;
    //         })
    //         ->filter(function($value) { return !empty($value); })
    //         ->unique()
    //         ->implode(', ');

    //         $schedule->merged_room = $rooms ?: '-';

    //         // --- HITUNG DURASI & JAM ---
    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 // Cari rentang waktu total untuk grup ini (untuk tampilan)
    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 // Akumulasi menit dari setiap sesi (Pastikan diffInMinutes positif)
    //                 // Jika data 07:45 - 12:30, diff = 285 menit
    //                 $minutes = $end->diffInMinutes($start);
    //                 $totalMinutes += abs($minutes);
    //             }
    //         }



    //         // OPSI 1: Hitung JP dari total durasi (45 menit = 1 JP)
    //         // Contoh: 285 menit / 45 = 6.33 -> round jadi 6
    //         $jpByTime = 0;
    //         if ($totalMinutes > 0) {
    //             // Gunakan round() atau floor() tergantung kebijakan sekolah.
    //             // Biasanya round() cukup aman. Jika ingin pembulatan ke bawah (agar tidak kelebihan), gunakan floor().
    //             $jpByTime = round($totalMinutes / 45);
    //         }

    //         // OPSI 2: Hitung JP dari jumlah baris data
    //         $jpByCount = $group->count();

    //         // SOLUSI: Ambil nilai terbesar
    //         // Jika data tersimpan sebagai "07:45-12:30" (1 baris), jpByCount = 1, jpByTime = 6. Maka diambil 6.
    //         // Jika data tersimpan per jam (6 baris), jpByCount = 6, jpByTime = 6. Maka diambil 6.
    //         $finalJP = max($jpByTime, $jpByCount);

    //         // Validasi minimal 1 JP
    //         $schedule->calculated_jp = $finalJP > 0 ? $finalJP : 1;

    //         // Set jam tampilan
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i');
    //             $schedule->end_time = $maxEnd->format('H:i');
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 6. Urutkan berdasarkan Hari
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // Nomor Surat
    //     $bulanRomawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
    //     $currMonth = date('n');
    //     $romawi = $bulanRomawi[$currMonth] ?? 'I';
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/" . $romawi . "/" . date('Y');



    //     return compact('teacher', 'schedules', 'semester', 'tahunAjaran', 'totalJam', 'nomorSurat');
    // }

    /**
     * HELPER: MEMPROSES DATA JADWAL (GROUPING & HITUNG JAM)
     * Menggunakan input Collection Schedules secara eksplisit (Bukan relasi)
     */


    /**
     * CETAK SURAT TUGAS SEMUA GURU (Batch)
     */
    // public function printAllSuratTugas()
    // {
    //     ini_set('max_execution_time', 600);
    //     ini_set('memory_limit', '512M');

    //     // Ambil semua guru yang punya jadwal
    //     $teachers = Teacher::whereHas('schedules')
    //                 ->with(['user', 'schedules.classroom', 'schedules.subject', 'schedules.room'])
    //                 ->get()
    //                 ->sortBy(function($t) {
    //                     return $t->user->name;
    //                 });

    //     // dd($teachers);

    //     $school = $this->getSchoolData();
    //     $allData = [];

    //     foreach($teachers as $teacher) {
    //         $allData[] = $this->processSuratTugasData($teacher);
    //     }

    //     $pdf = Pdf::loadView('pdf.surat_tugas_all', compact('allData', 'school'));

    //     $pdf->setPaper($school['paper_size'] ?? 'a4', 'portrait');
    //     $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

    //     return $pdf->stream('Surat_Tugas_Semua_Guru.pdf');
    // }

    /**
     * HELPER: MEMPROSES DATA JADWAL (GROUPING & HITUNG JAM)
     * Digunakan oleh printSuratTugas dan printAllSuratTugas
     */
    // private function processSuratTugasData($teacher)
    // {
    //     // 1. Tentukan Semester
    //     $bulan = date('n');
    //     $tahun = date('Y');
    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunAjaran = $tahun . "/" . ($tahun + 1);
    //     } else {
    //         $semester = "Genap";
    //         $tahunAjaran = ($tahun - 1) . "/" . $tahun;
    //     }

    //     // 2. Grouping Jadwal (Hari - Kelas - Mapel)
    //     $groupedSchedules = $teacher->schedules->groupBy(function($item) {
    //         return strtolower(trim($item->day)) . '-' . $item->classroom_id . '-' . $item->subject_id;
    //     });

    //     $finalSchedules = collect();
    //     $totalJam = 0;

    //     foreach ($groupedSchedules as $group) {
    //         $schedule = $group->first();

    //         $minStart = null;
    //         $maxEnd = null;
    //         $totalMinutes = 0;

    //         // --- FITUR NAMA RUANGAN ---
    //         $rooms = $group->map(function($item) {
    //             return $item->room->name ?? $item->room ?? null;
    //         })
    //         ->filter(function($value) { return !empty($value); })
    //         ->unique()
    //         ->implode(', ');

    //         $schedule->merged_room = $rooms ?: '-';

    //         // --- HITUNG DURASI ---
    //         foreach ($group as $item) {
    //             if ($item->start_time && $item->end_time) {
    //                 $start = Carbon::parse($item->start_time);
    //                 $end = Carbon::parse($item->end_time);

    //                 if (is_null($minStart) || $start->lt($minStart)) $minStart = $start;
    //                 if (is_null($maxEnd) || $end->gt($maxEnd)) $maxEnd = $end;

    //                 $totalMinutes += $end->diffInMinutes($start);
    //             }
    //         }

    //         // Konversi ke JP (45 menit = 1 JP)
    //         if ($totalMinutes > 0) {
    //             $jp = round($totalMinutes / 45);
    //             $schedule->calculated_jp = $jp > 0 ? $jp : 1;
    //         } else {
    //             $schedule->calculated_jp = $group->count();
    //         }

    //         // Set Jam Tampilan
    //         if ($minStart && $maxEnd) {
    //             $schedule->start_time = $minStart->format('H:i');
    //             $schedule->end_time = $maxEnd->format('H:i');
    //         }

    //         $totalJam += $schedule->calculated_jp;
    //         $finalSchedules->push($schedule);
    //     }

    //     // 3. Sorting
    //     $schedules = $finalSchedules->sortBy(function($schedule) {
    //         $dayMap = [
    //             'monday' => 1, 'senin' => 1,
    //             'tuesday' => 2, 'selasa' => 2,
    //             'wednesday' => 3, 'rabu' => 3,
    //             'thursday' => 4, 'kamis' => 4,
    //             'friday' => 5, 'jumat' => 5,
    //             'saturday' => 6, 'sabtu' => 6,
    //             'sunday' => 7, 'minggu' => 7
    //         ];
    //         $dayIndex = $dayMap[strtolower(trim($schedule->day))] ?? 8;
    //         return sprintf('%02d-%s', $dayIndex, $schedule->start_time);
    //     });

    //     // 4. Nomor Surat Romawi
    //     $bulanRomawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
    //     $currMonth = date('n');
    //     $romawi = $bulanRomawi[$currMonth] ?? 'I';
    //     $nomorSurat = "800.1.11.1/002/SMKN1 BKT/" . $romawi . "/" . date('Y');

    //     return compact('teacher', 'schedules', 'semester', 'tahunAjaran', 'totalJam', 'nomorSurat');
    // }
    /**
     * Private Helper: Get School Data from Settings
     */
    /**
     * Helper Private: Ambil Data Sekolah dari Settings
     */
    // private function getSchoolData()
    // {
    //     return [
    //         // Identitas Sekolah
    //         'name'       => Setting::value('school_name', 'SMK DEFAULT'),
    //         'address'    => Setting::value('school_address', 'Alamat Sekolah'),
    //         'phone'      => Setting::value('school_phone', '-'),
    //         'web'        => Setting::value('school_web', '-'),
    //         'email'      => Setting::value('school_email', '-'),
    //         'logo_left'  => Setting::value('logo_left'),
    //         'logo_right' => Setting::value('logo_right'),

    //         // Pengaturan Kertas
    //         'paper_size'        => Setting::value('paper_size', 'a4'),
    //         'paper_orientation' => Setting::value('paper_orientation', 'portrait'),

    //         // Pengaturan Margin (Tambahkan satuan cm/mm untuk CSS)
    //         'margin_top'    => Setting::value('margin_top', '2.5') . 'cm',
    //         'margin_right'  => Setting::value('margin_right', '2.5') . 'cm',
    //         'margin_bottom' => Setting::value('margin_bottom', '2.5') . 'cm',
    //         'margin_left'   => Setting::value('margin_left', '2.5') . 'cm',

    //         // Tanda Tangan
    //         'sign_city'  => Setting::value('signature_city', 'Jakarta'),
    //         'sign_title' => Setting::value('signature_title', 'Kepala Sekolah'),
    //         'sign_name'  => Setting::value('signature_name', 'Administrator'),
    //         'sign_nip'   => Setting::value('signature_nip', '-'),
    //     ];
    // }

    private function getSchoolData()
    {
        // Helper untuk membersihkan path (jika DB menyimpan 'storage/settings/logo.png', kita ubah jadi 'settings/logo.png')
        // Ini agar public_path('storage/' . $val) tidak menjadi 'public/storage/storage/...'
        $cleanPath = function($val) {
            if (!$val) return null;
            return str_replace('storage/', '', $val);
        };

        // Helper untuk convert image ke base64
        $imageToBase64 = function($path) use ($cleanPath) {
            $cleaned = $cleanPath($path);
            if (!$cleaned || $cleaned == '-') return null;

            $fullPath = storage_path('app/public/' . $cleaned);
            if (!file_exists($fullPath)) {
                $fullPath = public_path('storage/' . $cleaned);
            }

            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            return null;
        };

        return [
            'school_name'    => Setting::value('school_name', 'SMK DEFAULT'),
            'provinsi_name'    => Setting::value('provinsi_name', 'PROVINSI DEFAULT'),
            'school_address' => Setting::value('school_address', 'Alamat Sekolah'),
            'school_phone'   => Setting::value('school_phone', '-'),
            'school_web'     => Setting::value('school_web', '-'),
            'school_email'   => Setting::value('school_email', '-'),
            'logo_left'      => $imageToBase64(Setting::value('logo_left')),
            'logo_left_st'      => $imageToBase64(Setting::value('logo_left_st')),
            'logo_right'     => $imageToBase64(Setting::value('logo_right')),
            'logo_right_st'     => $imageToBase64(Setting::value('logo_right_st')),
            'paper_size'        => Setting::value('paper_size', 'a4'),
            'paper_orientation' => Setting::value('paper_orientation', 'portrait'),
            'margin_top'    => Setting::value('margin_top', '2.5') . 'cm',
            'margin_right'  => Setting::value('margin_right', '2.5') . 'cm',
            'margin_bottom' => Setting::value('margin_bottom', '2.5') . 'cm',
            'margin_left'   => Setting::value('margin_left', '2.5') . 'cm',
            'sign_city'  => Setting::value('signature_city', 'Jakarta'),
            'sign_title' => Setting::value('signature_title', 'Kepala Sekolah'),
            'sign_name'  => Setting::value('signature_name', 'Administrator'),
            'sign_nip'   => Setting::value('signature_nip', '-'),
            'sign_image' => $imageToBase64(Setting::value('signature_image')),
            'stempel' => $imageToBase64(Setting::value('stempel')),
            'ttd_pejabat' => $imageToBase64(Setting::value('ttd_pejabat')),
            'nip_surat'   => Setting::value('nip_surat', '-'),
            'ttd_surat'   => Setting::value('ttd_surat', '-'),
            'nomor_surat'   => Setting::value('nomor_surat', '-'),
            'pejabat'   => Setting::value('pejabat', '-'),
            'lakasi'   => Setting::value('lokasi', '-'),
            'tanggal_surat'   => Setting::value('tanggal_surat', '-'),
        ];
    }

    public function cetakLaporan(Request $request)
    // {
    //     // Set lokalisasi ke bahasa Indonesia agar nama hari menjadi Senin, Selasa, dst.
    //     Carbon::setLocale('id');

    //     // Gunakan parameter start_date jika ada, atau gunakan default 02 Maret 2026
    //     $inputDate = $request->input('start_date', '2026-03-02');
    //     $startDate = Carbon::parse($inputDate)->startOfWeek(); // Pastikan selalu dimulai dari Senin

    //     $days = [];
    //     // Looping untuk mendapatkan 5 hari (Senin - Jumat)
    //     for ($i = 0; $i < 5; $i++) {
    //         $days[] = $startDate->copy()->addDays($i);
    //     }

    //     // Query data siswa beserta kelas dan absensi pada rentang tanggal tersebut
    //     $students = Student::with(['classroom', 'dailyAttendances' => function($query) use ($days) {
    //         $query->whereBetween('date', [
    //             $days[0]->format('Y-m-d'),
    //             $days[4]->format('Y-m-d')
    //         ]);
    //     }])
    //     ->whereHas('classroom', function($query) {
    //         $query->where('name', 'X TITL 2');
    //     })
    //     ->orderBy('name', 'asc')
    //     ->get();

    //     return view('report.kegiatan', compact('students', 'days'));
    // }

    // {
    //     Carbon::setLocale('id');

    //     // Ambil semua data kelas untuk ditampilkan di dropdown form
    //     $classrooms = Classroom::orderBy('name', 'asc')->get();

    //     // Tangkap input filter dari user
    //     $classroomId = $request->input('classroom_id');
    //     // Default tanggal menggunakan hari ini, lalu dikunci ke awal minggu (Senin)
    //     $inputDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));

    //     $startDate = Carbon::parse($inputDate)->startOfWeek();

    //     $days = [];
    //     for ($i = 0; $i < 5; $i++) {
    //         $days[] = $startDate->copy()->addDays($i);
    //     }

    //     $students = collect(); // Koleksi kosong sebagai default
    //     $selectedClassroom = null;

    //     // Jika user sudah memilih kelas dan menekan tombol filter
    //     if ($classroomId) {
    //         $selectedClassroom = Classroom::find($classroomId);

    //         $students = Student::with(['classroom', 'dailyAttendances' => function($query) use ($days) {
    //             $query->whereBetween('date', [
    //                 $days[0]->format('Y-m-d'),
    //                 $days[4]->format('Y-m-d')
    //             ]);
    //         }])
    //         ->where('classroom_id', $classroomId) // Filter berdasarkan ID Kelas yang dipilih
    //         ->orderBy('name', 'asc')
    //         ->get();
    //     }

    //     return view('report.kegiatan', compact(
    //         'classrooms', 'students', 'days', 'classroomId', 'inputDate', 'selectedClassroom'
    //     ));
    // }

    // {
    //     // Set lokalisasi ke bahasa Indonesia
    //     Carbon::setLocale('id');

    //     // Ambil semua data kelas untuk ditampilkan di dropdown form
    //     $classrooms = Classroom::orderBy('name', 'asc')->get();

    //     // Tangkap input filter dari user
    //     $classroomId = $request->input('classroom_id');
    //     // Default tanggal menggunakan hari ini, lalu dikunci ke awal minggu (Senin)
    //     $inputDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));

    //     $startDate = Carbon::parse($inputDate)->startOfWeek();

    //     $days = [];
    //     // Looping untuk mendapatkan 5 hari (Senin - Jumat)
    //     for ($i = 0; $i < 5; $i++) {
    //         $days[] = $startDate->copy()->addDays($i);
    //     }

    //     $students = collect();
    //     $selectedClassroom = null;

    //     // Jika user sudah memilih kelas
    //     if ($classroomId) {
    //         // Eager load relasi guru BK dan Wali Kelas
    //         $selectedClassroom = Classroom::with(['homeroomTeacher', 'counselingTeacher'])->find($classroomId);

    //         // Query data siswa beserta kelas dan absensinya
    //         $students = Student::with(['classroom', 'dailyAttendances' => function($query) use ($days) {
    //             $query->whereBetween('date', [
    //                 $days[0]->format('Y-m-d'),
    //                 $days[4]->format('Y-m-d')
    //             ]);
    //         }])
    //         ->where('classroom_id', $classroomId)
    //         ->orderBy('name', 'asc')
    //         ->get();
    //     }

    //     return view('report.kegiatan', compact(
    //         'classrooms', 'students', 'days', 'classroomId', 'inputDate', 'selectedClassroom'
    //     ));
    // }

    {
        // =======================================================
        // SECURITY CHECK: Blokir jika yang login adalah siswa
        // =======================================================
        if (Auth::check() && Auth::user()->jenis_user == 'siswa') {
            // Langsung lemparkan ke halaman 403 Forbidden bawaan Laravel
            abort(403, 'Akses Ditolak: Halaman ini hanya untuk Guru dan Admin.');
        }
        // =======================================================
        // Set lokalisasi ke bahasa Indonesia
        Carbon::setLocale('id');

        // Default tanggal menggunakan hari ini, lalu dikunci ke awal minggu (Senin)
        $inputDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));

        // === JIKA REQUEST DATANG DARI AJAX (Saat User Memilih Kelas) ===
        if ($request->ajax()) {
            $classroomId = $request->input('classroom_id');
            $startDate = Carbon::parse($inputDate)->startOfWeek();

            $days = [];
            for ($i = 0; $i < 5; $i++) {
                $days[] = $startDate->copy()->addDays($i);
            }

            $students = collect();
            $selectedClassroom = null;

            if ($classroomId) {
                // Eager load relasi guru
                $selectedClassroom = Classroom::with(['homeroomTeacher', 'counselingTeacher'])->find($classroomId);

                // Query data siswa dan absensi
                $students = Student::with(['classroom', 'dailyAttendances' => function($query) use ($days) {
                    $query->whereBetween('date', [
                        $days[0]->format('Y-m-d'),
                        $days[4]->format('Y-m-d')
                    ]);
                }])
                ->where('classroom_id', $classroomId)
                ->orderBy('name', 'asc')
                ->get();
            }

            // Kembalikan HANYA view tabel
            return view('report.table_kegiatan', compact('students', 'days', 'selectedClassroom'));
        }

        // === JIKA REQUEST BIASA (Loading Halaman Pertama Kali) ===
        $classrooms = Classroom::orderBy('name', 'asc')->get();
        return view('report.kegiatan', compact('classrooms', 'inputDate'));
    }
}
