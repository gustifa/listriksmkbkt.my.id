<?php

namespace App\Http\Controllers;

use App\Models\DailyAttendance;
use Illuminate\Http\Request;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    /**
     * Halaman filter untuk memilih periode reward.
     */
    public function index()
    {
        return view('certificates.index');
    }

    /**
     * Proses hitung siswa rajin dan generate sertifikat.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|numeric',
            'year'  => 'required|numeric',
            'limit' => 'required|numeric|min:1|max:10', // Batasi misal Top 3 atau Top 10
            'title' => 'required|string', // Judul sertifikat, misal: "The Most Diligent Student"
        ]);

        $month = $request->month;
        $year = $request->year;
        $limit = $request->limit;
        $school = $this->getSchoolData();

        // LOGIKA: Hitung jumlah 'hadir' terbanyak per siswa
        // Update: Menyesuaikan dengan schema tabel 'daily_attendances'
        $bestStudents = DailyAttendance::with(['student', 'student.classroom'])
            ->select('student_id', DB::raw('count(*) as total_present'))
            // Menggunakan kolom 'date' sesuai schema, bukan created_at
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            // Menggunakan status 'hadir' sesuai enum schema ['hadir', 'terlambat', ...]
            // 'hadir' diasumsikan sebagai datang tepat waktu.
            ->where('status', 'hadir') 
            ->groupBy('student_id')
            ->orderByDesc('total_present') // Urutkan dari jumlah hadir terbanyak
            // Opsional: Jika jumlah hadir sama, bisa diurutkan siapa yang rata-rata arrival_time nya paling awal
            // ->orderBy(DB::raw('AVG(arrival_time)'), 'asc') 
            ->take($limit)
            ->get();

        if ($bestStudents->isEmpty()) {
            return back()->with('error', 'Tidak ada data siswa yang "Hadir" (Tepat Waktu) pada periode tersebut.');
        }

        $period = Carbon::create($year, $month, 1)->translatedFormat('F Y');

        return view('certificates.print', compact('bestStudents', 'period', 'request', 'school'));
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
        ];
    }
}