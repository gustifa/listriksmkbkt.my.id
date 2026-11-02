<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\InternshipTpScore;
use App\Models\TpMaster;
use App\Models\Teacher;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InternshipTpScoreController extends Controller
{
    private function getTeacher()
    {
        return Teacher::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * Tampilkan Form Penilaian Observasi berbasis Master TP Dinamis
     */
    public function create($internshipId)
    {
        $teacher = $this->getTeacher();
        $internship = Internship::with(['student.classroom.major.program', 'industry', 'tpScore'])
            ->where('id', $internshipId)
            ->where('advisor_id', $teacher->id)
            ->firstOrFail();

        $majorId = $internship->student->classroom->major_id ?? null;

        $tpMasters = TpMaster::where('major_id', $majorId)
            ->orWhereNull('major_id')
            ->orderBy('code', 'asc')
            ->get();

        return view('guru.internships.tp_dynamic_assessment', compact('internship', 'tpMasters'));
    }

    /**
     * Simpan / Update Penilaian Observasi TP Siswa
     */
    public function store(Request $request, $internshipId)
    {
        $teacher = $this->getTeacher();
        $internship = Internship::where('id', $internshipId)
            ->where('advisor_id', $teacher->id)
            ->firstOrFail();

        $inputScores = $request->input('scores', []);
        $inputNotes  = $request->input('notes', []);

        $majorId = $internship->student->classroom->major_id ?? null;
        $tpMasters = TpMaster::where('major_id', $majorId)->orWhereNull('major_id')->get();

        $totalTpFinalScores = [];

        foreach ($tpMasters as $tp) {
            $tpCode = $tp->code;
            // Ambil array skor berdasarkan kode asli atau format slug
            $scores = $inputScores[$tpCode] ?? ($inputScores[\Illuminate\Support\Str::slug($tpCode, '-')] ?? []);
            
            $obtainedScore = array_sum(array_map('intval', $scores));
            $totalIndicators = count($tp->indicators ?? []);
            $maxPossibleScore = $totalIndicators * ($tp->max_score_per_item ?? 4);

            $finalScore = $maxPossibleScore > 0 ? ($obtainedScore / $maxPossibleScore) * 100 : 0;
            
            if ($maxPossibleScore > 0) {
                $totalTpFinalScores[] = $finalScore;
            }
        }

        // Hitung rata-rata nilai akhir seluruh TP (Skala 0 - 100)
        $finalTpAverage = count($totalTpFinalScores) > 0 ? array_sum($totalTpFinalScores) / count($totalTpFinalScores) : 0;

        InternshipTpScore::updateOrCreate(
            ['internship_id' => $internship->id],
            [
                'scores'         => $inputScores,
                'notes'          => $inputNotes,
                'final_tp_score' => round($finalTpAverage, 2),
            ]
        );

        return redirect()->route('guru.internships.index')->with('success', 'Penilaian Observasi TP Siswa berhasil disimpan!');
    }

    /**
     * Cetak Lembar Rapor / Transkrip Nilai PKL Siswa (PDF)
     */
    public function printTranscript($internshipId)
    {
        $teacher = $this->getTeacher();

        $internship = Internship::with([
            'student.classroom.major.program',
            'industry',
            'tpScore',
            'exam'
        ])
        ->where('id', $internshipId)
        ->firstOrFail();

        $majorId = $internship->student->classroom->major_id ?? null;

        $tpMasters = TpMaster::where('major_id', $majorId)
            ->orWhereNull('major_id')
            ->orderBy('code', 'asc')
            ->get();

        $school = [
            'name'            => Setting::where('key', 'school_name')->value('value') ?? 'SMK NEGERI 1 BUKITTINGGI',
            'sign_city'       => Setting::where('key', 'sign_city')->value('value') ?? 'Bukittinggi',
            'headmaster_name' => Setting::where('key', 'sign_name')->value('value') ?? 'FITHRI NOVILIA, S.Pd',
            'headmaster_nip'  => Setting::where('key', 'sign_nip')->value('value') ?? '19721102 199802 2 003',
        ];

        $pdf = Pdf::loadView('guru.internships.transcript_pdf', compact('internship', 'tpMasters', 'school', 'teacher'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Rapor_PKL_' . str_replace(' ', '_', $internship->student->name) . '.pdf');
    }
}