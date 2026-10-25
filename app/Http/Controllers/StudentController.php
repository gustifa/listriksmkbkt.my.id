<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // 1. Import Trait

class StudentController extends Controller
{
    /**
     * Tampilkan Daftar Siswa (dengan Pencarian & Pagination)
     */
    // public function index(Request $request)
    // {
    //     $query = Student::with('classroom');

    //     // Logika Pencarian
    //     if ($request->has('search')) {
    //         $search = $request->search;
    //         $query->where(function($q) use ($search) {
    //             $q->where('name', 'LIKE', "%{$search}%")
    //               ->orWhere('nis', 'LIKE', "%{$search}%");
    //         });
    //     }

    //     // Filter Kelas (Opsional)
    //     if ($request->has('classroom_id') && $request->classroom_id != '') {
    //         $query->where('classroom_id', $request->classroom_id);
    //     }

    //     // Ambil data dengan pagination (10 per halaman)
    //     //$students = $query->orderBy('classroom_id')->orderBy('name')->paginate(10);
    //     $students = $query->orderBy('classroom_id')->orderBy('name')->where('status', 'active')->get()->all();

    //     // Ambil daftar kelas untuk filter
    //     $classrooms = Classroom::orderBy('name')->get();

    //     return view('students.index', compact('students', 'classrooms'));
    // }

//     public function index(Request $request)
// {
//     // $query = Student::with('classroom');
//     // 1. Terapkan scope accessibleBy agar query disaring dari awal
//     $query = Student::accessibleBy($request->user())->with('classroom');

//     if ($request->filled('search')) {
//         $search = $request->search;
//         $query->where(function($q) use ($search) {
//             // Gunakan ILIKE khusus PostgreSQL agar case-insensitive
//             $q->where('name', 'ilike', "%{$search}%")
//               ->orWhere('nis', 'ilike', "%{$search}%");
//         });
//     }

//     if ($request->filled('classroom_id')) {
//         $query->where('classroom_id', $request->classroom_id);
//     }

//     $students = $query->orderBy('name')->paginate(10);
//     $classrooms = Classroom::orderBy('name')->get();

//     // Jika request dikirim via AJAX, kembalikan hanya partial view tabel
//     if ($request->ajax()) {
//         return view('students.partials.student_table', compact('students'))->render();
//     }

//     return view('students.index', compact('students', 'classrooms'));
// }

public function index(Request $request)
{
    $user = $request->user();

    // 1. Query siswa terfilter sesuai role & jurusan user
    $query = Student::accessibleBy($user)->with('classroom');

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
              ->orWhere('nis', 'ilike', "%{$search}%");
        });
    }

    if ($request->filled('classroom_id')) {
        $query->where('classroom_id', $request->classroom_id);
    }

    $students = $query->orderBy('name')->paginate(10);

    // 2. FILTER DROPDOWN KELAS SESUAI WEWENANG USER
    $classroomsQuery = Classroom::query();

    if (!$user->hasRole(['admin', 'super-admin']) && !$user->can('students.view-all')) {
        $teacher = $user->teacher;

        if ($teacher && $teacher->major_id) {
            // Jika Kabeng/Kaproka/BK, hanya ambil kelas yang berjurusan TPTUP
            $classroomsQuery->where('major_id', $teacher->major_id);
        } elseif ($teacher && $user->hasRole('walas')) {
            // Jika Walas, hanya ambil kelas yang diwalikan
            $classroomsQuery->whereIn('id', $teacher->classrooms()->pluck('id'));
        } else {
            $classroomsQuery->whereRaw('1 = 0');
        }
    }

    $classrooms = $classroomsQuery->orderBy('name')->get();

    if ($request->ajax()) {
        return view('students.partials.student_table', compact('students'))->render();
    }

    return view('students.index', compact('students', 'classrooms'));
}


    /**
     * Tampilkan Detail Siswa
     */
    public function show($id)
    {
        $student = Student::with('classroom')->findOrFail($id);

        // Otorisasi melalui StudentPolicy (melempar 403 Forbidden jika di luar akses)
        // $this->authorize('view', $student);

        // Gunakan Gate::authorize sebagai pengganti $this->authorize
        Gate::authorize('view', $student);

        return view('students.show', compact('student'));
    }

    /**
     * Form Edit Siswa
     */
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $classrooms = Classroom::orderBy('name')->get();

        return view('students.edit', compact('student', 'classrooms'));
    }

    /**
     * Update Data Siswa
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'nis' => 'required|unique:students,nis,' . $id, // Unique kecuali punya sendiri
            'name' => 'required|string|max:255',
            'gender'       => 'required|in:L,P', // Validasi Jenis Kelamin L / P
            'classroom_id' => 'required|exists:classrooms,id',
            'phone' => 'nullable|numeric',
        ]);

        $student->update([
            'nis' => $request->nis,
            'name' => $request->name,
            'gender'       => $request->gender,
            'classroom_id' => $request->classroom_id,
            'phone' => $request->phone,
            'address'      => $request->address,
        ]);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    /**
     * Hapus Siswa
     */
    // public function destroy($id)
    // {
    //     $student = Student::findOrFail($id);
    //     $student->delete();

    //     return redirect()->route('students.index')->with('success', 'Data siswa berhasil dihapus!');
    // }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $student = Student::findOrFail($id);

            // 1. Cek dan hapus akun User terkait jika ada (berdasarkan user_id)
            if ($student->user_id) {
                User::where('id', $student->user_id)->delete();
            }

            // 2. Hapus data Siswa
            $student->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Data siswa beserta akun user berhasil dihapus!'
                ]);
            }

            return redirect()->back()->with('success', 'Data siswa beserta akun user berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menghapus data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function removeClassroom($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['classroom_id' => null]); // Set kelas jadi null
        return back()->with('success', "Siswa {$student->name} berhasil dikeluarkan dari kelas.");
    }

    public function removeClass($id)
    {
        try {
            $student = Student::findOrFail($id);
            $namaKelas = $student->classroom->name ?? '-';

            // Set classroom_id menjadi null (tanpa menghapus siswa dari database)
            $student->update(['classroom_id' => null]);

            return redirect()->back()->with('success', "Siswa {$student->name} berhasil dikeluarkan dari kelas {$namaKelas}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new StudentExport, 'Data-Siswa.xlsx');
    }

    /**
     * Cetak ID Card Siswa (Ukuran 9cm x 4cm).
     */
    public function printIdCard($id)
    {
        // Ambil data siswa beserta kelasnya
        $student = Student::with('classroom')->findOrFail($id);

        return view('students.print_id_card', compact('student'));
    }


    public function printIdCardPdf($id)
{
    $student = \App\Models\Student::with('classroom')->findOrFail($id);

    // Set ukuran kertas kustom sesuai ID Card (9cm x 5.5cm atau 9cm x 4cm)
    // array(0, 0, width_in_points, height_in_points) -> 1cm ≈ 28.35 points
    $customPaper = array(0, 0, 255.1, 113.4); // ~9cm x 4cm

    $pdf = Pdf::loadView('students.pdf_id_card', compact('student'))
                ->setPaper($customPaper, 'landscape');

    return $pdf->stream('ID-CARD-' . $student->nis . '.pdf');
}

/**
     * Memasukkan siswa ke dalam kelas
     */
    public function assignClass(Request $request, $classroomId)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($request->student_id);
        $student->update([
            'classroom_id' => $classroomId,
        ]);

        return back()->with('success', 'Siswa ' . $student->name . ' berhasil ditambahkan ke kelas.');
    }

    /**
     * Mengeluarkan siswa dari kelas (set classroom_id menjadi null)
     */
    public function removeFromClass($studentId)
    {
        $student = Student::findOrFail($studentId);
        $studentName = $student->name;
        
        $student->update([
            'classroom_id' => null,
        ]);

        return back()->with('success', 'Siswa ' . $studentName . ' berhasil dikeluarkan dari kelas.');
    }



}
