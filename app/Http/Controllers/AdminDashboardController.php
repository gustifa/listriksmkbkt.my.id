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
use App\Models\Absensi;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'harian');
        $today = Carbon::today()->format('Y-m-d');
        $nowTime = Carbon::now()->format('H:i:s');

        // 1. DOKUMENTASI RENTANG TANGGAL BERDASARKAN FILTER PERIODE
        switch ($filter) {
            case 'mingguan':
                $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $endDate   = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'bulanan':
                $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $endDate   = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'harian':
            default:
                $startDate = $today;
                $endDate   = $today;
                break;
        }

        // Variabel Nama Hari untuk Jadwal
        $hariEnCapital = Carbon::now()->format('l');
        $hariEnLower   = strtolower($hariEnCapital);
        $hariId        = Carbon::now()->locale('id')->isoFormat('dddd');
        $hariIdLower   = strtolower($hariId);
        $variasiHari   = [$hariEnCapital, $hariEnLower, $hariId, $hariIdLower, ucfirst($hariIdLower)];

        // --- METRIK UTAMA ---
        $countUser = User::count();
        $countGuru = Teacher::count();
        $countPiket = 0; 
        $countAllTeacher = $countGuru;
        $countStudent = Student::count();

        // --- PRESENSI GERBANG (Tabel: daily_attendances) Sesuai Filter Periode ---
        $countPresensiMasuk = DailyAttendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('arrival_time')
            ->count();

        $countPresensiPulang = DailyAttendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('departure_time')
            ->count();

        $countPresensi = $countPresensiMasuk;

        // --- PRESENSI PBM / KELAS (Tabel: attendances) Sesuai Filter Periode ---
        $countMapelHadir = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('subject_id')
            ->whereIn('status', ['hadir', 'present', 'Hadir', 'Present'])
            ->count();

        // --- DATA AKADEMIK ---
        $countMapel = Subject::count();
        $countJurusan = Major::count();
        $countJadwal = Schedule::count();
        $countKelasTotal = Classroom::count();

        // --- STATUS JADWAL HARI INI ---
        $scheduleTodayQuery = Schedule::whereIn('day', $variasiHari);

        $countJadwalBerlangsung = (clone $scheduleTodayQuery)
            ->whereTime('start_time', '<=', $nowTime)
            ->whereTime('end_time', '>=', $nowTime)
            ->count();

        $countJadwalSelesai = (clone $scheduleTodayQuery)
            ->whereTime('end_time', '<', $nowTime)
            ->count();

        $countJadwalBelumMulai = (clone $scheduleTodayQuery)
            ->whereTime('start_time', '>', $nowTime)
            ->count();

        $countKelasIsi = (clone $scheduleTodayQuery)
            ->whereTime('start_time', '<=', $nowTime)
            ->whereTime('end_time', '>=', $nowTime)
            ->distinct('classroom_id')
            ->count('classroom_id');
            
        $countKelasKosong = max(0, $countKelasTotal - $countKelasIsi);

        $listRombelSummary = Classroom::with(['major', 'homeroomTeacher'])
            ->take(6)
            ->get();

        // --- DATA GRAFIK PIE SESUAI FILTER PERIODE ---
        $pieDataGerbang = [
            DailyAttendance::whereBetween('date', [$startDate, $endDate])->whereIn('status', ['hadir', 'present', 'Hadir'])->count(),
            DailyAttendance::whereBetween('date', [$startDate, $endDate])->whereIn('status', ['terlambat', 'late', 'Terlambat'])->count(),
            DailyAttendance::whereBetween('date', [$startDate, $endDate])->whereIn('status', ['sakit', 'sick', 'Sakit'])->count(),
            DailyAttendance::whereBetween('date', [$startDate, $endDate])->whereIn('status', ['izin', 'permit', 'Izin'])->count(),
            DailyAttendance::whereBetween('date', [$startDate, $endDate])->whereIn('status', ['alpha', 'absent', 'Alpha'])->count(),
        ];

        // --- DATA GRAFIK TREN (LINE CHART) ---
        if ($filter == 'mingguan') {
            $labels = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $hadirData = [];
            $terlambatData = [];
            
            for ($i = 0; $i < 6; $i++) {
                $dayDate = Carbon::now()->startOfWeek()->addDays($i)->format('Y-m-d');
                $hadirData[] = DailyAttendance::whereDate('date', $dayDate)->whereIn('status', ['hadir', 'present', 'Hadir'])->count();
                $terlambatData[] = DailyAttendance::whereDate('date', $dayDate)->whereIn('status', ['terlambat', 'late', 'Terlambat'])->count();
            }
        } elseif ($filter == 'bulanan') {
            $labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
            $hadirData = [
                DailyAttendance::whereBetween('date', [Carbon::now()->startOfMonth()->format('Y-m-d'), Carbon::now()->startOfMonth()->addDays(7)->format('Y-m-d')])->whereIn('status', ['hadir', 'present'])->count(),
                DailyAttendance::whereBetween('date', [Carbon::now()->startOfMonth()->addDays(8)->format('Y-m-d'), Carbon::now()->startOfMonth()->addDays(15)->format('Y-m-d')])->whereIn('status', ['hadir', 'present'])->count(),
                DailyAttendance::whereBetween('date', [Carbon::now()->startOfMonth()->addDays(16)->format('Y-m-d'), Carbon::now()->startOfMonth()->addDays(23)->format('Y-m-d')])->whereIn('status', ['hadir', 'present'])->count(),
                DailyAttendance::whereBetween('date', [Carbon::now()->startOfMonth()->addDays(24)->format('Y-m-d'), Carbon::now()->endOfMonth()->format('Y-m-d')])->whereIn('status', ['hadir', 'present'])->count(),
            ];
            $terlambatData = [5, 12, 8, 10]; // Contoh atau buatkan query dinamis serupa
        } else {
            // Harian (Jam Operational)
            $labels = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
            $hadirData = [];
            $terlambatData = [];
            foreach ($labels as $jam) {
                $hadirData[] = DailyAttendance::whereDate('date', $today)->whereTime('arrival_time', '<=', $jam)->whereIn('status', ['hadir', 'present', 'Hadir'])->count();
                $terlambatData[] = DailyAttendance::whereDate('date', $today)->whereTime('arrival_time', '<=', $jam)->whereIn('status', ['terlambat', 'late', 'Terlambat'])->count();
            }
        }

        $chartDataGerbang = [
            'labels' => $labels,
            'hadir' => $hadirData,
            'terlambat' => $terlambatData
        ];

        return view('admin.dashboard', compact(
            'filter', 'countUser', 'countGuru', 'countPiket', 'countAllTeacher',
            'countStudent', 'countPresensi', 'countPresensiMasuk', 'countPresensiPulang',
            'countMapelHadir', 'countMapel', 'countJurusan', 'countJadwal', 'countKelasTotal',
            'countKelasIsi', 'countKelasKosong', 'countJadwalBerlangsung', 'countJadwalSelesai',
            'countJadwalBelumMulai', 'listRombelSummary', 'chartDataGerbang', 'pieDataGerbang'
        ));
    }
    // public function index(Request $request)
    // {
    //     // 1. DATA PENGGUNA UTAMA
    //     $countUser    = User::count();
    //     $countTeacher = Teacher::count();
    //     $countStudent = Student::count();
    //     $countPiket   = User::where('jenis_user', 'piket')->count();
    //     $countAdmin   = User::where('jenis_user', 'admin')->count();
    //     $countGuru    = User::where('jenis_user', 'guru')->count();
    //     $countAllTeacher = $countTeacher + $countPiket + $countAdmin;

    //     // 2. DATA AKADEMIK
    //     $countMapel   = Subject::count();
    //     $countKelasTotal = Classroom::count();
    //     $countKelasIsi   = Classroom::has('students')->count();
    //     $countKelasKosong = $countKelasTotal - $countKelasIsi;

    //     $countJurusan = Major::count();
    //     $countJadwal  = Schedule::count();

    //     // 3. RINGKASAN ABSENSI HARI INI
    //     $today = Carbon::today();
    //     $countPresensi = DailyAttendance::whereDate('date', $today)->count();
    //     $countPresensiMasuk = DailyAttendance::whereDate('date', $today)->whereNotNull('arrival_time')->count();
    //     $countPresensiPulang = DailyAttendance::whereDate('date', $today)->whereNotNull('departure_time')->count();

    //     // 4. RINGKASAN ABSENSI PEMBELAJARAN
    //     $countMapelHadir = Attendance::whereDate('created_at', $today)
    //         ->whereIn('status', ['hadir', 'terlambat'])
    //         ->count();

    //     // --- DATA GRAFIK ---
    //     $filter = $request->get('filter', 'harian');
        
    //     $chartData = $this->getChartData($filter, Attendance::class);
    //     $chartDataGerbang = $this->getChartData($filter, DailyAttendance::class);
    //     $pieData = $this->getPieData($filter, Attendance::class);
    //     $pieDataGerbang = $this->getPieData($filter, DailyAttendance::class);

    //     return view('admin.dashboard', compact(
    //         'countUser', 'countTeacher', 'countStudent', 'countPiket', 'countAdmin', 'countAllTeacher','countGuru',
    //         'countMapel', 'countKelasTotal', 'countKelasIsi', 'countKelasKosong',
    //         'countJurusan', 'countJadwal',
    //         'countPresensi', 'countPresensiMasuk', 'countPresensiPulang',
    //         'countMapelHadir',
    //         'chartData', 'pieData', 'filter', 'pieDataGerbang', 'chartDataGerbang'
    //     ));
    // }

    // Helper refactored untuk mendukung PostgreSQL dan mengurangi duplikasi kode
    // private function getChartData($filter, $modelClass)
    // {
    //     $labels = []; $dataHadir = []; $dataTerlambat = []; $dataAlpa = [];
    //     $endDate = Carbon::now();
    //     $startDate = $endDate->copy()->subDays(6);

    //     if ($filter == 'mingguan') $startDate = $endDate->copy()->subWeeks(4);
    //     elseif ($filter == 'bulanan') $startDate = $endDate->copy()->subMonths(6);
    //     elseif ($filter == 'tahunan') $startDate = $endDate->copy()->subYears(5);

    //     $currentDate = $startDate->copy();
    //     while ($currentDate <= $endDate) {
    //         $query = $modelClass::query();

    //         if ($filter == 'harian') {
    //             $labels[] = $currentDate->format('D, d M');
    //             $query->whereDate('date', $currentDate->format('Y-m-d'));
    //             $currentDate->addDay();
    //         } elseif ($filter == 'mingguan') {
    //             $labels[] = 'W-' . $currentDate->weekOfYear;
    //             $query->whereBetween('date', [$currentDate->copy()->startOfWeek(), $currentDate->copy()->endOfWeek()]);
    //             $currentDate->addWeek();
    //         } elseif ($filter == 'bulanan') {
    //             $labels[] = $currentDate->format('M Y');
    //             // PERBAIKAN POSTGRES: Menggunakan whereMonth dan whereYear bawaan Laravel
    //             $query->whereMonth('date', $currentDate->month)
    //                   ->whereYear('date', $currentDate->year);
    //             $currentDate->addMonth();
    //         } else {
    //             $labels[] = $currentDate->format('Y');
    //             $query->whereYear('date', $currentDate->year);
    //             $currentDate->addYear();
    //         }

    //         // Kloning query untuk tiap status agar tidak menumpuk where-nya
    //         $dataHadir[] = (clone $query)->where('status', 'hadir')->count();
    //         $dataTerlambat[] = (clone $query)->where('status', 'terlambat')->count();
    //         $dataAlpa[] = (clone $query)->whereIn('status', ['alpa', 'alpha'])->count();
    //     }

    //     return ['labels' => $labels, 'hadir' => $dataHadir, 'terlambat' => $dataTerlambat, 'alpa' => $dataAlpa];
    // }

    private function getChartData($filter, $modelClass)
    {
        $labels = []; $dataHadir = []; $dataTerlambat = []; $dataAlpa = [];
        $endDate = Carbon::now()->endOfDay();
        
        // Tentukan titik awal dan interval perulangan
        if ($filter == 'harian') {
            $startDate = $endDate->copy()->subDays(6)->startOfDay();
            $increment = 'addDay';
        } elseif ($filter == 'mingguan') {
            $startDate = $endDate->copy()->subWeeks(4)->startOfWeek();
            $increment = 'addWeek';
        } elseif ($filter == 'bulanan') {
            $startDate = $endDate->copy()->subMonths(5)->startOfMonth(); // 6 bulan terakhir
            $increment = 'addMonth';
        } else { // tahunan
            $startDate = $endDate->copy()->subYears(4)->startOfYear(); // 5 tahun terakhir
            $increment = 'addYear';
        }

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Inisialisasi query baru di setiap putaran loop
            $query = $modelClass::query();

            if ($filter == 'harian') {
                $labels[] = $currentDate->translatedFormat('d M');
                $query->whereDate('date', $currentDate->format('Y-m-d'));
            } elseif ($filter == 'mingguan') {
                $start = $currentDate->copy()->startOfWeek();
                $end = $currentDate->copy()->endOfWeek();
                $labels[] = "Mgu " . $currentDate->weekOfYear;
                $query->whereBetween('date', [$start, $end]);
            } elseif ($filter == 'bulanan') {
                $labels[] = $currentDate->translatedFormat('F Y');
                // Gunakan whereRaw EXTRACT untuk PostgreSQL agar lebih akurat
                $query->whereRaw("EXTRACT(MONTH FROM date) = ?", [$currentDate->month])
                    ->whereRaw("EXTRACT(YEAR FROM date) = ?", [$currentDate->year]);
            } else { // tahunan
                $labels[] = $currentDate->format('Y');
                $query->whereRaw("EXTRACT(YEAR FROM date) = ?", [$currentDate->year]);
            }

            // Ambil data berdasarkan status
            // Menggunakan clone agar kondisi status tidak menumpuk
            $dataHadir[] = (clone $query)->where('status', 'hadir')->count();
            $dataTerlambat[] = (clone $query)->where('status', 'terlambat')->count();
            $dataAlpa[] = (clone $query)->whereIn('status', ['alpa', 'alpha'])->count();

            // Pindah ke periode berikutnya
            $currentDate->$increment();
        }

        return [
            'labels' => $labels,
            'hadir' => $dataHadir,
            'terlambat' => $dataTerlambat,
            'alpa' => $dataAlpa
        ];
    }

    // private function getPieData($filter, $modelClass)
    // {
    //     $query = $modelClass::query();
    //     $today = Carbon::today();

    //     // Menggunakan helper Laravel agar kompatibel dengan MySQL & PostgreSQL
    //     if ($filter == 'harian') {
    //         $query->whereDate('date', $today);
    //     } elseif ($filter == 'mingguan') {
    //         $query->whereBetween('date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]);
    //     } elseif ($filter == 'bulanan') {
    //         $query->whereMonth('date', $today->month)->whereYear('date', $today->year);
    //     } elseif ($filter == 'semester') {
    //         $start = ($today->month >= 7) ? $today->year.'-07-01' : $today->year.'-01-01';
    //         $end = ($today->month >= 7) ? $today->year.'-12-31' : $today->year.'-06-30';
    //         $query->whereBetween('date', [$start, $end]);
    //     } elseif ($filter == 'tahunan') {
    //         $query->whereYear('date', $today->year);
    //     }

    //     $counts = $query->select('status', DB::raw('count(*) as total'))
    //                    ->groupBy('status')
    //                    ->pluck('total', 'status')
    //                    ->toArray();

    //     return [
    //         $counts['hadir'] ?? 0,
    //         $counts['terlambat'] ?? 0,
    //         $counts['sakit'] ?? 0,
    //         $counts['izin'] ?? 0,
    //         ($counts['alpa'] ?? 0) + ($counts['alpha'] ?? 0)
    //     ];
    // }

    private function getPieData($filter, $modelClass)
    {
        $query = $modelClass::query();
        $today = Carbon::today();

        if ($filter == 'harian') {
            $query->whereDate('date', $today);
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('date', [
                $today->copy()->startOfWeek()->format('Y-m-d'), 
                $today->copy()->endOfWeek()->format('Y-m-d')
            ]);
        } elseif ($filter == 'bulanan') {
            // Gunakan EXTRACT untuk PostgreSQL
            $query->whereRaw("EXTRACT(MONTH FROM date) = ?", [$today->month])
                ->whereRaw("EXTRACT(YEAR FROM date) = ?", [$today->year]);
        } elseif ($filter == 'semester') {
            // Semester Ganjil (Juli - Des) atau Genap (Jan - Juni)
            if ($today->month >= 7) {
                $query->whereBetween('date', [$today->year . '-07-01', $today->year . '-12-31']);
            } else {
                $query->whereBetween('date', [$today->year . '-01-01', $today->year . '-06-30']);
            }
        } elseif ($filter == 'tahunan') {
            $query->whereRaw("EXTRACT(YEAR FROM date) = ?", [$today->year]);
        }

        // Hitung akumulasi per status
        $counts = $query->select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();

        // Normalisasi Array untuk Chart.js (memastikan urutan index tetap)
        // Index: 0=Hadir, 1=Terlambat, 2=Sakit, 3=Izin, 4=Alpa
        return [
            $counts['hadir'] ?? 0,
            $counts['terlambat'] ?? 0,
            $counts['sakit'] ?? 0,
            $counts['izin'] ?? 0,
            ($counts['alpa'] ?? 0) + ($counts['alpha'] ?? 0)
        ];
    }
}