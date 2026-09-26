<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\TeachingJournal;
use App\Models\Teacher;
use App\Models\Schedule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Classroom;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LaporanGuruController extends Controller
{
   /**
     * CETAK REKAP DAFTAR HADIR (FORMAT GRID TANGGAL) - FITUR BARU
     * Menghasilkan PDF Landscape mirip "DAFTAR HADIR SEMESTER GENAP"
     */
    // public function printAttendanceList(Request $request, $schedule_id)
    // {
    //     $user = Auth::user();
    //     $schedule = Schedule::with(['classroom', 'subject', 'teacher'])->findOrFail($schedule_id);

    //     // Validasi Akses
    //     if ($user->jenis_user !== 'admin') {
    //          $teacher = Teacher::where('user_id', $user->id)->first();
    //          // Pastikan guru ini mengajar mapel & kelas tersebut
    //          if (!$teacher || $schedule->teacher_id !== $teacher->id) {
    //              abort(403, 'Akses Ditolak: Anda bukan pengampu jadwal ini.');
    //          }
    //     }

    //     // Tentukan Semester & Tahun Ajaran Otomatis
    //     $bulan = date('n');
    //     $tahun = date('Y');

    //     if ($bulan >= 7) {
    //         $semester = "Ganjil";
    //         $tahunMulai = $tahun;
    //     } else {
    //         $semester = "Genap";
    //         $tahunMulai = $tahun - 1;
    //     }
    //     $tahunAjaran = $tahunMulai . "/" . ($tahunMulai + 1);

    //     // Rentang Tanggal Semester
    //     if ($semester == 'Ganjil') {
    //         $startDate = $tahunMulai . '-07-01';
    //         $endDate = $tahunMulai . '-12-31';
    //     } else {
    //         $startDate = ($tahunMulai + 1) . '-01-01';
    //         $endDate = ($tahunMulai + 1) . '-06-30';
    //     }

    //     // Ambil Data Siswa
    //     $students = \App\Models\Student::where('classroom_id', $schedule->classroom_id)
    //                 ->orderBy('name')
    //                 ->get();

    //     // Ambil Data Absensi pada Rentang Semester untuk Mapel & Kelas ini
    //     // Kita gunakan whereHas schedule untuk memastikan subject & classroom sama
    //     $attendances = Attendance::whereHas('schedule', function($q) use ($schedule) {
    //                         $q->where('classroom_id', $schedule->classroom_id)
    //                           ->where('subject_id', $schedule->subject_id);
    //                     })
    //                     ->whereBetween('date', [$startDate, $endDate])
    //                     ->orderBy('date', 'asc')
    //                     ->get();

    //     // Grouping Tanggal Pertemuan (Unique)
    //     $dates = $attendances->pluck('date')->unique()->sort()->values();

    //     // Mapping Data: [student_id][date] => status
    //     $attendanceMap = [];
    //     // Mapping Rekap Sakit/Izin/Alpa per siswa
    //     $recapMap = [];

    //     // Inisialisasi Rekap
    //     foreach($students as $s) {
    //         $recapMap[$s->id] = ['H'=>0, 'S'=>0, 'I'=>0, 'A'=>0, 'T'=>0];
    //     }

    //     foreach($attendances as $att) {
    //         // Status Singkat
    //         $status = $att->status;
    //         $short = '-';
    //         // Mapping status ke kode singkat dan hitung rekap
    //         if ($status == 'hadir' || $status == 'present') { $short = 'H'; $recapMap[$att->student_id]['H']++; }
    //         elseif ($status == 'sakit' || $status == 'sick') { $short = 'S'; $recapMap[$att->student_id]['S']++; }
    //         elseif ($status == 'izin' || $status == 'permission') { $short = 'I'; $recapMap[$att->student_id]['I']++; }
    //         elseif ($status == 'alpa' || $status == 'alpha') { $short = 'A'; $recapMap[$att->student_id]['A']++; }
    //         elseif ($status == 'terlambat' || $status == 'late') { $short = 'T'; $recapMap[$att->student_id]['T']++; }

    //         $attendanceMap[$att->student_id][$att->date] = $short;
    //     }

    //     $school = $this->getSchoolData();

    //     // Generate PDF menggunakan view baru
    //     $pdf = Pdf::loadView('guru.pdf.attendance_recap', compact(
    //         'schedule', 'students', 'dates', 'attendanceMap', 'recapMap', 'school', 'semester', 'tahunAjaran'
    //     ));

    //     // Landscape untuk tabel lebar
    //     $pdf->setPaper('a4', 'landscape');
    //     $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

    //     return $pdf->stream('Rekap-Presensi-' . $schedule->classroom->name . '.pdf');
    // }

    /**
     * CETAK REKAP DAFTAR HADIR (FORMAT GRID TANGGAL) - FITUR BARU
     * Menghasilkan PDF Landscape mirip "DAFTAR HADIR SEMESTER GENAP"
     */
    public function printAttendanceList(Request $request, $schedule_id)
    {
        $user = Auth::user();
        $schedule = Schedule::with(['classroom', 'subject', 'teacher'])->findOrFail($schedule_id);

        // Validasi Akses
        if ($user->jenis_user !== 'admin') {
             $teacher = Teacher::where('user_id', $user->id)->first();
             // Pastikan guru ini mengajar mapel & kelas tersebut
             if (!$teacher || $schedule->teacher_id !== $teacher->id) {
                 abort(403, 'Akses Ditolak: Anda bukan pengampu jadwal ini.');
             }
        }

        // Tentukan Semester & Tahun Ajaran Otomatis
        $bulan = date('n');
        $tahun = date('Y');

        if ($bulan >= 7) {
            $semester = "Ganjil";
            $tahunMulai = $tahun;
        } else {
            $semester = "Genap";
            $tahunMulai = $tahun - 1;
        }
        $tahunAjaran = $tahunMulai . "/" . ($tahunMulai + 1);

        // Rentang Tanggal Semester
        if ($semester == 'Ganjil') {
            $startDate = $tahunMulai . '-07-01';
            $endDate = $tahunMulai . '-12-31';
        } else {
            $startDate = ($tahunMulai + 1) . '-01-01';
            $endDate = ($tahunMulai + 1) . '-06-30';
        }

        // Ambil Data Siswa (Hanya yang aktif di kelas ini)
        $students = \App\Models\Student::where('classroom_id', $schedule->classroom_id)
                    ->orderBy('name')
                    ->get();

        // Ambil Data Absensi pada Rentang Semester untuk Mapel & Kelas ini
        $attendances = Attendance::whereHas('schedule', function($q) use ($schedule) {
                            $q->where('classroom_id', $schedule->classroom_id)
                              ->where('subject_id', $schedule->subject_id);
                        })
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'asc')
                        ->get();

        // Grouping Tanggal Pertemuan (Unique)
        $dates = $attendances->pluck('date')->unique()->sort()->values();

        // Mapping Data: [student_id][date] => status
        $attendanceMap = [];
        // Mapping Rekap Sakit/Izin/Alpa per siswa
        $recapMap = [];

        // Inisialisasi Rekap hanya untuk siswa yang aktif
        foreach($students as $s) {
            $recapMap[$s->id] = ['H'=>0, 'S'=>0, 'I'=>0, 'A'=>0, 'T'=>0];
        }

        foreach($attendances as $att) {
            // FIX: Cek apakah siswa masih ada di kelas ini (dalam array $recapMap)
            // Jika siswa sudah pindah/keluar, skip datanya untuk mencegah error Undefined Index
            if (!isset($recapMap[$att->student_id])) {
                continue;
            }

            // Status Singkat
            $status = $att->status;
            $short = '-';
            // Mapping status ke kode singkat dan hitung rekap
            if ($status == 'hadir' || $status == 'present') { $short = 'H'; $recapMap[$att->student_id]['H']++; }
            elseif ($status == 'sakit' || $status == 'sick') { $short = 'S'; $recapMap[$att->student_id]['S']++; }
            elseif ($status == 'izin' || $status == 'permission') { $short = 'I'; $recapMap[$att->student_id]['I']++; }
            elseif ($status == 'alpa' || $status == 'alpha') { $short = 'A'; $recapMap[$att->student_id]['A']++; }
            elseif ($status == 'terlambat' || $status == 'late') { $short = 'T'; $recapMap[$att->student_id]['T']++; }

            $attendanceMap[$att->student_id][$att->date] = $short;
        }

        $school = $this->getSchoolData();

        // Generate PDF menggunakan view baru
        $pdf = Pdf::loadView('guru.pdf.attendance_recap', compact(
            'schedule', 'students', 'dates', 'attendanceMap', 'recapMap', 'school', 'semester', 'tahunAjaran'
        ));

        // Landscape untuk tabel lebar
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

        return $pdf->stream('Rekap-Presensi-' . $schedule->classroom->name . '.pdf');
    }


        // --- HELPER IMAGE TO BASE64 ---
    private function imageToBase64($path) {
        if (!$path || $path == '-') return null;
        $cleanPath = str_replace('storage/', '', $path);

        $pathsToCheck = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($path)
        ];

        foreach ($pathsToCheck as $fullPath) {
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        return null;
    }

    private function getSchoolData()
    {
        return [
            'school_name'    => Setting::value('school_name', 'SMK DEFAULT'),
            'school_address' => Setting::value('school_address', 'Alamat Sekolah'),
            'school_phone'   => Setting::value('school_phone', '-'),
            'school_web'     => Setting::value('school_web', '-'),
            'school_email'   => Setting::value('school_email', '-'),
            'logo_left'      => $this->imageToBase64(Setting::value('logo_left')),
            'logo_right'     => $this->imageToBase64(Setting::value('logo_right')),
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
            'sign_image' => $this->imageToBase64(Setting::value('signature_image')),
            'provinsi_name' => Setting::value('provinsi_name', 'PROVINSI SUMATERA BARAT'),
        ];
    }
}
