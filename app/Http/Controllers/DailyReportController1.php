<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Classroom;
use Carbon\Carbon;
use App\Jobs\SendWhatsappJob; // Queue Job untuk WA
use App\Models\AttendanceSetting; // Jangan lupa import model ini
use Illuminate\Support\Facades\DB; // Tambahkan import DB
use Illuminate\Support\Facades\Auth;

class DailyReportController extends Controller
{
    public function reportDaily(Request $request)
    {
        // 1. Ambil data siswa untuk dropdown filter
        $classrooms = Classroom::orderBy('name')->get();
        $students = Student::orderBy('name', 'asc')->get();

        // 2. Query Builder Dasar
        // Load relasi 'student' dan 'classroom' agar tidak N+1 Query
        $query = DailyAttendance::with(['student', 'student.classroom']);

        // --- FILTER 1: SPESIFIK SISWA ---
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // --- FILTER 2: PERIODE WAKTU ---
        // Jika tidak ada filter, default ke hari ini
        $filterType = $request->filter_type ?? 'harian';

        switch ($filterType) {
            case 'harian':
                $date = $request->date ?? now()->format('Y-m-d');
                $query->whereDate('created_at', $date);
                break;

            case 'mingguan':
                // Menggunakan rentang tanggal manual (Start Date s/d End Date)
                $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfWeek();
                $endDate   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfWeek();

                $query->whereBetween('created_at', [$startDate, $endDate]);
                break;

            case 'bulanan':
                $month = $request->month ?? now()->month;
                $year  = $request->year ?? now()->year;

                $query->whereMonth('created_at', $month)
                      ->whereYear('created_at', $year);
                break;

            case 'semester':
                $year = $request->year ?? now()->year;
                $semester = $request->semester; // 'ganjil' atau 'genap'

                if ($semester == 'ganjil') {
                    // Semester Ganjil: 1 Juli - 31 Desember
                    $start = Carbon::create($year, 7, 1)->startOfDay();
                    $end   = Carbon::create($year, 12, 31)->endOfDay();
                } else {
                    // Semester Genap: 1 Januari - 30 Juni
                    $start = Carbon::create($year, 1, 1)->startOfDay();
                    $end   = Carbon::create($year, 6, 30)->endOfDay();
                }

                $query->whereBetween('created_at', [$start, $end]);
                break;
        }

        // 3. Urutkan data: Terbaru di atas
        $attendances = $query->latest()->get();

        // 4. Hitung Ringkasan (Opsional, untuk header laporan)
        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin'  => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
        ];

        return view('daily_attendance.reports.report', compact('attendances', 'students', 'summary', 'classrooms'));
    }

    // public function printAbsensi(Request $request)
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
    //     $query = DailyAttendance::with(['student'])
    //                     ->orderBy('date', 'asc')
    //                     ->orderBy('arrival_time', 'asc');

        


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

    //             // // ==========================================
    //     //  TAMBAHKAN VALIDASI INI
    //     // ==========================================
    //     if ($attendances->isEmpty()) {
    //         return redirect()->back()->with('error', 'Data absensi tidak ditemukan pada periode/filter yang dipilih.');
    //     }
    //     // ==========================================

    //     // GENERATE PDF
    //     $pdf = Pdf::loadView('daily_attendance.reports.pdf_view', compact(
    //         'attendances',
    //         'labelPeriode',
    //         'startDate',
    //         'endDate',
    //         'school'));
    //     $pdf->setPaper($school['paper_size'], $school['paper_orientation']);

    //     return $pdf->stream('Laporan-Kehadiran.pdf');
    // }

    public function printAbsensi(Request $request)
    {
        $startDate = null;
        $endDate = null;
        $labelPeriode = "";
        $id = Auth::user()->jenis_user;
        $school = $this->getSchoolData();
        

        // LOGIKA PENENTUAN TANGGAL
        switch ($request->periode) {
            case 'harian':
                $startDate = $request->tanggal;
                $endDate = $request->tanggal;
                $labelPeriode = "Harian (" . Carbon::parse($startDate)->translatedFormat('d F Y') . ")";
                break;

            case 'mingguan':
                $request->validate([
                    'start_date' => 'required|date',
                    'end_date'   => 'required|date|after_or_equal:start_date',
                ]);
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $labelPeriode = "Mingguan (" . Carbon::parse($startDate)->format('d/m') . " - " . Carbon::parse($endDate)->format('d/m/Y') . ")";
                break;

            case 'bulanan':
                $month = $request->bulan;
                $year = $request->tahun_bulan;

                $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

                $labelPeriode = "Bulan " . Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
                break;

            case 'semester':
                $year = $request->tahun_semester;
                if ($request->semester == 'ganjil') {
                    // Ganjil: Juli Tahun Ini - Desember Tahun Ini
                    $startDate = $year . '-07-01';
                    $endDate   = $year . '-12-31';
                    $labelPeriode = "Semester Ganjil T.A $year/" . ($year+1);
                } else {
                    // Genap: Januari Tahun Depan - Juni Tahun Depan
                    $startDate = ($year + 1) . '-01-01';
                    $endDate   = ($year + 1) . '-06-30';
                    $labelPeriode = "Semester Genap T.A $year/" . ($year+1);
                }
                break;
        }


        // 2. AMBIL DATA SEKOLAH (KOP SURAT)
       

        // 3. QUERY DATA ABSENSI (DENGAN FILTER KELAS DAN TANGGAL)
        $query = DailyAttendance::with(['student'])
            ->orderBy('date', 'asc')
            ->orderBy('arrival_time', 'asc');

        // ===========================================
        // !!! INI BAGIAN YANG DITAMBAHKAN !!!
        // ===========================================
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        // ===========================================


        $labelTambahan = null;
        // --- TAMBAHAN: Filter Per Kelas ---
        if ($request->filled('classroom_id')) {
            // Filter hanya siswa yang berada di kelas yang dipilih
            $query->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });

            // Ambil nama kelas untuk judul PDF
            $kelas = Classroom::find($request->classroom_id);
            if ($kelas) {
                $labelTambahan = "Kelas: " . $kelas->name;
            }
        }
        // ----------------------------------
        $attendances = $query->get();
        

        // ==========================================
        // TAMBAHKAN VALIDASI INI
        // ==========================================
        if ($attendances->isEmpty()) {
            return redirect()->back()->with('error', 'Data absensi tidak ditemukan pada periode/filter yang dipilih.');
        }
        // ==========================================
         
        // $school = get_school_data();
        // GENERATE PDF
        $pdf = Pdf::loadView('daily_attendance.reports.pdf_view', compact(
            'attendances',
            'labelPeriode',
            'startDate',
            'endDate',
            'id',
            'school'));
        $pdf->setOptions(['isPhpEnabled' => true]);
        $pdf->setPaper($school['paper_size'], $school['paper_orientation']);

        return $pdf->stream('Laporan-Kehadiran.pdf');
    }

    public function printStudentAbsensi($id)
    {
        // 1. Fetch Student Data
        $student = Student::with('classroom')->findOrFail($id);

        // 2. Fetch Attendance History
        $attendances = DailyAttendance::with(['student'])
                        ->where('student_id', $id)
                        ->orderBy('date', 'desc')
                        ->orderBy('arrival_time', 'desc')
                        ->get();

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
        $school = get_school_data();

        // 6. Generate PDF using specific view
        $pdf = Pdf::loadView('daily_attendance.reports.student_history', compact(
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

    private function getSchoolData()
    {
        return [
            // Identitas Sekolah
            'name'       => Setting::value('school_name', 'SMK DEFAULT'),
            'address'    => Setting::value('school_address', 'Alamat Sekolah'),
            'phone'      => Setting::value('school_phone', '-'),
            'web'        => Setting::value('school_web', '-'),
            'email'      => Setting::value('school_email', '-'),
            'logo_left'  => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),

            // Pengaturan Kertas
            'paper_size'        => Setting::value('paper_size', 'a4'),
            'paper_orientation' => Setting::value('paper_orientation', 'portrait'),

            // Pengaturan Margin (Tambahkan satuan cm/mm untuk CSS)
            'margin_top'    => Setting::value('margin_top', '2.5') . 'cm',
            'margin_right'  => Setting::value('margin_right', '2.5') . 'cm',
            'margin_bottom' => Setting::value('margin_bottom', '2.5') . 'cm',
            'margin_left'   => Setting::value('margin_left', '2.5') . 'cm',

            // Tanda Tangan
            'sign_city'  => Setting::value('signature_city', 'Jakarta'),
            'sign_title' => Setting::value('signature_title', 'Kepala Sekolah'),
            'sign_name'  => Setting::value('signature_name', 'Administrator'),
            'sign_nip'   => Setting::value('signature_nip', '-'),
            'sign_image'  => Setting::value('signature_image', '-'),
        ];
    }
}
