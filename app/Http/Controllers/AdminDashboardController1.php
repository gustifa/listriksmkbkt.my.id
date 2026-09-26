<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\Schedule;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. DATA PENGGUNA UTAMA
        $countUser    = User::count();
        $countTeacher = Teacher::count();
        $countStudent = Student::count();
        $countPiket   = User::where('jenis_user', 'piket')->count();
        $countAdmin   = User::where('jenis_user', 'admin')->count();
        $countGuru   = User::where('jenis_user', 'guru')->count();
        $countAllTeacher =  $countTeacher+$countPiket+$countAdmin;

        // 2. DATA AKADEMIK
        $countMapel   = Subject::count();
        
        // Hitung Kelas
        $countKelasTotal = Classroom::count();
        $countKelasIsi   = Classroom::has('students')->count(); // Kelas ada siswanya
        $countKelasKosong = $countKelasTotal - $countKelasIsi; // Kelas tidak ada siswanya

        $countJurusan = Major::count();
        $countJadwal  = Schedule::count();

        // 3. RINGKASAN ABSENSI HARI INI (Gerbang)
        $today = Carbon::today();
        $countPresensi = DailyAttendance::whereDate('date', $today)->count();
        $countPresensiMasuk = DailyAttendance::whereDate('date', $today)->whereNotNull('arrival_time')->count();
        $countPresensiPulang = DailyAttendance::whereDate('date', $today)->whereNotNull('departure_time')->count();

        // 4. RINGKASAN ABSENSI PEMBELAJARAN (Mapel)
        $countMapelHadir = Attendance::whereDate('created_at', $today)->whereIn('status', ['hadir', 'terlambat'])->count();

        // --- DATA GRAFIK (STATISTIK) ---
        // Filter Periode (Default: Harian/7 hari terakhir)
        $filter = $request->get('filter', 'harian');
        
        $chartData = $this->getChartData($filter);
        $chartDataGerbang = $this->getChartDataGerbang($filter);
        $pieData = $this->getPieData($filter);
        $pieDataGerbang = $this->getPieDataGerbang($filter);

        return view('admin.dashboard', compact(
            'countUser', 'countTeacher', 'countStudent', 'countPiket', 'countAdmin', 'countAllTeacher','countGuru',
            'countMapel', 'countKelasTotal', 'countKelasIsi', 'countKelasKosong',
            'countJurusan', 'countJadwal',
            'countPresensi', 'countPresensiMasuk', 'countPresensiPulang',
            'countMapelHadir',
            'chartData', 'pieData', 'filter', 'pieDataGerbang', 'chartDataGerbang'
        ));
    }

    // Helper untuk Data Grafik Batang (Tren)
    private function getChartData($filter)
    {
        $labels = [];
        $dataHadir = [];
        $dataTerlambat = [];
        $dataAlpa = [];

        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(6); // Default 7 hari

        if ($filter == 'mingguan') {
            $startDate = $endDate->copy()->subWeeks(4); // 4 Minggu terakhir
        } elseif ($filter == 'bulanan') {
            $startDate = $endDate->copy()->subMonths(6); // 6 Bulan terakhir
        } elseif ($filter == 'tahunan') {
            $startDate = $endDate->copy()->subYears(5); // 5 Tahun terakhir
        }

        // Logic Loop Tanggal (Sederhana) - Bisa dioptimalkan dengan Group By SQL
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            
            if ($filter == 'harian') {
                $label = $currentDate->format('D, d M');
                $queryDate = $currentDate->format('Y-m-d');
                $whereClause = "DATE(date) = '$queryDate'";
                $increment = 'addDay';
            } elseif ($filter == 'mingguan') {
                $label = 'Week ' . $currentDate->weekOfYear;
                $startWeek = $currentDate->copy()->startOfWeek()->format('Y-m-d');
                $endWeek = $currentDate->copy()->endOfWeek()->format('Y-m-d');
                $whereClause = "date BETWEEN '$startWeek' AND '$endWeek'";
                $increment = 'addWeek';
            } elseif ($filter == 'bulanan') {
                $label = $currentDate->format('M Y');
                $month = $currentDate->month;
                $year = $currentDate->year;
                $whereClause = "MONTH(date) = $month AND YEAR(date) = $year";
                $increment = 'addMonth';
            } else { // Tahunan
                $label = $currentDate->format('Y');
                $year = $currentDate->year;
                $whereClause = "YEAR(date) = $year";
                $increment = 'addYear';
            }

            $labels[] = $label;
            
            // Gunakan Raw Query untuk fleksibilitas filter (atau Eloquent whereRaw)
            // Catatan: Pastikan kolom 'date' ada di daily_attendances
            $dataHadir[] = Attendance::whereRaw($whereClause)->where('status', 'hadir')->count();
            $dataTerlambat[] = Attendance::whereRaw($whereClause)->where('status', 'terlambat')->count();
            $dataAlpa[] = Attendance::whereRaw($whereClause)->whereIn('status', ['alpa', 'alpha'])->count();

            $currentDate->$increment();
        }

        return [
            'labels' => $labels,
            'hadir' => $dataHadir,
            'terlambat' => $dataTerlambat,
            'alpa' => $dataAlpa
        ];
    }

    private function getChartDataGerbang($filter)
    {
        $labels = [];
        $dataHadir = [];
        $dataTerlambat = [];
        $dataAlpa = [];

        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(6); // Default 7 hari

        if ($filter == 'mingguan') {
            $startDate = $endDate->copy()->subWeeks(4); // 4 Minggu terakhir
        } elseif ($filter == 'bulanan') {
            $startDate = $endDate->copy()->subMonths(6); // 6 Bulan terakhir
        } elseif ($filter == 'tahunan') {
            $startDate = $endDate->copy()->subYears(5); // 5 Tahun terakhir
        }

        // Logic Loop Tanggal (Sederhana) - Bisa dioptimalkan dengan Group By SQL
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            
            if ($filter == 'harian') {
                $label = $currentDate->format('D, d M');
                $queryDate = $currentDate->format('Y-m-d');
                $whereClause = "DATE(date) = '$queryDate'";
                $increment = 'addDay';
            } elseif ($filter == 'mingguan') {
                $label = 'Week ' . $currentDate->weekOfYear;
                $startWeek = $currentDate->copy()->startOfWeek()->format('Y-m-d');
                $endWeek = $currentDate->copy()->endOfWeek()->format('Y-m-d');
                $whereClause = "date BETWEEN '$startWeek' AND '$endWeek'";
                $increment = 'addWeek';
            } elseif ($filter == 'bulanan') {
                $label = $currentDate->format('M Y');
                $month = $currentDate->month;
                $year = $currentDate->year;
                $whereClause = "MONTH(date) = $month AND YEAR(date) = $year";
                $increment = 'addMonth';
            } else { // Tahunan
                $label = $currentDate->format('Y');
                $year = $currentDate->year;
                $whereClause = "YEAR(date) = $year";
                $increment = 'addYear';
            }

            $labels[] = $label;
            
            // Gunakan Raw Query untuk fleksibilitas filter (atau Eloquent whereRaw)
            // Catatan: Pastikan kolom 'date' ada di daily_attendances
            $dataHadir[] = DailyAttendance::whereRaw($whereClause)->where('status', 'hadir')->count();
            $dataTerlambat[] = DailyAttendance::whereRaw($whereClause)->where('status', 'terlambat')->count();
            $dataAlpa[] = DailyAttendance::whereRaw($whereClause)->whereIn('status', ['alpa', 'alpha'])->count();

            $currentDate->$increment();
        }

        return [
            'labels' => $labels,
            'hadir' => $dataHadir,
            'terlambat' => $dataTerlambat,
            'alpa' => $dataAlpa
        ];
    }

    // Helper untuk Data Pie Chart (Akumulasi Periode)
    private function getPieData($filter)
    {
        $query = Attendance::query();
        
        $today = Carbon::today();

        if ($filter == 'harian') {
            $query->whereDate('date', $today);
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]);
        } elseif ($filter == 'bulanan') {
            $query->whereMonth('date', $today->month)->whereYear('date', $today->year);
        } elseif ($filter == 'semester') {
            $month = $today->month;
            if ($month >= 7) { // Semester Ganjil
                $query->whereBetween('date', [$today->year.'-07-01', $today->year.'-12-31']);
            } else { // Semester Genap
                $query->whereBetween('date', [$today->year.'-01-01', $today->year.'-06-30']);
            }
        } elseif ($filter == 'tahunan') {
            $query->whereYear('date', $today->year);
        }

        // Hitung total per status
        $counts = $query->select('status', DB::raw('count(*) as total'))
                       ->groupBy('status')
                       ->pluck('total', 'status')
                       ->toArray();

        // Normalisasi data (jika key tidak ada, isi 0)
        return [
            $counts['hadir'] ?? 0,      // Index 0
            $counts['terlambat'] ?? 0,  // Index 1
            $counts['sakit'] ?? 0,      // Index 2
            $counts['izin'] ?? 0,       // Index 3
            $counts['alpa'] ?? ($counts['alpha'] ?? 0) // Index 4
        ];
    }

    private function getPieDataGerbang($filter)
    {
        $query = DailyAttendance::query();
        
        $today = Carbon::today();

        if ($filter == 'harian') {
            $query->whereDate('date', $today);
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]);
        } elseif ($filter == 'bulanan') {
            $query->whereMonth('date', $today->month)->whereYear('date', $today->year);
        } elseif ($filter == 'semester') {
            $month = $today->month;
            if ($month >= 7) { // Semester Ganjil
                $query->whereBetween('date', [$today->year.'-07-01', $today->year.'-12-31']);
            } else { // Semester Genap
                $query->whereBetween('date', [$today->year.'-01-01', $today->year.'-06-30']);
            }
        } elseif ($filter == 'tahunan') {
            $query->whereYear('date', $today->year);
        }

        // Hitung total per status
        $counts = $query->select('status', DB::raw('count(*) as total'))
                       ->groupBy('status')
                       ->pluck('total', 'status')
                       ->toArray();

        // Normalisasi data (jika key tidak ada, isi 0)
        return [
            $counts['hadir'] ?? 0,      // Index 0
            $counts['terlambat'] ?? 0,  // Index 1
            $counts['sakit'] ?? 0,      // Index 2
            $counts['izin'] ?? 0,       // Index 3
            $counts['alpa'] ?? ($counts['alpha'] ?? 0) // Index 4
        ];
    }
}