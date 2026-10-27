<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentViolation;

class StudentProfileController extends Controller
{
    public function violations()
    {
        $user = auth()->user();
        
        // Pastikan user adalah siswa dan terhubung dengan data Student
        $student = $user->student; 
        
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        // Ambil riwayat pelanggaran milik siswa
        $violations = StudentViolation::with('violationType')
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->get();

        // Hitung total poin pelanggaran yang didapat
        $totalPoints = $violations->sum(function ($violation) {
            return $violation->violationType->points ?? 0;
        });

        return view('students.violations.index', compact('violations', 'totalPoints'));
    }
}
