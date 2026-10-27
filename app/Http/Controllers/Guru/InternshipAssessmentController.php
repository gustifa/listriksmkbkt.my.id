<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Internship;
use App\Models\InternshipGrade;
use App\Models\InternshipAttendance;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;

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

        if (!$teacher->isAdvisor()) {
            return redirect()->route('dashboard')->with('error', 'Menu PKL hanya dapat diakses oleh Guru Pembimbing yang telah ditentukan Admin.');
        }

        $internships = Internship::with(['student.classroom', 'industry', 'grade'])
            ->where('advisor_id', $teacher->id)
            ->whereIn('status', ['active', 'completed'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('guru.internships.index', compact('internships'));
    }

    /**
     * Form Penilaian Siswa
     */
    public function create($id)
    {
        $teacher = $this->getTeacher();

        $internship = Internship::with(['student.classroom.major', 'industry', 'grade'])
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

        // 3. Decode Rincian Nilai jika Sudah Ada
        if ($internship->grade && $internship->grade->grade_details) {
            $internship->grade_details = is_array($internship->grade->grade_details) 
                ? $internship->grade->grade_details 
                : json_decode($internship->grade->grade_details, true);
        }

        return view('guru.internships.assess', compact('internship', 'attendances', 'summary'));
    }

    /**
     * Simpan Nilai PKL (UPDATE: Menyimpan Array Grade Details ke JSON)
     */
    public function store(Request $request, $id)
    {
        $teacher = $this->getTeacher();
        $internship = Internship::where('id', $id)->where('advisor_id', $teacher->id)->firstOrFail();

        // 1. Validasi Input Dinamis Array Scores
        $request->validate([
            'scores'           => 'required|array',
            'scores.*'         => 'nullable|numeric|min:0|max:100',
            'hardskill_labels' => 'nullable|array',
            'notes'            => 'nullable|string'
        ]);

        $scores = $request->input('scores', []);
        $hardskillLabels = $request->input('hardskill_labels', []);

        // 2. Gabungkan Label Custom Hard Skill ke Array Details
        $gradeDetails = $scores;
        foreach ($hardskillLabels as $key => $label) {
            if (!empty($label)) {
                $gradeDetails['hardskill_title_' . $key] = $label;
            }
        }

        // 3. Hitung Rata-Rata Nilai Akhir (Final Score) dari Seluruh Komponen yang Terisi (> 0)
        $validScores = array_filter($scores, function ($val) {
            return is_numeric($val) && $val > 0;
        });

        $totalScore = array_sum($validScores);
        $count = count($validScores);
        $finalScore = $count > 0 ? round($totalScore / $count, 2) : 0;

        // 4. Simpan / Update ke Database
        // Catatan: Jika Model InternshipGrade sudah pasang $casts ['grade_details' => 'array'], 
        // passing array $gradeDetails langsung tanpa json_encode.
        InternshipGrade::updateOrCreate(
            ['internship_id' => $internship->id],
            [
                'student_id'    => $internship->student_id,
                'teacher_id'    => $teacher->id,
                'final_score'   => $finalScore,
                'grade_details' => $gradeDetails,
                'notes'         => $request->notes
            ]
        );

        return redirect()->route('teacher.internships.index')
            ->with('success', 'Nilai PKL untuk siswa ' . $internship->student->name . ' berhasil disimpan.');
    }

    /**
     * CETAK SERTIFIKAT PKL (PDF PORTRAIT)
     */
    public function printCertificate($id)
    {
        $teacher = $this->getTeacher();

        $internship = Internship::with(['student.classroom.major', 'industry', 'grade'])
            ->where('id', $id)
            ->where('advisor_id', $teacher->id)
            ->firstOrFail();

        if (!$internship->grade) {
            return redirect()->back()->with('error', 'Sertifikat tidak dapat dicetak karena penilaian belum selesai.');
        }

        $score = $internship->grade->final_score;
        $predikat = 'Cukup';
        if ($score >= 90) $predikat = 'Sangat Baik';
        elseif ($score >= 80) $predikat = 'Baik';
        elseif ($score >= 70) $predikat = 'Cukup';
        else $predikat = 'Kurang';

        $school = [
            'name'            => Setting::value('school_name', 'SMK NEGERI 1 BUKITTINGGI'),
            'logo_left'       => Setting::value('logo_left'),
            'sign_city'       => Setting::value('sign_city', 'Bukittinggi'),
            'headmaster_name' => Setting::value('sign_name', '.........................'),
            'headmaster_nip'  => Setting::value('sign_nip', '.........................'),
        ];

        $pdf = Pdf::loadView('guru.internships.certificate_pdf', compact('internship', 'school', 'teacher', 'predikat'));
        
        // PENTING: Set Kertas A4 Portrait agar pas 2 Halaman
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Sertifikat_PKL_' . str_replace(' ', '_', $internship->student->name) . '.pdf');
    }
}