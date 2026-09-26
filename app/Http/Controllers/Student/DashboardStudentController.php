<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use App\Models\InternshipTimeline; // Import Model Timeline

class DashboardStudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userAktif = $user->status;
        // dd($userAktif);

        // 1. Cari Data Guru
        $student = Student::where('user_id', $user->id)->first();

        if (!$student || $userAktif != 1 ) {
            return view('guru.teacher_dashboard.empty', compact('teacher'));
        }

        // --- FITUR BARU: AMBIL TIMELINE KEGIATAN ---
        $timelines = InternshipTimeline::orderBy('start_date', 'asc')->get();


        return view('siswa.siswa_dashboard.index', compact(
            'student',
            'timelines'
        ));
    }
}
