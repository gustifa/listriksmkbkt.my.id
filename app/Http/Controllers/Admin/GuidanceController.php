<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ViolationType;
use App\Models\StudentViolation;
use App\Models\StudentGuidance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\Classroom; // <--- Tambahkan import ini

class GuidanceController extends Controller
{
    /**
     * Dashboard Pembinaan: List Siswa Bermasalah (Poin Tertinggi)
     */
    // public function index(Request $request)
    // {
    //     $user = Auth::user();

    //     // 1. Query Siswa terfilter otomatis berdasarkan scope accessibleBy
    //     $query = Student::accessibleBy($user)
    //         ->leftJoin('student_violations', 'students.id', '=', 'student_violations.student_id')
    //         ->leftJoin('violation_types', 'student_violations.violation_type_id', '=', 'violation_types.id')
    //         ->select(
    //             'students.*',
    //             DB::raw('COALESCE(SUM(violation_types.points), 0) as total_points')
    //         )
    //         ->groupBy('students.id');

    //     // Filter Pencarian Nama / NIS
    //     if ($request->filled('search')) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('students.name', 'like', '%' . $request->search . '%')
    //               ->orWhere('students.nis', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     // Hanya ambil siswa yang punya poin > 0
    //     $query->having(DB::raw('COALESCE(SUM(violation_types.points), 0)'), '>', 0);

    //     // Urutkan dari poin tertinggi
    //     $query->orderByDesc('total_points');

    //     $students = $query->with('classroom')->paginate(15)->withQueryString();

    //     return view('admin.guidance.index', compact('students'));
    // }
    public function index(Request $request)
{
    $user = Auth::user();

    // 1. Ambil daftar kelas yang diizinkan untuk dropdown filter
    $classrooms = Classroom::accessibleBy($user)->orderBy('name')->get();

    // 2. Query Siswa terfilter otomatis berdasarkan scope accessibleBy
    $query = Student::accessibleBy($user)
        ->leftJoin('student_violations', 'students.id', '=', 'student_violations.student_id')
        ->leftJoin('violation_types', 'student_violations.violation_type_id', '=', 'violation_types.id')
        ->select(
            'students.*',
            DB::raw('COALESCE(SUM(violation_types.points), 0) as total_points')
        )
        ->groupBy('students.id');

    // Filter Kelas
    if ($request->filled('classroom_id')) {
        $query->where('students.classroom_id', $request->classroom_id);
    }

    // Filter Pencarian Nama / NIS
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('students.name', 'like', '%' . $request->search . '%')
              ->orWhere('students.nis', 'like', '%' . $request->search . '%');
        });
    }

    // Hanya ambil siswa yang punya poin > 0
    $query->having(DB::raw('COALESCE(SUM(violation_types.points), 0)'), '>', 0);

    // Urutkan dari poin tertinggi
    $query->orderByDesc('total_points');

    $students = $query->with('classroom')->paginate(15)->withQueryString();

    return view('admin.guidance.index', compact('students', 'classrooms'));
}

    public function create()
    {
        $user = Auth::user();

        // Filter siswa untuk dropdown pembuatan kasus hanya yang diizinkan oleh scope accessibleBy
        $students = Student::accessibleBy($user)->with('classroom')->orderBy('name')->get();
        $violationTypes = ViolationType::orderBy('name')->get();

        return view('admin.guidance.create', compact('students', 'violationTypes'));
    }

    /**
     * Halaman Detail Siswa: Tampilkan History Pelanggaran & Form Pembinaan
     */
    public function show($id)
    {
        $user = Auth::user();

        // Pastikan hanya bisa melihat siswa yang berada dalam wewenang scope accessibleBy
        $student = Student::accessibleBy($user)
            ->with(['classroom', 'violations.type', 'guidances.teacher'])
            ->findOrFail($id);

        $violationTypes = ViolationType::orderBy('name')->get();

        // Cek Role Guru yang sedang login
        $teacher = Teacher::where('user_id', $user->id)->first();

        $role = 'guru_biasa';
        if ($teacher && $student->classroom) {
            if ($student->classroom->homeroom_teacher_id == $teacher->id || $student->classroom->teacher_id == $teacher->id) {
                $role = 'wali_kelas';
            } elseif ($user->hasRole(['guru_bk', 'guru-bk'])) {
                $role = 'bk';
            } elseif ($user->hasRole(['kaprog'])) {
                $role = 'kaprog';
            } elseif ($user->hasRole(['kabeng'])) {
                $role = 'kabeng';
            }
        }

        return view('admin.guidance.show', compact('student', 'violationTypes', 'role'));
    }

    /**
     * Simpan Pelanggaran Baru
     */
    public function storeViolation(Request $request, $student_id)
    {
        $user = Auth::user();

        // Validasi bahwa siswa yang ditambahkan pelanggaran berada dalam hak akses user
        $student = Student::accessibleBy($user)->findOrFail($student_id);

        $request->validate([
            'violation_type_id' => 'required|exists:violation_types,id',
            'date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        StudentViolation::create([
            'student_id' => $student->id,
            'violation_type_id' => $request->violation_type_id,
            'date' => $request->date,
            'note' => $request->note,
            'reported_by' => Auth::id()
        ]);

        return back()->with('success', 'Pelanggaran berhasil dicatat.');
    }

    /**
     * Simpan Hasil Pembinaan
     */
    public function storeGuidance(Request $request, $student_id)
    {
        $user = Auth::user();

        // Validasi bahwa siswa yang dibina berada dalam hak akses user
        $student = Student::accessibleBy($user)->findOrFail($student_id);

        $request->validate([
            'date' => 'required|date',
            'problem_summary' => 'required|string',
            'advice' => 'required|string',
            'student_commitment' => 'required|string',
            'status' => 'required',
        ]);

        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();

        StudentGuidance::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'date' => $request->date,
            'problem_summary' => $request->problem_summary,
            'advice' => $request->advice,
            'student_commitment' => $request->student_commitment,
            'status' => $request->status,
            'role_context' => $request->role_context ?? 'guru',
        ]);

        return back()->with('success', 'Laporan pembinaan berhasil disimpan.');
    }

    /**
     * Cetak Surat Perjanjian Pembinaan
     */
    public function printAgreement($guidanceId)
    {
        $user = Auth::user();

        $guidance = StudentGuidance::with(['student.classroom', 'teacher.user'])
            ->whereHas('student', function($q) use ($user) {
                $q->accessibleBy($user);
            })
            ->findOrFail($guidanceId);

        $student = $guidance->student;

        $school = [
            'name' => Setting::value('school_name', 'SMK NEGERI 1 BUKITTINGGI'),
            'address' => Setting::value('school_address', 'Jalan Iskandar Teja Sukmana'),
            'phone' => Setting::value('school_phone', '(0752) 32330'),
            'email' => Setting::value('school_email', 'smkn1_bkt@yahoo.com'),
            'logo_left' => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),
            'provinsi_name' => Setting::value('provinsi_name', 'SUMATERA BARAT'),
            'sign_city' => Setting::value('sign_city', 'Bukittinggi'),
            'headmaster_name' => Setting::value('sign_name', 'Drs. MUHAMMAD DININ'),
            'headmaster_nip' => Setting::value('sign_nip', '19640817 198903 1 030'),
        ];

        return view('admin.guidance.print_agreement', compact('guidance', 'student', 'school'));
    }

    /**
     * Buat dan Kirim Surat Panggilan Orang Tua (Beserta WA)
     */
    public function sendSummon(Request $request, $guidanceId)
    {
        $user = Auth::user();

        $guidance = StudentGuidance::with(['student.classroom'])
            ->whereHas('student', function($q) use ($user) {
                $q->accessibleBy($user);
            })
            ->findOrFail($guidanceId);

        $student = $guidance->student;

        $school = \App\Models\Setting::pluck('value', 'key')->toArray();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.guidance.summon_pdf', compact('guidance', 'student', 'school', 'request'));

        $fileName = 'surat_panggilan_' . $student->nis . '_' . time() . '.pdf';
        $filePath = 'guidance_summons/' . $fileName;
        \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $pdf->output());

        $guidance->update([
            'is_summoned' => true,
            'summon_date' => $request->summon_date,
            'summon_time' => $request->summon_time,
            'summon_file' => $filePath
        ]);

        $parentPhone = $student->parent_phone ?? $student->phone;

        if ($parentPhone) {
            $dateFormatted = \Carbon\Carbon::parse($request->summon_date)->translatedFormat('l, d F Y');

            $message = "*SURAT PANGGILAN ORANG TUA/WALI*\n\n"
                     . "Yth. Bapak/Ibu Orang Tua/Wali dari *{$student->name}*,\n\n"
                     . "Sehubungan dengan evaluasi kedisiplinan dan pembinaan siswa, kami mengharap kehadiran Bapak/Ibu pada:\n\n"
                     . "📅 Hari/Tgl: {$dateFormatted}\n"
                     . "⏰ Waktu: {$request->summon_time} WIB\n"
                     . "📍 Tempat: Ruang BK / Ruang Kepala Bengkel SMKN 1 Bukittinggi\n\n"
                     . "Mengingat pentingnya hal ini, kami sangat mengharapkan kehadiran Bapak/Ibu tepat pada waktunya. Surat panggilan resmi dapat diunduh melalui tautan berikut:\n"
                     . asset('storage/'.$filePath) . "\n\n"
                     . "Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.\n\n"
                     . "_Hormat Kami,_\n_Pihak Sekolah_";

            try {
                \App\Jobs\SendWhatsappJob::dispatch($parentPhone, $message);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal dispatch WA Summons: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Surat panggilan berhasil dibuat dan otomatis dikirim ke WA Orang Tua.');
    }

    /**
     * Menghapus data pelanggaran (Violation)
     */
    public function destroy($id)
    {
        $pelanggaran = StudentViolation::findOrFail($id);
        $pelanggaran->delete();

        return redirect()->back()->with('success', 'Data riwayat pembinaan berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        $violationType = StudentViolation::findOrFail($id);
        $violationType->update($request->all());

        return redirect()->back()->with('success', 'Data pelanggaran berhasil diperbarui.');
    }
}