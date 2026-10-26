<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AlumniProfile;
use App\Models\AlumniSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AlumniController extends Controller
{
    public function index()
    {
        return view('admin.alumni.index');
    }

    // Ambil Data Siswa Berstatus Lulus (graduated) via AJAX
    public function getData(Request $request)
    {
        $alumni = Student::with(['alumniProfile', 'alumniSkills'])
            ->where('status', 'graduated')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('nisn', 'LIKE', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return response()->json($alumni);
    }

    // Ambil Detail Profil & Skill 1 Alumni
    public function show($student_id)
    {
        $student = Student::with(['alumniProfile', 'alumniSkills'])->findOrFail($student_id);
        return response()->json($student);
    }

    // Simpan / Update Profil Alumni & File CV
    public function storeProfile(Request $request)
    {
        $request->validate([
            'student_id'      => 'required|exists:students,id',
            'graduation_year' => 'required|numeric',
            'current_status'  => 'required|in:working,studying,entrepreneur,searching',
            'phone_number'    => 'nullable|string',
            'cv_file'         => 'nullable|mimes:pdf|max:2048',
        ]);

        $profile = AlumniProfile::firstOrNew(['student_id' => $request->student_id]);
        $profile->graduation_year = $request->graduation_year;
        $profile->current_status  = $request->current_status;
        $profile->current_company = $request->current_company;
        $profile->position        = $request->position;
        $profile->phone_number    = $request->phone_number;
        $profile->linkedin_url    = $request->linkedin_url;

        // Upload CV PDF jika ada
        if ($request->hasFile('cv_file')) {
            if ($profile->cv_file) {
                Storage::disk('public')->delete($profile->cv_file);
            }
            $profile->cv_file = $request->file('cv_file')->store('alumni_cv', 'public');
        }

        $profile->save();

        return response()->json(['status' => 'success', 'message' => 'Profil Alumni berhasil diperbarui!']);
    }

    // Tambah Skill & Sertifikat
    public function storeSkill(Request $request)
    {
        $request->validate([
            'student_id'        => 'required|exists:students,id',
            'skill_name'        => 'required|string',
            'certificate_name'  => 'nullable|string',
            'level'             => 'required|in:basic,intermediate,advanced',
        ]);

        AlumniSkill::create([
            'student_id'       => $request->student_id,
            'skill_name'       => $request->skill_name,
            'certificate_name' => $request->certificate_name,
            'level'            => $request->level,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Kompetensi berhasil ditambahkan!']);
    }

    // Hapus Skill
    public function deleteSkill($id)
    {
        $skill = AlumniSkill::findOrFail($id);
        $skill->delete();

        return response()->json(['status' => 'success', 'message' => 'Kompetensi berhasil dihapus!']);
    }
}