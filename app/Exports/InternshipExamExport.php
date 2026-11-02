<?php

namespace App\Exports;

use App\Models\Internship;
use App\Models\Teacher;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;

class InternshipExamExport implements FromView, ShouldAutoSize
{
    public function view(): View
    {
        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();

        // Ambil semua siswa bimbingan yang sudah ada nilai ujian jurnalnya
        $internships = Internship::with(['student.classroom.major', 'exam'])
            ->where('advisor_id', $teacher->id)
            ->whereHas('exam')
            ->get();

        $school = [
            'name'            => Setting::value('school_name', 'SMK NEGERI 1 BUKITTINGGI'),
            'sign_city'       => Setting::value('sign_city', 'Bukittinggi'),
            'headmaster_name' => Setting::value('sign_name', 'FITHRI NOVILIA, S.Pd'),
            'headmaster_nip'  => Setting::value('sign_nip', '19721102 199802 2 003'),
        ];

        return view('exports.internship_exam_excel', compact('internships', 'teacher', 'school'));
    }
}