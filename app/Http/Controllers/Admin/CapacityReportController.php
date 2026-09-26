<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Major;
use App\Models\Classroom;
use App\Models\Student;

class CapacityReportController extends Controller
{
    public function index()
    {
        // Mengambil semua jurusan/program keahlian beserta kelas dan siswanya
        $majors = Major::with(['classrooms' => function($query) {
            // Kita ambil semua kelas untuk dihitung rombelnya,
            // dan load siswanya untuk dihitung jumlah siswa per kelas.
            $query->with('students');
        }])->orderBy('name', 'asc')->get();

        $reportData = [];
        $totalSiswaLulus = 0;
        $totalRombel = 0;

        foreach ($majors as $major) {
            // Asumsi: Kelas XII ditandai dengan angka "XII" di nama kelasnya.
            // Sesuaikan regex/pencarian ini dengan format penamaan kelas di sekolah Anda.
            $kelasXII = $major->classrooms->filter(function ($classroom) {
                return str_contains($classroom->name, 'XII');
            });

            // Menghitung jumlah siswa di semua kelas XII pada jurusan tersebut
            $jumlahSiswaLulus = 0;
            foreach ($kelasXII as $kelas) {
                $jumlahSiswaLulus += $kelas->students->count();
            }

            // Menghitung jumlah rombel (jumlah kelas XII)
            $jumlahRombel = $kelasXII->count();

            $reportData[] = [
                'program_keahlian' => 'Teknik Ketenagalistrikan', // Sesuaikan jika ada pengelompokan program keahlian di DB
                'konsentrasi_keahlian' => $major->name,
                'siswa_lulus' => $jumlahSiswaLulus,
                'jumlah_rombel' => $jumlahRombel,
            ];

            $totalSiswaLulus += $jumlahSiswaLulus;
            $totalRombel += $jumlahRombel;
        }

        return view('report.daya_tampung', compact('reportData', 'totalSiswaLulus', 'totalRombel'));
    }
}
