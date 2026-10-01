<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classroom;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClassPromotionController extends Controller
{
    /**
     * Menampilkan Halaman Kenaikan Kelas & Kelulusan
     */
    public function index(Request $request)
    {
        $classrooms = Classroom::orderBy('name', 'asc')->get();
        $selectedClassroomId = $request->query('classroom_id');

        $students = collect();
        $currentClassroom = null;

        if ($selectedClassroomId) {
            $currentClassroom = Classroom::findOrFail($selectedClassroomId);

            // Menampilkan siswa aktif yang BELUM diproses di periode ini
            $students = Student::where('classroom_id', $selectedClassroomId)
                ->where('status', 'active')
                ->where('is_promoted', false)
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('promotions.index', compact('classrooms', 'students', 'currentClassroom', 'selectedClassroomId'));
    }

    /**
     * Memproses Kenaikan Kelas / Kelulusan Siswa
     */
    public function process(Request $request)
    {
        $request->validate([
            'source_classroom_id' => 'required|exists:classrooms,id',
            'students' => 'required|array',
            'students.*.action' => 'required|in:promote,graduate,stay',
            'students.*.target_classroom_id' => 'nullable|required_if:students.*.action,promote|exists:classrooms,id',
            'graduation_year' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $hasGraduation = collect($request->students)->contains('action', 'graduate');
                    if ($hasGraduation && empty($value)) {
                        $fail('Tahun kelulusan wajib diisi jika ada siswa yang diluluskan.');
                    }
                },
            ],
            'graduation_date' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $hasGraduation = collect($request->students)->contains('action', 'graduate');
                    if ($hasGraduation && empty($value)) {
                        $fail('Tanggal kelulusan wajib diisi jika ada siswa yang diluluskan.');
                    }
                },
            ],
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->students as $studentId => $data) {
                $student = Student::findOrFail($studentId);

                if ($data['action'] === 'graduate') {
                    // Siswa Lulus
                    $student->update([
                        'status' => 'graduated',
                        'last_classroom_id' => $student->classroom_id,
                        'classroom_id' => null,
                        'is_promoted' => true,
                    ]);

                    Alumni::create([
                        'id' => (string) Str::uuid(),
                        'student_id' => $student->id,
                        'graduation_year' => $request->graduation_year,
                        'graduation_date' => $request->graduation_date,
                        'phone' => $student->phone,
                        'address' => $student->address,
                    ]);

                } elseif ($data['action'] === 'promote') {
                    // Siswa Naik Kelas
                    $student->update([
                        'classroom_id' => $data['target_classroom_id'],
                        'is_promoted' => true,
                    ]);

                } elseif ($data['action'] === 'stay') {
                    // Siswa Tinggal Kelas
                    $student->update([
                        'is_promoted' => true,
                    ]);
                }
            }
        });

        return redirect()->route('promotions.index', ['classroom_id' => $request->source_classroom_id])
            ->with('success', 'Proses kenaikan kelas dan kelulusan berhasil diperbarui!');
    }

    /**
     * Me-reset status flag penanda saat memulai Tahun Ajaran Baru
     */
    public function resetPromotion()
    {
        Student::where('status', 'active')->update(['is_promoted' => false]);
        return redirect()->back()->with('success', 'Status proses kenaikan kelas telah di-reset untuk Tahun Ajaran Baru.');
    }
}