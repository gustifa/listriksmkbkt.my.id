<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\AlumniProfile;
use App\Models\AlumniSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AlumniPortalController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $student = $user->student;
        $profile = $student ? AlumniProfile::where('student_id', $student->id)->first() : null;
        $latestJobs = JobPosting::with('company')->where('status', 'open')->latest()->take(5)->get();

        return view('admin.alumni.dashboard', compact('user', 'student', 'profile', 'latestJobs'));
    }

    public function profile()
    {
        $student = Auth::user()->student;
        $profile = AlumniProfile::firstOrCreate(['student_id' => $student->id], [
            'graduation_year' => date('Y'),
            'current_status'  => 'searching'
        ]);
        $skills = AlumniSkill::where('student_id', $student->id)->get();

        return view('alumni.profile', compact('student', 'profile', 'skills'));
    }

    public function updateProfile(Request $request)
    {
        $student = Auth::user()->student;
        $request->validate([
            'graduation_year' => 'required|numeric',
            'current_status'  => 'required|in:working,studying,entrepreneur,searching',
            'cv_file'         => 'nullable|mimes:pdf|max:2048'
        ]);

        $profile = AlumniProfile::firstOrNew(['student_id' => $student->id]);
        $profile->graduation_year = $request->graduation_year;
        $profile->current_status  = $request->current_status;
        $profile->current_company = $request->current_company;
        $profile->position        = $request->position;
        $profile->phone_number    = $request->phone_number;
        $profile->linkedin_url    = $request->linkedin_url;

        if ($request->hasFile('cv_file')) {
            if ($profile->cv_file) {
                Storage::disk('public')->delete($profile->cv_file);
            }
            $profile->cv_file = $request->file('cv_file')->store('alumni_cv', 'public');
        }

        $profile->save();

        return response()->json(['status' => 'success', 'message' => 'Profil Tracer Study berhasil diperbarui!']);
    }
}