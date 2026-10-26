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

class GuidanceController extends Controller
{
    /**
     * Dashboard Pembinaan: List Siswa Bermasalah (Poin Tertinggi)
     */
    // public function index(Request $request)
    // {
    //     // Ambil siswa yang memiliki pelanggaran, urutkan dari poin tertinggi
    //     $students = Student::withSum('violations as total_points', 'violation_types.points')
    //         ->with(['classroom'])
    //         ->having('total_points', '>', 0)
    //         ->orderByDesc('total_points');

    //     return view('admin.guidance.index', compact('students'));
    // }

    /**
     * Dashboard Pembinaan: List Siswa Bermasalah (Poin Tertinggi)
     */
    public function index(Request $request)
    // {
    //     // Query Siswa dengan Total Poin Pelanggaran
    //     $query = Student::query();

    //     // Join ke tabel student_violations dan violation_types untuk menghitung total poin
    //     // Menggunakan subquery agar bisa diurutkan dan difilter
    //     $query->select('students.*')
    //           ->selectSub(function ($q) {
    //               $q->from('student_violations')
    //                 ->join('violation_types', 'student_violations.violation_type_id', '=', 'violation_types.id')
    //                 ->whereColumn('student_violations.student_id', 'students.id')
    //                 ->selectRaw('COALESCE(SUM(violation_types.points), 0)');
    //           }, 'total_points');

    //     // Filter Pencarian
    //     if ($request->filled('search')) {
    //         $query->where(function($q) use ($request) {
    //             $q->where('name', 'like', '%' . $request->search . '%')
    //               ->orWhere('nis', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     // Hanya ambil siswa yang punya poin > 0
    //     // Karena 'total_points' adalah alias hasil subquery, kita gunakan having
    //     $query->having('total_points', '>', 0);

    //     // Urutkan dari poin tertinggi
    //     $query->orderByDesc('total_points');

    //     $students = $query->with('classroom')->paginate(15)->withQueryString();

    //     return view('admin.guidance.index', compact('students'));
    // }

        {
        // Query Siswa dengan Total Poin Pelanggaran
        // Menggunakan JOIN dan GROUP BY agar kompatibel dengan PostgreSQL dan HAVING
        $query = Student::query()
            ->leftJoin('student_violations', 'students.id', '=', 'student_violations.student_id')
            ->leftJoin('violation_types', 'student_violations.violation_type_id', '=', 'violation_types.id')
            ->select(
                'students.*',
                DB::raw('COALESCE(SUM(violation_types.points), 0) as total_points')
            )
            ->groupBy('students.id');

        // Filter Pencarian
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

        return view('admin.guidance.index', compact('students'));
    }


    public function create(){
        $students = Student::with('classroom')->orderBy('name')->get();
        // Ambil jenis pelanggaran
        $violationTypes = ViolationType::orderBy('name')->get();

        return view('admin.guidance.create', compact('students', 'violationTypes'));
    }

    /**
     * Halaman Detail Siswa: Tampilkan History Pelanggaran & Form Pembinaan
     */
    public function show($id)
    {
        $student = Student::with(['classroom', 'violations.type', 'guidances.teacher'])->findOrFail($id);
        $violationTypes = ViolationType::orderBy('name')->get();

        // Cek Role Guru yang sedang login
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        $role = 'guru_biasa';
        if($teacher) {
            // Logika sederhana penentuan role (bisa disesuaikan dengan logic role Anda)
            if ($student->classroom->homeroom_teacher_id == $teacher->id) $role = 'wali_kelas';
            // elseif (cek_apakah_guru_bk) $role = 'bk';
            // elseif (cek_apakah_kaprog) $role = 'kaprog';
        }

        return view('admin.guidance.show', compact('student', 'violationTypes', 'role'));
    }

    /**
     * Simpan Pelanggaran Baru
     */
    public function storeViolation(Request $request, $student_id)
    {
        $request->validate([
            'violation_type_id' => 'required|exists:violation_types,id',
            'date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        StudentViolation::create([
            'student_id' => $student_id,
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
        $request->validate([
            'date' => 'required|date',
            'problem_summary' => 'required|string',
            'advice' => 'required|string',
            'student_commitment' => 'required|string',
            'status' => 'required',
        ]);

        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();

        StudentGuidance::create([
            'student_id' => $student_id,
            'teacher_id' => $teacher->id,
            'date' => $request->date,
            'problem_summary' => $request->problem_summary,
            'advice' => $request->advice,
            'student_commitment' => $request->student_commitment,
            'status' => $request->status,
            'role_context' => $request->role_context ?? 'guru', // Dikirim dari hidden input view
        ]);

        return back()->with('success', 'Laporan pembinaan berhasil disimpan.');
    }

    /**
     * Cetak Surat Perjanjian Pembinaan
     */
    public function printAgreement($guidanceId)
    {
        // Cari data pembinaan beserta relasinya
        $guidance = StudentGuidance::with(['student.classroom', 'teacher.user'])->findOrFail($guidanceId);
        $student = $guidance->student;

        // Ambil data sekolah dari pengaturan (atau gunakan default)
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

        // Tampilkan view print (HTML biasa tanpa butuh library PDF agar format A4 pas)
        return view('admin.guidance.print_agreement', compact('guidance', 'student', 'school'));
    }

    /**
     * Buat dan Kirim Surat Panggilan Orang Tua (Beserta WA)
     */
    public function sendSummon(Request $request, $guidanceId)
    {
        // $request->validate([
        //     'summon_date' => 'required|date',
        //     'summon_time' => 'required',
        // ]);

        $guidance = StudentGuidance::with(['student.classroom'])->findOrFail($guidanceId);
        $student = $guidance->student;

        // 1. Ambil Data Sekolah
        $school = \App\Models\Setting::pluck('value', 'key')->toArray();

        // 2. Generate PDF Surat Panggilan
        // Menggunakan view summon_pdf yang sudah Anda buat sebelumnya
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.guidance.summon_pdf', compact('guidance', 'student', 'school', 'request'));

        // Simpan PDF ke Storage public
        $fileName = 'surat_panggilan_' . $student->nis . '_' . time() . '.pdf';
        $filePath = 'guidance_summons/' . $fileName;
        \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $pdf->output());

        // 3. Update Database Status Panggilan
        $guidance->update([
            'is_summoned' => true,
            'summon_date' => $request->summon_date,
            'summon_time' => $request->summon_time,
            'summon_file' => $filePath
        ]);

        // 4. Kirim WhatsApp ke Orang Tua
        // Asumsi model Student memiliki kolom 'parent_phone', jika tidak fallback ke nomor HP siswa
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

            // Memasukkan pengiriman WA ke dalam antrean (Queue) agar tidak memblokir respon halaman
            try {
                \App\Jobs\SendWhatsappJob::dispatch($parentPhone, $message);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal dispatch WA Summons: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Surat panggilan berhasil dibuat dan otomatis dikirim ke WA Orang Tua.');
    }

    /**
     * Menghapus data pembinaan (Guidance)
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
