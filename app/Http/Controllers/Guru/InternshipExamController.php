<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Internship;
use App\Models\InternshipExam;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InternshipExamExport;

class InternshipExamController extends Controller
{
    private function getTeacher()
    {
        return Teacher::where('user_id', Auth::id())->firstOrFail();
    }

    public function create($internshipId)
    {
        $teacher = $this->getTeacher();

        // $internship = Internship::with(['student.classroom.major', 'industry', 'exam'])
        //     ->where('id', $internshipId)
        //     ->where('advisor_id', $teacher->id)
        //     ->firstOrFail();
        // UBAH BARIS INI: Tambahkan .program pada relasi major
        $internship = Internship::with(['student.classroom.major.program', 'industry', 'exam'])
            ->where('id', $internshipId)
            ->where('advisor_id', $teacher->id)
            ->firstOrFail();

        return view('guru.internships.exam', compact('internship'));
    }

    public function store(Request $request, $internshipId)
    {
        $teacher = $this->getTeacher();

        $internship = Internship::where('id', $internshipId)
            ->where('advisor_id', $teacher->id)
            ->firstOrFail();

        $request->validate([
            'exam_date'         => 'required|date',
            'score_materi'      => 'required|numeric|in:18,25,35',
            'score_gambar'      => 'required|numeric|in:7,10',
            'score_sistematika' => 'required|numeric|in:7,10',
            'score_tampilan'    => 'required|numeric|in:3,5',
            'score_pakaian'     => 'required|numeric|in:3,5',
            'score_bahasa'      => 'required|numeric|in:5,7,10',
            'score_penguasaan'  => 'required|numeric|in:5,10,15',
            'score_jawaban'     => 'required|numeric|in:5,10',
            'status'            => 'required|in:scheduled,passed,revision,failed',
            'revision_notes'    => 'nullable|string',
        ]);

        $subtotalLaporan = $request->score_materi + $request->score_gambar + $request->score_sistematika + $request->score_tampilan;
        $subtotalPresentasi = $request->score_pakaian + $request->score_bahasa + $request->score_penguasaan + $request->score_jawaban;
        $totalScore = $subtotalLaporan + $subtotalPresentasi;

        InternshipExam::updateOrCreate(
            ['internship_id' => $internship->id],
            [
                'examiner_id'         => $teacher->id,
                'exam_date'           => $request->exam_date,
                'score_materi'        => $request->score_materi,
                'score_gambar'        => $request->score_gambar,
                'score_sistematika'   => $request->score_sistematika,
                'score_tampilan'      => $request->score_tampilan,
                'subtotal_laporan'    => $subtotalLaporan,
                'score_pakaian'       => $request->score_pakaian,
                'score_bahasa'        => $request->score_bahasa,
                'score_penguasaan'    => $request->score_penguasaan,
                'score_jawaban'       => $request->score_jawaban,
                'subtotal_presentasi' => $subtotalPresentasi,
                'total_score'         => $totalScore,
                'status'              => $request->status,
                'revision_notes'      => $request->revision_notes,
            ]
        );

        return redirect()->route('teacher.internships.index')
            ->with('success', 'Nilai Ujian Jurnal/Laporan siswa ' . $internship->student->name . ' berhasil disimpan.');
    }

    /**
     * Cetak Lembar Nilai Presentasi Laporan (Ujian Jurnal) PKL (PDF)
     */
    public function printExamResult($internshipId)
    {
        $teacher = $this->getTeacher();

        // $internship = Internship::with(['student.classroom.major', 'industry', 'exam'])
        //     ->where('id', $internshipId)
        //     ->where('advisor_id', $teacher->id)
        //     ->firstOrFail();
        // Tambahkan .program juga di sini
        $internship = Internship::with(['student.classroom.major.program', 'industry', 'exam'])
            ->where('id', $internshipId)
            ->where('advisor_id', $teacher->id)
            ->firstOrFail();

        if (!$internship->exam) {
            return redirect()->back()->with('error', 'Nilai Ujian Jurnal belum diisi.');
        }

        $school = [
            'name'            => \App\Models\Setting::value('school_name', 'SMK NEGERI 1 BUKITTINGGI'),
            'sign_city'       => \App\Models\Setting::value('sign_city', 'Bukittinggi'),
            'headmaster_name' => \App\Models\Setting::value('sign_name', 'FITHRI NOVILIA, S.Pd'),
            'headmaster_nip'  => \App\Models\Setting::value('sign_nip', '19721102 199802 2 003'),
        ];

        

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('guru.internships.exam_pdf', compact('internship', 'school', 'teacher'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Nilai_Ujian_Jurnal_' . str_replace(' ', '_', $internship->student->name) . '.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new InternshipExamExport, 'Nilai_Ujian_Jurnal_PKL.xlsx');
    }
}