<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Industry;
use App\Models\Internship;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\InternshipTimeline; // Import Model Timeline
use Illuminate\Support\Facades\Auth;
use App\Models\Setting; // Import Setting Model
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf; // Import DomPDF

class InternshipStudentController extends Controller
{
    // /**
    //  * Halaman Utama Pemilihan PKL untuk Siswa
    //  */
    // public function index()
    // {
    //     $user = Auth::user();
    //     $student = Student::where('user_id', $user->id)->first();

    //     if (!$student) {
    //         return redirect()->back()->with('error', 'Data siswa tidak ditemukan pada akun ini.');
    //     }

    //     // Cek pengajuan PKL siswa saat ini
    //     $myInternship = Internship::with('industry', 'advisor')
    //         ->where('student_id', $student->id)
    //         ->orderBy('created_at', 'desc')
    //         ->first();

    //     // Ambil daftar Industri beserta jumlah kuota yang sudah terisi
    //     $industries = Industry::withCount(['internships as terisi' => function($q) {
    //         $q->whereIn('status', ['pending', 'active']);
    //     }])->orderBy('name')->get();

    //     return view('siswa.internships.index', compact('industries', 'myInternship'));
    // }

    // /**
    //  * Proses Pengajuan Tempat PKL
    //  */
    // public function apply(Request $request)
    // {
    //     $request->validate([
    //         'industry_id' => 'required|exists:industries,id'
    //     ]);

    //     $user = Auth::user();
    //     $student = Student::where('user_id', $user->id)->firstOrFail();

    //     // 1. Cek apakah siswa sudah punya pengajuan yang aktif atau pending
    //     $hasActiveInternship = Internship::where('student_id', $student->id)
    //         ->whereIn('status', ['pending', 'active'])
    //         ->exists();

    //     if ($hasActiveInternship) {
    //         return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan PKL yang sedang diproses atau aktif.');
    //     }

    //     // 2. Cek Kuota Industri
    //     $industry = Industry::findOrFail($request->industry_id);
    //     $terisi = Internship::where('industry_id', $industry->id)
    //         ->whereIn('status', ['pending', 'active'])
    //         ->count();

    //     if ($industry->quota > 0 && $terisi >= $industry->quota) {
    //         return redirect()->back()->with('error', 'Maaf, kuota untuk tempat PKL ini sudah penuh.');
    //     }

    //     // 3. Buat Pengajuan (Status default: pending)
    //     Internship::create([
    //         'student_id' => $student->id,
    //         'industry_id' => $industry->id,
    //         // Tanggal default bisa diatur oleh admin nanti saat di-approve,
    //         // atau diset default untuk semester ini
    //         'start_date' => now(),
    //         'end_date' => now()->addMonths(3),
    //         'status' => 'pending'
    //     ]);

    //     return redirect()->back()->with('success', 'Pengajuan tempat PKL berhasil dikirim! Silakan tunggu persetujuan dari Guru/Admin.');
    // }

    /**
     * Helper: Cek apakah kelas siswa diizinkan untuk PKL
     */
    private function checkAccess($student)
    {
        // Pastikan relasi classroom diload. Jika tidak punya kelas, tolak.
        if (!$student->classroom) {
            return false;
        }
        // Cek flag is_pkl_active pada tabel classrooms
        return $student->classroom->is_pkl_active;
    }

    /**
     * Halaman Utama Pemilihan PKL untuk Siswa
     */
    // public function index()
    // {
    //     $user = Auth::user();
    //     $student = Student::where('user_id', $user->id)->first();

    //     if (!$student) {
    //         return redirect()->back()->with('error', 'Data siswa tidak ditemukan pada akun ini.');
    //     }

    //     // Cek pengajuan PKL siswa saat ini
    //     $myInternship = Internship::with('industry', 'advisor')
    //         ->where('student_id', $student->id)
    //         ->orderBy('created_at', 'desc')
    //         ->first();

    //     // Ambil daftar Industri beserta jumlah kuota yang sudah terisi
    //     $industries = Industry::withCount(['internships as terisi' => function($q) {
    //         $q->whereIn('status', ['pending', 'active']);
    //     }])->orderBy('name')->get();

    //     return view('siswa.internships.index', compact('industries', 'myInternship'));
    // }

        public function index()
    {
        $user = Auth::user();
        // Load relasi classroom untuk pengecekan
        $student = Student::with('classroom')->where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan pada akun ini.');
        }

        // --- GATEKEEPING: CEK MAPPING KELAS ---
        // Jika kelas tidak aktif PKL, tampilkan halaman terkunci
        if (!$this->checkAccess($student)) {
            return view('siswa.internships.locked', compact('student'));
        }

        // Cek pengajuan PKL siswa saat ini
        $myInternship = Internship::with('industry', 'advisor')
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Ambil daftar Industri beserta jumlah kuota yang sudah terisi
        $industries = Industry::withCount(['internships as terisi' => function($q) {
            $q->whereIn('status', ['pending', 'active']);
        }])->orderBy('name')->get();

        // Data Guru untuk pilihan pembimbing (jika siswa boleh request)
        $teachers = Teacher::orderBy('name')->get();

        // --- FITUR BARU: AMBIL TIMELINE KEGIATAN ---
        // Mengambil data timeline urut berdasarkan tanggal mulai
        $timelines = InternshipTimeline::orderBy('start_date', 'asc')->get();

        return view('siswa.internships.index', compact('industries', 'myInternship', 'teachers', 'timelines'));
    }

    /**
     * Proses Pengajuan Tempat PKL
     */
    public function apply(Request $request)
    {
        $request->validate([
            'industry_id' => 'required|exists:industries,id'
        ]);

        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // 1. Cek apakah siswa sudah punya pengajuan yang aktif atau pending
        $hasActiveInternship = Internship::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($hasActiveInternship) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan PKL yang sedang diproses atau aktif.');
        }

        // 2. Cek Kuota Industri
        $industry = Industry::findOrFail($request->industry_id);
        $terisi = Internship::where('industry_id', $industry->id)
            ->whereIn('status', ['pending', 'active'])
            ->count();

        if ($industry->quota > 0 && $terisi >= $industry->quota) {
            return redirect()->back()->with('error', 'Maaf, kuota untuk tempat PKL ini sudah penuh.');
        }

        // 3. Buat Pengajuan (Status default: pending)
        Internship::create([
            'student_id' => $student->id,
            'industry_id' => $industry->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(6), // Default 6 bulan
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Pengajuan tempat PKL berhasil dikirim! Silakan upload surat izin orang tua.');
    }

     /**
     * Upload Surat Persetujuan Orang Tua
     * 
     */

    public function upload(){
        tes;
    }

    public function uploadConsent(Request $request)
    {
        $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:2048' // Maks 2MB
        ]);

        $internship = Internship::findOrFail($request->internship_id);

        // Validasi kepemilikan
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        if ($internship->student_id !== $student->id) {
            abort(403, 'Akses Ditolak');
        }

        // Upload File
        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($internship->parent_consent_file) {
                if (Storage::disk('public')->exists($internship->parent_consent_file)) {
                    Storage::disk('public')->delete($internship->parent_consent_file);
                }
            }

            $path = $request->file('file')->store('internship_consents', 'public');
            $internship->update(['parent_consent_file' => $path]);
        }

        return redirect()->back()->with('success', 'Surat persetujuan orang tua berhasil diunggah.');
    }

    /**
     * Halaman Web View Cetak Bukti Persetujuan Ortu
     */
    public function agreement()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $myInternship = Internship::with('industry')
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Ambil Data Sekolah untuk Kop Surat View HTML
        $school = [
            'name' => Setting::value('school_name', 'SMK NEGERI 1 BUKITTINGGI'),
            'address' => Setting::value('school_address', 'Jalan Iskandar Teja Sukmana - Padang Gamuak'),
            'phone' => Setting::value('school_phone', ''),
            'email' => Setting::value('school_email', ''),
            'logo_left' => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),
            'provinsi_name' => Setting::value('provinsi_name', 'SUMATERA BARAT'),
        ];

        return view('student.internships.agreement', compact('student', 'myInternship', 'school'));
    }

    /**
     * Cetak Surat Persetujuan Orang Tua (PDF Download)
     */
    public function printConsentLetter()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Ambil pengajuan PKL terakhir
        $internship = Internship::with('industry')
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$internship) {
            return redirect()->back()->with('error', 'Anda belum memilih tempat PKL.');
        }

        // Ambil Data Sekolah untuk Kop Surat
        $school = [
            'name' => Setting::value('school_name', 'SMK NEGERI 1 BUKITTINGGI'),
            'address' => Setting::value('school_address', 'Jalan Iskandar Teja Sukmana - Padang Gamuak'),
            'phone' => Setting::value('school_phone', ''),
            'email' => Setting::value('school_email', ''),
            'logo_left' => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),
            'provinsi_name' => Setting::value('provinsi_name', 'SUMATERA BARAT'),
            'kabeng_name' => Setting::value('kabeng_name', '.........................'), // Nama Kepala Bengkel
            'kabeng_nip' => Setting::value('kabeng_nip', '.........................'),
        ];

        $pdf = Pdf::loadView('siswa.internships.consent_pdf', compact('student', 'internship', 'school'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Izin_Ortu_' . $student->name . '.pdf');
    }

     /**
     * Halaman Lihat Transkrip Nilai (Web View)
     */
    public function transcript()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Ambil data PKL yang sudah ada nilainya (grade)
        $internship = Internship::with(['industry', 'grade', 'advisor'])
            ->where('student_id', $student->id)
            ->whereHas('grade') // Hanya tampil jika sudah dinilai guru
            ->first();

        if (!$internship) {
            return redirect()->route('student.internships.index')
                ->with('error', 'Nilai PKL belum diterbitkan oleh Guru Pembimbing.');
        }

        // Hitung Predikat
        $score = $internship->grade->final_score;
        $predikat = 'Kurang';
        if ($score >= 90) $predikat = 'Sangat Baik';
        elseif ($score >= 80) $predikat = 'Baik';
        elseif ($score >= 70) $predikat = 'Cukup';

        return view('siswa.internships.transcript', compact('student', 'internship', 'predikat'));
    }

    /**
     * Cetak Transkrip Nilai (PDF Download)
     */
    public function printTranscript()
    {
        $user = Auth::user();
        $student = Student::with('classroom')->where('user_id', $user->id)->firstOrFail();

        $internship = Internship::with(['industry', 'grade', 'advisor'])
            ->where('student_id', $student->id)
            ->whereHas('grade')
            ->firstOrFail();

        // Hitung Predikat
        $score = $internship->grade->final_score;
        $predikat = 'Kurang';
        if ($score >= 90) $predikat = 'Sangat Baik';
        elseif ($score >= 80) $predikat = 'Baik';
        elseif ($score >= 70) $predikat = 'Cukup';

        // Data Sekolah & Pejabat Penandatangan
        $school = [
            'name' => Setting::value('school_name', 'SMK NEGERI 1 BUKITTINGGI'),
            'address' => Setting::value('school_address', 'Jalan Iskandar Teja Sukmana - Padang Gamuak'),
            'phone' => Setting::value('school_phone', ''),
            'email' => Setting::value('school_email', ''),
            'logo_left' => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),
            'provinsi_name' => Setting::value('provinsi_name', 'SUMATERA BARAT'),
            'sign_city' => Setting::value('sign_city', 'Bukittinggi'),
            
            // Kepala Sekolah
            'headmaster_name' => Setting::value('sign_name', '.........................'),
            'headmaster_nip' => Setting::value('sign_nip', '.........................'),
            
            // Kepala Bengkel / Kaprog (Opsional, jika ada di format)
            'kabeng_name' => Setting::value('kabeng_name', '.........................'),
            'kabeng_nip' => Setting::value('kabeng_nip', '.........................'),
        ];

        $pdf = Pdf::loadView('siswa.internships.transcript_pdf', compact('student', 'internship', 'school', 'predikat'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Transkrip_Nilai_PKL_' . str_replace(' ', '_', $student->name) . '.pdf');
    }
}
