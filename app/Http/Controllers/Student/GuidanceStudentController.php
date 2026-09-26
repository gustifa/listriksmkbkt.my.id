<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentGuidance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuidanceStudentController extends Controller
{
    /**
     * Tampilkan Halaman Riwayat Pelanggaran & Pembinaan (Siswa)
     */
    public function index()
    {
        $user = Auth::user();
        
        // Ambil data siswa berdasarkan user login beserta riwayatnya
        $student = Student::with(['classroom', 'violations.type', 'guidances.teacher'])
                    ->where('user_id', $user->id)
                    ->firstOrFail();

        return view('siswa.guidance.index', compact('student'));
    }

    /**
     * Proses Upload Surat Perjanjian
     */
    public function uploadAgreement(Request $request, $guidanceId)
    {
        $request->validate([
            'agreement_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        $guidance = StudentGuidance::findOrFail($guidanceId);

        // Validasi Kepemilikan (Pastikan ini milik siswa yang sedang login)
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        if ($guidance->student_id !== $student->id) {
            abort(403, 'Anda tidak memiliki akses ke data pembinaan ini.');
        }

        // Simpan File
        if ($request->hasFile('agreement_file')) {
            // Hapus file lama jika ada
            if ($guidance->agreement_file) {
                Storage::disk('public')->delete($guidance->agreement_file);
            }
            
            $path = $request->file('agreement_file')->store('guidance_agreements', 'public');
            $guidance->update(['agreement_file' => $path]);
        }

        return redirect()->back()->with('success', 'Surat Perjanjian berhasil diunggah.');
    }
}