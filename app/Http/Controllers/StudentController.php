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
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Tampilkan Daftar Siswa (dengan Pencarian & Pagination)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Query siswa terfilter sesuai role & jurusan user
        $query = Student::accessibleBy($user)->with('classroom');

        // 2. Handling Filter Live Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nis', 'ilike', "%{$search}%");
            });
        }

        // 3. Handling Filter Kelas
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // 4. Handling Dynamic Sorting (ASC / DESC)
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = strtolower($request->input('sort_order', 'asc'));

        $allowedSorts = ['nis', 'name', 'classroom_id'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // 5. Handling Filter Per Page (Jumlah Data Dinamis)
        $perPage = (int) $request->input('per_page', 10);
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Eksekusi Paginasi Dinamis dengan $perPage
        $students = $query->orderBy($sortBy, $sortOrder)
                          ->paginate($perPage)
                          ->appends($request->all());

        // 6. Filter Dropdown Kelas Sesuai Wewenang User
        $classroomsQuery = Classroom::query();

        if (!$user->hasRole(['admin', 'super-admin']) && !$user->can('students.view-all')) {
            $teacher = $user->teacher;

            if ($teacher && $teacher->major_id) {
                $classroomsQuery->where('major_id', $teacher->major_id);
            } elseif ($teacher && $user->hasRole('walas')) {
                $classroomsQuery->whereIn('id', $teacher->classrooms()->pluck('id'));
            } else {
                $classroomsQuery->whereRaw('1 = 0');
            }
        }

        $classrooms = $classroomsQuery->orderBy('name')->get();

        // 7. Response AJAX / Normal View
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

        Gate::authorize('view', $student);

        return view('students.show', compact('student'));
    }

    /**
     * Form Edit Siswa (Mendukung Semua Field Fillable)
     */
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $classrooms = Classroom::orderBy('name')->get();

        return view('students.edit', compact('student', 'classrooms'));
    }

    /**
     * Update Seluruh Data Siswa
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        // Validasi Seluruh Bidang (Data Pribadi, Alamat, Kontak, & Ortu)
        $validatedData = $request->validate([
            // Identitas Utama & Akademik
            'nis'               => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($id)],
            'nisn'              => ['nullable', 'string', 'max:50', Rule::unique('students', 'nisn')->ignore($id)],
            'nik'               => ['nullable', 'string', 'max:16', Rule::unique('students', 'nik')->ignore($id)],
            'name'              => ['required', 'string', 'max:255'],
            'gender'            => ['required', 'in:L,P'],
            'pob'               => ['nullable', 'string', 'max:100'],
            'dob'               => ['nullable', 'date'],
            'religion'          => ['nullable', 'string', 'max:50'],
            'classroom_id'      => ['required', 'exists:classrooms,id'],
            'status'            => ['required', 'string'],
            'is_promoted'       => ['nullable', 'boolean'],
            'previous_school'   => ['nullable', 'string', 'max:255'],

            // Kontak & Alamat Siswa
            'phone'             => ['nullable', 'string', 'max:20'], // No. HP Orang Tua / Wali
            'student_phone'     => ['nullable', 'string', 'max:20'], // No. HP Pribadi Siswa
            'email'             => ['nullable', 'email', 'max:255'],
            'address'           => ['nullable', 'string'],
            'rt'                => ['nullable', 'string', 'max:10'],
            'rw'                => ['nullable', 'string', 'max:10'],
            'dusun'             => ['nullable', 'string', 'max:100'],
            'subdistrict'       => ['nullable', 'string', 'max:100'],
            'district'          => ['nullable', 'string', 'max:100'],
            'postal_code'       => ['nullable', 'string', 'max:10'],

            // Data Ayah
            'father_name'       => ['nullable', 'string', 'max:255'],
            'father_nik'        => ['nullable', 'string', 'max:16'],
            'father_education'  => ['nullable', 'string', 'max:50'],
            'father_job'        => ['nullable', 'string', 'max:100'],
            'father_income'     => ['nullable', 'string', 'max:100'],

            // Data Ibu
            'mother_name'       => ['nullable', 'string', 'max:255'],
            'mother_nik'        => ['nullable', 'string', 'max:16'],
            'mother_education'  => ['nullable', 'string', 'max:50'],
            'mother_job'        => ['nullable', 'string', 'max:100'],
            'mother_income'     => ['nullable', 'string', 'max:100'],

            // Data Wali
            'guardian_name'     => ['nullable', 'string', 'max:255'],
            'guardian_nik'      => ['nullable', 'string', 'max:16'],
            'guardian_education'=> ['nullable', 'string', 'max:50'],
            'guardian_job'      => ['nullable', 'string', 'max:100'],
            'guardian_income'   => ['nullable', 'string', 'max:100'],
        ]);

        DB::beginTransaction();
        try {
            // 1. Update Data Siswa Massal Sesuai Field yang Divalidasi
            $student->update($validatedData);

            // 2. Jika Siswa Terhubung ke Tabel User, Sinkronkan Nama & Email
            if ($student->user_id && $student->user) {
                $student->user->update([
                    'name'  => $request->name,
                    'email' => $request->email ?? $student->user->email,
                ]);
            }

            DB::commit();
            return redirect()->route('students.index')->with('success', "Data siswa {$student->name} berhasil diperbarui!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Siswa
     */
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
        $student->update(['classroom_id' => null]);
        return back()->with('success', "Siswa {$student->name} berhasil dikeluarkan dari kelas.");
    }

    public function removeClass($id)
    {
        try {
            $student = Student::findOrFail($id);
            $namaKelas = $student->classroom->name ?? '-';

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
        $student = Student::with('classroom')->findOrFail($id);
        return view('students.print_id_card', compact('student'));
    }

    public function printIdCardPdf($id)
    {
        $student = \App\Models\Student::with('classroom')->findOrFail($id);

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