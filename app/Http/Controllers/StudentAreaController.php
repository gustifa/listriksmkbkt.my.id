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
    // public function historySubject()
    // {
    //     $student = Student::where('user_id', Auth::id())->firstOrFail();

    //     $attendances = Attendance::with(['schedule.subject', 'schedule.teacher'])
    //                     ->where('student_id', $student->id)
    //                     ->orderBy('date', 'desc')
    //                     ->orderBy('check_in_time', 'desc')
    //                     ->paginate(10);

    //     return view('student_area.history_subject', compact('attendances'));
    // }

    /**
     * Riwayat Absensi Mata Pelajaran dengan Instant Filter & AJAX Pagination
     */
    public function historySubject(Request $request)
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $query = Attendance::with(['schedule.subject', 'schedule.teacher'])
                    ->where('student_id', $student->id);

        // Filter Berdasarkan Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Berdasarkan Rentang Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $attendances = $query->orderBy('date', 'desc')
                            ->orderBy('check_in_time', 'desc')
                            ->paginate(9);

        // Jika Request Dipanggil via AJAX
        if ($request->ajax()) {
            return response()->json([
                'status'     => 'success',
                'html_card'  => view('student_area.partials.subject_cards', compact('attendances'))->render(),
                'html_table' => view('student_area.partials.subject_table', compact('attendances'))->render(),
                'pagination' => $attendances->hasPages() ? (string) $attendances->links('pagination::bootstrap-5') : ''
            ]);
        }

        return view('student_area.history_subject', compact('attendances'));
    }

    /**
     * Riwayat Absensi Harian (Datang/Pulang)
     */
    // public function historyDaily()
    // {
    //     $student = Student::where('user_id', Auth::id())->firstOrFail();

    //     $dailies = DailyAttendance::where('student_id', $student->id)
    //                 ->orderBy('date', 'desc')
    //                 ->paginate(10);

    //     return view('student_area.history_daily', compact('dailies'));
    // }

    /**
     * Riwayat Absensi Harian (Datang/Pulang) dengan Filter & AJAX Pagination
     */
    // public function historyDaily(Request $request)
    // {
    //     $student = Student::where('user_id', Auth::id())->firstOrFail();

    //     $query = DailyAttendance::where('student_id', $student->id);

    //     // Filter Berdasarkan Status
    //     if ($request->has('status') && !empty($request->status)) {
    //         $query->where('status', $request->status);
    //     }

    //     // Filter Berdasarkan Rentang Tanggal
    //     if ($request->has('start_date') && !empty($request->start_date)) {
    //         $query->whereDate('date', '>=', $request->start_date);
    //     }
    //     if ($request->has('end_date') && !empty($request->end_date)) {
    //         $query->whereDate('date', '<=', $request->end_date);
    //     }

    //     $dailies = $query->orderBy('date', 'desc')->paginate(9);

    //     // Jika Request Berasal dari AJAX Client
    //     if ($request->ajax()) {
    //         return response()->json([
    //             'status'     => 'success',
    //             'html_card'  => view('student_area.partials.daily_cards', compact('dailies'))->render(),
    //             'html_table' => view('student_area.partials.daily_table', compact('dailies'))->render(),
    //             'pagination' => (string) $dailies->links()
    //         ]);
    //     }

    //     return view('student_area.history_daily', compact('dailies'));
    // }

    public function historyDaily(Request $request)
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $query = DailyAttendance::where('student_id', $student->id);

        // Filter Berdasarkan Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Berdasarkan Rentang Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $dailies = $query->orderBy('date', 'desc')->paginate(5);

        // Jika Request Berasal dari AJAX Client
        if ($request->ajax()) {
            return response()->json([
                'status'     => 'success',
                'html_card'  => view('student_area.partials.daily_cards', compact('dailies'))->render(),
                'html_table' => view('student_area.partials.daily_table', compact('dailies'))->render(),
                'pagination' => (string) $dailies->links('pagination::bootstrap-5') // Gunakan style bootstrap-5
            ]);
        }

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

    /**
     * AJAX Get Detail Presensi Mapel
     */
    public function getAttendanceDetail($id)
    {
        $attendance = Attendance::with(['schedule.subject', 'schedule.teacher', 'student.classroom'])
            ->where('student_id', optional(Auth::user()->student)->id)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'date'          => \Carbon\Carbon::parse($attendance->date)->translatedFormat('l, d F Y'),
                'time'          => \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') . ' WIB',
                'subject'       => $attendance->schedule->subject->name ?? $attendance->schedule->subject_name ?? '-',
                'teacher'       => $attendance->schedule->teacher->name ?? '-',
                'status'        => ucfirst($attendance->status),
                'recorded_by'   => $attendance->recorded_by ?? 'Siswa (Mandiri)',
                'notes'         => $attendance->notes ?? '-',
            ]
        ]);
    }

    /**
     * AJAX Get Detail Presensi Gerbang (Harian)
     */
    public function getDailyDetail($id)
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $daily = DailyAttendance::where('student_id',$student->id)
                    ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'date'        => \Carbon\Carbon::parse($daily->date)->translatedFormat('l, d F Y'),
                'arrival'     => $daily->arrival_time ? \Carbon\Carbon::parse($daily->arrival_time)->format('H:i') . ' WIB' : '-',
                'departure'   => $daily->departure_time ? \Carbon\Carbon::parse($daily->departure_time)->format('H:i') . ' WIB' : 'Belum Pulang',
                'status'      => strtoupper($daily->status),
                'notes'       => $daily->notes ?? '-',
                'recorded_by' => $daily->recorded_by ?? 'Sistem Gerbang',
                'photo_in'    => $daily->photo_in ? asset('storage/' . $daily->photo_in) : null,
                'photo_out'   => $daily->photo_out ? asset('storage/' . $daily->photo_out) : null,
            ]
        ]);
    }
}
