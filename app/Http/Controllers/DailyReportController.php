<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Classroom;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DailyReportController extends Controller
{
    // ... method reportDaily ...
    public function reportDaily(Request $request)
    {
        // 1. Ambil data siswa untuk dropdown filter
        $classrooms = Classroom::orderBy('name')->get();
        $students = Student::orderBy('name', 'asc')->get();

        // 2. Query Builder Dasar
        $query = DailyAttendance::with(['student', 'student.classroom']);

        // --- FILTER 1: SPESIFIK SISWA ---
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // --- FILTER 2: PERIODE WAKTU ---
        $filterType = $request->filter_type ?? 'harian';

        switch ($filterType) {
            case 'harian':
                $date = $request->date ?? now()->format('Y-m-d');
                $query->whereDate('created_at', $date);
                break;

            case 'mingguan':
                $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfWeek();
                $endDate   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfWeek();
                $query->whereBetween('created_at', [$startDate, $endDate]);
                break;

            case 'bulanan':
                $month = $request->month ?? now()->month;
                $year  = $request->year ?? now()->year;
                $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
                break;

            case 'semester':
                $year = $request->year ?? now()->year;
                $semester = $request->semester;
                if ($semester == 'ganjil') {
                    $start = Carbon::create($year, 7, 1)->startOfDay();
                    $end   = Carbon::create($year, 12, 31)->endOfDay();
                } else {
                    $start = Carbon::create($year, 1, 1)->startOfDay();
                    $end   = Carbon::create($year, 6, 30)->endOfDay();
                }
                $query->whereBetween('created_at', [$start, $end]);
                break;
        }

        // 3. Urutkan data
        $attendances = $query->latest()->get();

        // 4. Hitung Ringkasan
        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin'  => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
        ];

        return view('daily_attendance.reports.report', compact('attendances', 'students', 'summary', 'classrooms'));
    }

    public function printAbsensi(Request $request)
    {
        $startDate = null;
        $endDate = null;
        $labelPeriode = "";
        $id = Auth::user()->jenis_user;

        // AMBIL DATA SEKOLAH (UPDATED KEY MAPPING)
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
                    $startDate = $year . '-07-01';
                    $endDate   = $year . '-12-31';
                    $labelPeriode = "Semester Ganjil T.A $year/" . ($year+1);
                } else {
                    $startDate = ($year + 1) . '-01-01';
                    $endDate   = ($year + 1) . '-06-30';
                    $labelPeriode = "Semester Genap T.A $year/" . ($year+1);
                }
                break;
        }

        // QUERY DATA ABSENSI
        $query = DailyAttendance::with(['student', 'student.classroom'])
            ->orderBy('date', 'asc')
            ->orderBy('arrival_time', 'asc');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $labelTambahan = null;
        // Filter Per Kelas
        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });

            $kelas = Classroom::find($request->classroom_id);
            if ($kelas) {
                $labelTambahan = "Kelas: " . $kelas->name;
            }
        }

        $attendances = $query->get();

        if ($attendances->isEmpty()) {
            return redirect()->back()->with('error', 'Data absensi tidak ditemukan pada periode/filter yang dipilih.');
        }

        // GENERATE PDF (Menggunakan view 'pdf.recap' yang sudah diperbaiki)
        $pdf = Pdf::loadView('daily_attendance.reports.pdf_view', compact(
            'attendances',
            'labelPeriode',
            'labelTambahan',
            'startDate',
            'endDate',
            'id',
            'school'
        ));

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
            'chroot' => public_path(),
        ]);

        $pdf->setPaper($school['paper_size'], $school['paper_orientation']);

        return $pdf->stream('Laporan-Kehadiran.pdf');
    }

    public function printStudentAbsensi($id)
    {
        $student = Student::with('classroom')->findOrFail($id);

        $attendances = DailyAttendance::with(['student'])
                        ->where('student_id', $id)
                        ->orderBy('date', 'desc')
                        ->orderBy('arrival_time', 'desc')
                        ->get();

        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
            'total' => $attendances->count()
        ];

        $startDate = $attendances->last()->date ?? date('Y-m-d');
        $endDate = $attendances->first()->date ?? date('Y-m-d');

        $school = $this->getSchoolData();

        // Gunakan view recap yang sama atau view student_history jika berbeda
        $pdf = Pdf::loadView('daily_attendance.reports.student_history', compact(
            'student',
            'attendances',
            'summary',
            'startDate',
            'endDate',
            'school'
        ));

        // FIX: Tambahkan options yang sama
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
            'chroot' => public_path(),
        ]);

        $pdf->setPaper($school['paper_size'], $school['paper_orientation']);

        return $pdf->stream('Laporan-Siswa-' . $student->name . '.pdf');
    }

    // --- HELPER UNTUK MENGUBAH GAMBAR JADI BASE64 AGAR TAMPIL DI PDF ---
    private function imageToBase64($path) {
        if (!$path || $path == '-') return null;

        // Bersihkan path jika ada prefix 'storage/' ganda
        $cleanPath = str_replace('storage/', '', $path);

        // Coba akses path fisik langsung (paling aman untuk DOMPDF)
        $fullPath = storage_path('app/public/' . $cleanPath);

        if (!file_exists($fullPath)) {
            // Fallback ke public path jika file tidak ketemu di storage/app
            $fullPath = public_path('storage/' . $cleanPath);
        }

        if (file_exists($fullPath)) {
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);
            $data = file_get_contents($fullPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        return null;
    }

    private function getSchoolData()
    {
        // MAPPING KUNCI AGAR SESUAI DENGAN VIEW PDF
        return [
            // Identitas Sekolah
            'school_name'    => Setting::value('school_name', 'SMK DEFAULT'),
            'school_address' => Setting::value('school_address', 'Alamat Sekolah'),
            'school_phone'   => Setting::value('school_phone', '-'),
            'school_web'     => Setting::value('school_web', '-'),
            'school_email'   => Setting::value('school_email', '-'),
            'provinsi_name'   => Setting::value('provinsi_name', '-'),

            // Convert Logo ke Base64
            'logo_left'      => $this->imageToBase64(Setting::value('logo_left')),
            'logo_right'     => $this->imageToBase64(Setting::value('logo_right')),

            // Pengaturan Kertas
            'paper_size'        => Setting::value('paper_size', 'a4'),
            'paper_orientation' => Setting::value('paper_orientation', 'portrait'),

            // Pengaturan Margin
            'margin_top'    => Setting::value('margin_top', '2.5') . 'cm',
            'margin_right'  => Setting::value('margin_right', '2.5') . 'cm',
            'margin_bottom' => Setting::value('margin_bottom', '2.5') . 'cm',
            'margin_left'   => Setting::value('margin_left', '2.5') . 'cm',

            // Tanda Tangan
            'sign_city'  => Setting::value('signature_city', 'Jakarta'),
            'sign_title' => Setting::value('signature_title', 'Kepala Sekolah'),
            'sign_name'  => Setting::value('signature_name', 'Administrator'),
            'sign_nip'   => Setting::value('signature_nip', '-'),
            'sign_image' => $this->imageToBase64(Setting::value('signature_image')), // Convert TTD ke Base64
        ];
    }
}
