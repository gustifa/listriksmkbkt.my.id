<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Internship;
use App\Models\InternshipGrade;
use App\Models\InternshipAttendance; // Import Model Absensi
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan DomPDF terinstall
use App\Models\Setting; // Import Setting untuk data sekolah


class InternshipAssessmentController extends Controller
{
    /**
     * Helper: Ambil Data Guru Login
     */
    private function getTeacher()
    {
        return Teacher::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * Daftar Siswa Bimbingan PKL
     */
    public function index()
    {
        $teacher = $this->getTeacher();

        // --- GATEKEEPING: Cek apakah Guru ini Pembimbing? ---
        // Menggunakan method isAdvisor() yang sudah ditambahkan di Model Teacher
        if (!$teacher->isAdvisor()) {
            // Jika bukan pembimbing, kembalikan ke dashboard dengan pesan error
            return redirect()->route('dashboard')->with('error', 'Menu PKL hanya dapat diakses oleh Guru Pembimbing yang telah ditentukan Admin.');
        }

        // Ambil data PKL dimana guru ini adalah PEMBIMBINGNYA (advisor_id)
        $internships = Internship::with(['student.classroom', 'industry', 'grade'])
            ->where('advisor_id', $teacher->id)
            ->whereIn('status', ['active', 'completed']) // Hanya tampilkan yang aktif/selesai
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('guru.internships.index', compact('internships'));
    }

    // /**
    //  * Form Penilaian Siswa
    //  */
    // public function create($id)
    // {
    //     $teacher = $this->getTeacher();

    //     // Pastikan akses valid (hanya pembimbing sendiri)
    //     $internship = Internship::with(['student', 'industry', 'grade'])
    //         ->where('id', $id)
    //         ->where('advisor_id', $teacher->id)
    //         ->firstOrFail();

    //     return view('guru.internships.assess', compact('internship'));
    // }

    /**
     * Form Penilaian Siswa (UPDATE: Tambah Data Jurnal & Absensi)
     */
    public function create($id)
    {
        $teacher = $this->getTeacher();

        // Pastikan akses valid (hanya pembimbing sendiri)
        $internship = Internship::with(['student.classroom', 'industry', 'grade'])
            ->where('id', $id)
            ->where('advisor_id', $teacher->id)
            ->firstOrFail();

        // 1. Ambil Riwayat Absensi & Jurnal
        $attendances = InternshipAttendance::where('internship_id', $internship->id)
            ->orderBy('date', 'desc')
            ->get();

        // 2. Hitung Statistik Kehadiran
        $summary = [
            'total'   => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'sick'    => $attendances->where('status', 'sick')->count(),
            'permit'  => $attendances->where('status', 'permit')->count(),
            'alpha'   => $attendances->where('status', 'alpha')->count(),
        ];

        return view('guru.internships.assess', compact('internship', 'attendances', 'summary'));
    }

    /**
     * Simpan Nilai
     */
    public function store(Request $request, $id)
    {
        $teacher = $this->getTeacher();
        $internship = Internship::where('id', $id)->where('advisor_id', $teacher->id)->firstOrFail();

        $request->validate([
            'discipline' => 'required|integer|min:0|max:100',
            'teamwork' => 'required|integer|min:0|max:100',
            'initiative' => 'required|integer|min:0|max:100',
            'responsibility' => 'required|integer|min:0|max:100',
            'technical_mastery' => 'required|integer|min:0|max:100',
            'work_quality' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        // Hitung Nilai Akhir (Contoh: Rata-rata sederhana)
        $totalScore = (
            $request->discipline +
            $request->teamwork +
            $request->initiative +
            $request->responsibility +
            $request->technical_mastery +
            $request->work_quality
        ) / 6;

        InternshipGrade::updateOrCreate(
            ['internship_id' => $internship->id],
            [
                'student_id' => $internship->student_id,
                'teacher_id' => $teacher->id,
                'discipline' => $request->discipline,
                'teamwork' => $request->teamwork,
                'initiative' => $request->initiative,
                'responsibility' => $request->responsibility,
                'technical_mastery' => $request->technical_mastery,
                'work_quality' => $request->work_quality,
                'final_score' => round($totalScore, 2),
                'notes' => $request->notes
            ]
        );

        return redirect()->route('teacher.internships.index')
            ->with('success', 'Nilai PKL untuk siswa ' . $internship->student->name . ' berhasil disimpan.');
    }

     /**
     * CETAK SERTIFIKAT PKL (PDF)
     */
    public function printCertificate($id)
    {
        $teacher = $this->getTeacher();

        // Ambil data internship + nilai
        $internship = Internship::with(['student.classroom', 'industry', 'grade'])
            ->where('id', $id)
            ->where('advisor_id', $teacher->id)
            ->firstOrFail();

        // Cek apakah nilai sudah ada
        if (!$internship->grade) {
            return redirect()->back()->with('error', 'Sertifikat tidak dapat dicetak karena penilaian belum selesai.');
        }

        // Tentukan Predikat
        $score = $internship->grade->final_score;
        $predikat = 'Cukup';
        if ($score >= 90) $predikat = 'Sangat Baik';
        elseif ($score >= 80) $predikat = 'Baik';
        elseif ($score >= 70) $predikat = 'Cukup';
        else $predikat = 'Kurang';

        // Data Sekolah & Tanda Tangan
        $school = [
            'name' => Setting::value('school_name', 'SMK NEGERI 1 BUKITTINGGI'),
            'logo_left' => Setting::value('logo_left'),
            'sign_city' => Setting::value('sign_city', 'Bukittinggi'),

            // Tanda Tangan Kepala Sekolah
            'headmaster_name' => Setting::value('sign_name', '.........................'),
            'headmaster_nip' => Setting::value('sign_nip', '.........................'),
        ];

        $pdf = Pdf::loadView('guru.internships.certificate_pdf', compact('internship', 'school', 'teacher', 'predikat'));

        // Setup Kertas Landscape (Sertifikat)
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Sertifikat_PKL_' . str_replace(' ', '_', $internship->student->name) . '.pdf');
    }
}
