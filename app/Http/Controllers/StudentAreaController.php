<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\DailyAttendance;
use App\Models\Setting;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // <--- Import Library QR

class StudentAreaController extends Controller
{
    /**
     * Halaman Profil Siswa (Edit HP & Alamat)
     */
    public function profileStudent()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        // Ambil data siswa yang sedang login
        $students = Student::where('user_id', Auth::id())->with('classroom')->firstOrFail();

        return view('student_area.profile', compact('students', 'profileData'));
    }

    /**
     * Proses Update Profil
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
            'address' => 'nullable|string|max:500',
        ]);

        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $student->update([
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Data profil berhasil diperbarui.');
    }

    /**
     * Riwayat Absensi Mata Pelajaran
     */
    public function historySubject()
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $attendances = Attendance::with(['schedule.subject', 'schedule.teacher'])
                        ->where('student_id', $student->id)
                        ->orderBy('date', 'desc')
                        ->orderBy('check_in_time', 'desc')
                        ->paginate(10);

        return view('student_area.history_subject', compact('attendances'));
    }

    /**
     * Riwayat Absensi Harian (Datang/Pulang)
     */
    public function historyDaily()
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $dailies = DailyAttendance::where('student_id', $student->id)
                    ->orderBy('date', 'desc')
                    ->paginate(10);

        return view('student_area.history_daily', compact('dailies'));
    }

    /**
     * [BARU] Cetak Kartu Sendiri
     */
    public function printCard()
    {
        $settings = $this->getSchoolData();
        // Ambil data siswa yang sedang login
        $students = Student::where('user_id', Auth::id())->with('classroom')->firstOrFail();

        // Generate QR Code berdasarkan NIS
        $qrcode = QrCode::size(120)->generate($students->nis);

        // Gunakan view yang SAMA dengan milik Admin agar desain konsisten
        return view('print.single_card', compact('students', 'qrcode', 'settings'));
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

    /**
     * Halaman Pemilihan Kelas (Dashboard Cetak)
     */
    public function index()
    {
        // Ambil semua kelas beserta jumlah siswanya
        $classrooms = Classroom::withCount('students')->orderBy('name')->get();

        return view('print.select_class', compact('classrooms'));
    }

    /**
     * Cetak Kartu Berdasarkan Kelas Spesifik
     */
    public function printByClass($id)
    {
        $classroom = Classroom::findOrFail($id);
        $settings = Setting::pluck('value', 'key')->toArray();

        // Ambil siswa HANYA dari kelas tersebut
        $students = Student::where('classroom_id', $id)->orderBy('name')->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Kelas ini belum memiliki siswa.');
        }

        // Kita gunakan view 'print.all_cards' yang sudah ada (Reusable)
        return view('print.all_cards', compact('students', 'classroom', 'settings'));
    }

    /**
     * Cetak Semua Kartu (Massal Satu Sekolah)
     */
    public function printAll()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $students = Student::with('classroom')->orderBy('classroom_id')->orderBy('name')->get();
        return view('student_area.all_cards', compact('students', 'settings'));
    }

    /**
     * Cetak Satu Kartu Saja (Perorangan)
     */
    public function printSingle($id)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $student = Student::with('classroom')->findOrFail($id);
        $qrcode = QrCode::size(120)->generate($student->nis);

        return view('print.single_card', compact('student', 'qrcode', 'settings'));
    }


    /**
     * [BARU] Halaman Pilih Siswa (Checkbox)
     */
    public function selectStudents($id)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $classroom = Classroom::findOrFail($id);
        $students = Student::where('classroom_id', $id)->orderBy('name')->get();

        return view('student_area.select_students', compact('classroom', 'students', 'settings'));
    }

    /**
     * [BARU] Proses Cetak Siswa Terpilih
     */
    public function printSelected(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);
        $settings = Setting::pluck('value', 'key')->toArray();

        $students = Student::with('classroom')
                    ->whereIn('id', $request->student_ids)
                    ->orderBy('name')
                    ->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang dipilih.');
        }

        // Ambil kelas dari siswa pertama untuk judul (opsional)
        $classroom = $students->first()->classroom;

        return view('print.all_cards', compact('students', 'classroom', 'settings'));
    }
}
