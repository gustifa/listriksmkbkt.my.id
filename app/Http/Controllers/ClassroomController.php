<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Major;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth; // <-- TAMBAHKAN LINE INI

class ClassroomController extends Controller
{
    /**
     * Menampilkan daftar kelas.
     */
    // public function index(Request $request)
    // {
    //     $query = Classroom::with(['major', 'students', 'homeroomTeacher', 'counselingTeacher', 'classLeader']);

    //     if ($request->has('search')) {
    //         $query->where('name', 'like', '%' . $request->search . '%');
    //     }

    //     $classrooms = $query->latest()->paginate(100);
    //     // Ambil daftar siswa yang BELUM MEMILIKI KELAS
    //     $unassignedStudents = Student::whereNull('classroom_id')->orderBy('name', 'asc')->get();

    //     return view('classrooms.index', compact('classrooms', 'unassignedStudents'));
    // }

    /**
     * Menampilkan daftar kelas (Pencarian Persis & Support AJAX)
     */
    // public function index(Request $request)
    // {
    //     $query = Classroom::with(['major', 'homeroomTeacher', 'counselingTeacher', 'classLeader'])
    //         ->withCount('students');

    //     // Filter Pencarian Persis (Exact Match)
    //     if ($request->filled('search')) {
    //         $query->where('name', '=', trim($request->search));
    //     }

    //     $classrooms = $query->latest()->paginate(12)->withQueryString();

    //     // Ambil data pendukung
    //     $allTeachers = Teacher::orderBy('name')->select('id', 'name')->get();

    //     $takenHomeroomIds = Classroom::whereNotNull('homeroom_teacher_id')
    //         ->pluck('homeroom_teacher_id')
    //         ->toArray();

    //     $unassignedStudents = Student::whereNull('classroom_id')
    //         ->select('id', 'name', 'nis')
    //         ->orderBy('name', 'asc')
    //         ->take(100)
    //         ->get();

    //     // Jika request dari AJAX, kembalikan Partial View HTML
    //     if ($request->ajax()) {
    //         return response()->json([
    //             'html' => view('classrooms.partials.classroom_data', compact('classrooms', 'allTeachers', 'takenHomeroomIds', 'unassignedStudents'))->render()
    //         ]);
    //     }

    //     return view('classrooms.index', compact(
    //         'classrooms',
    //         'allTeachers',
    //         'takenHomeroomIds',
    //         'unassignedStudents'
    //     ));
    // }

    /**
     * Menampilkan daftar kelas (Live Search AJAX & Fitur Drag and Drop)
     */
//     public function index(Request $request)
// {
//     $query = Classroom::with(['major', 'homeroomTeacher', 'counselingTeacher', 'classLeader'])
//         ->withCount('students');

//     if ($request->filled('search')) {
//         $query->where('name', 'like', '%' . trim($request->search) . '%');
//     }

//     $classrooms = $query->latest()->paginate(12)->withQueryString();

//     $allTeachers = Teacher::orderBy('name')->select('id', 'name')->get();

//     $takenHomeroomIds = Classroom::whereNotNull('homeroom_teacher_id')
//         ->pluck('homeroom_teacher_id')
//         ->toArray();

//     // AMBIL SISWA TANPA KELAS DENGAN PATH DEDIKASI
//     // Ambil kelas pertama sebagai default path untuk render awal modal
//     $firstClassroomId = $classrooms->first()->id ?? 0;

//     $unassignedStudents = Student::whereNull('classroom_id')
//         ->select('id', 'name', 'nis', 'nisn')
//         ->orderBy('name', 'asc')
//         ->paginate(15)
//         ->withPath(route('classrooms.unassigned_students', $firstClassroomId));

//     if ($request->ajax()) {
//         return response()->json([
//             'html' => view('classrooms.partials.classroom_data', compact('classrooms', 'allTeachers', 'takenHomeroomIds', 'unassignedStudents'))->render()
//         ]);
//     }

//     return view('classrooms.index', compact(
//         'classrooms',
//         'allTeachers',
//         'takenHomeroomIds',
//         'unassignedStudents'
//     ));
// }

// public function index(Request $request)
// {
//     $user = Auth::user();
//     $userMajorId = $user->teacher->major_id ?? null;

//     $query = Classroom::with(['major', 'homeroomTeacher', 'counselingTeacher', 'classLeader'])
//         ->withCount('students')
//         // Filter jurusan admin/kaprog jika ada
//         ->when($userMajorId, function ($q) use ($userMajorId) {
//             return $q->where('major_id', $userMajorId);
//         });

//     if ($request->filled('search')) {
//         $query->where('name', 'like', '%' . trim($request->search) . '%');
//     }

//     // URUTKAN: Yang AKTIF (true / 1) di paling atas, baru diurutkan berdasarkan NAMA KELAS
//     $classrooms = $query->orderBy('is_active', 'desc')
//                         ->orderBy('name', 'asc')
//                         ->paginate(12)
//                         ->withQueryString();

//     $allTeachers = Teacher::orderBy('name')->select('id', 'name')->get();

//     $takenHomeroomIds = Classroom::whereNotNull('homeroom_teacher_id')
//         ->pluck('homeroom_teacher_id')
//         ->toArray();

//     $firstClassroomId = $classrooms->first()->id ?? 0;

//     $unassignedStudents = Student::whereNull('classroom_id')
//         ->select('id', 'name', 'nis', 'nisn')
//         ->orderBy('name', 'asc')
//         ->paginate(15)
//         ->withPath(route('classrooms.unassigned_students', $firstClassroomId));

//     if ($request->ajax()) {
//         return response()->json([
//             'html' => view('classrooms.partials.classroom_data', compact('classrooms', 'allTeachers', 'takenHomeroomIds', 'unassignedStudents'))->render()
//         ]);
//     }

//     return view('classrooms.index', compact(
//         'classrooms',
//         'allTeachers',
//         'takenHomeroomIds',
//         'unassignedStudents'
//     ));
// }

// public function index(Request $request)
// {
//     $user = Auth::user();
//     $userMajorId = $user->teacher->major_id ?? null;

//     $query = Classroom::with(['major', 'homeroomTeacher', 'counselingTeacher', 'classLeader'])
//         ->withCount('students')
//         ->when($userMajorId, function ($q) use ($userMajorId) {
//             return $q->where('major_id', $userMajorId);
//         });

//     if ($request->filled('search')) {
//         $query->where('name', 'like', '%' . trim($request->search) . '%');
//     }

//     $classrooms = $query->orderBy('is_active', 'desc')
//                         ->orderBy('name', 'asc')
//                         ->paginate(12)
//                         ->withQueryString();

//     $allTeachers = Teacher::orderBy('name')->select('id', 'name')->get();

//     $takenHomeroomIds = Classroom::whereNotNull('homeroom_teacher_id')
//         ->pluck('homeroom_teacher_id')
//         ->toArray();

//     // AMBIL SISWA TANPA KELAS (classroom_id IS NULL)
//     // Fleksibel: Tampilkan jika major_id sesuai jurusan admin ATAU jika major_id bernilai NULL
//     $unassignedStudents = Student::whereNull('classroom_id')
//         ->when($userMajorId, function ($q) use ($userMajorId) {
//             return $q->where(function ($sub) use ($userMajorId) {
//                 $sub->where('major_id', $userMajorId)
//                     ->orWhereNull('major_id');
//             });
//         })
//         ->select('id', 'name', 'nis', 'nisn')
//         ->orderBy('name', 'asc')
//         ->take(15)
//         ->get();

//     if ($request->ajax()) {
//         return response()->json([
//             'html' => view('classrooms.partials.classroom_data', compact('classrooms', 'allTeachers', 'takenHomeroomIds', 'unassignedStudents'))->render()
//         ]);
//     }

//     return view('classrooms.index', compact(
//         'classrooms',
//         'allTeachers',
//         'takenHomeroomIds',
//         'unassignedStudents'
//     ));
// }

public function index(Request $request)
{
    $user = Auth::user();

    // 1. Gunakan Scope accessibleBy agar rombel terfilter otomatis sesuai Role & Jurusan/Wali Kelas
    $query = Classroom::accessibleBy($user)
        ->with(['major', 'homeroomTeacher', 'counselingTeacher', 'classLeader'])
        ->withCount('students');

    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . trim($request->search) . '%');
    }

    $classrooms = $query->orderBy('is_active', 'desc')
                        ->orderBy('name', 'asc')
                        ->paginate(12)
                        ->withQueryString();

    $allTeachers = Teacher::orderBy('name')->select('id', 'name')->get();

    $takenHomeroomIds = Classroom::whereNotNull('homeroom_teacher_id')
        ->pluck('homeroom_teacher_id')
        ->toArray();

    // 2. Filter Siswa Tanpa Kelas (Sesuai akses jurusan guru)
    $teacher = $user->teacher;
    $userMajorId = $teacher->major_id ?? null;

    $unassignedStudents = Student::whereNull('classroom_id')
        ->when(!$user->hasRole(['admin', 'super-admin']) && $userMajorId, function ($q) use ($userMajorId) {
            return $q->where(function ($sub) use ($userMajorId) {
                $sub->where('major_id', $userMajorId)
                    ->orWhereNull('major_id');
            });
        })
        ->select('id', 'name', 'nis', 'nisn')
        ->orderBy('name', 'asc')
        ->take(15)
        ->get();

    if ($request->ajax()) {
        return response()->json([
            'html' => view('classrooms.partials.classroom_data', compact('classrooms', 'allTeachers', 'takenHomeroomIds', 'unassignedStudents'))->render()
        ]);
    }

    return view('classrooms.index', compact(
        'classrooms',
        'allTeachers',
        'takenHomeroomIds',
        'unassignedStudents'
    ));
}

public function searchUnassignedStudents(Request $request, $classroomId)
{
    $user = Auth::user();
    $userMajorId = $user->teacher->major_id ?? null;

    $query = Student::whereNull('classroom_id')
        ->when($userMajorId, function ($q) use ($userMajorId) {
            return $q->where(function ($sub) use ($userMajorId) {
                $sub->where('major_id', $userMajorId)
                    ->orWhereNull('major_id');
            });
        });

    if ($request->filled('q')) {
        $search = trim($request->q);
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('nis', 'like', '%' . $search . '%')
              ->orWhere('nisn', 'like', '%' . $search . '%');
        });
    }

    $students = $query->orderBy('name', 'asc')
                      ->select('id', 'name', 'nis', 'nisn')
                      ->paginate(10)
                      ->withPath(route('classrooms.unassigned_students', $classroomId));

    $itemsHtml = view('classrooms.partials.unassigned_list_items', compact('students', 'classroomId'))->render();

    $paginationHtml = $students->hasPages() 
        ? $students->links('pagination::bootstrap-4')->render() 
        : '';

    return response()->json([
        'html_items'      => $itemsHtml,
        'html_pagination' => $paginationHtml,
        'total'           => $students->total()
    ]);
}


//     public function searchUnassignedStudents(Request $request, $classroomId)
// {
//     $query = Student::whereNull('classroom_id');

//     if ($request->filled('q')) {
//         $search = trim($request->q);
//         $query->where(function($q) use ($search) {
//             $q->where('name', 'like', '%' . $search . '%')
//               ->orWhere('nis', 'like', '%' . $search . '%')
//               ->orWhere('nisn', 'like', '%' . $search . '%');
//         });
//     }

//     // Ambil 15 siswa per halaman
//     $students = $query->orderBy('name', 'asc')
//                       ->select('id', 'name', 'nis', 'nisn')
//                       ->paginate(15);

//     // Render HTML partial item siswa + link paginasinya
//     $html = view('classrooms.partials.unassigned_list_items', compact('students', 'classroomId'))->render();

//     return response()->json([
//         'html' => $html,
//         'total' => $students->total()
//     ]);
// }

// public function searchUnassignedStudents(Request $request, $classroomId)
// {
//     $query = Student::whereNull('classroom_id');

//     if ($request->filled('q')) {
//         $search = trim($request->q);
//         $query->where(function($q) use ($search) {
//             $q->where('name', 'like', '%' . $search . '%')
//               ->orWhere('nis', 'like', '%' . $search . '%')
//               ->orWhere('nisn', 'like', '%' . $search . '%');
//         });
//     }

//     // Set path khusus agar link paginasi mengarah ke route ini
//     $students = $query->orderBy('name', 'asc')
//                       ->select('id', 'name', 'nis', 'nisn')
//                       ->paginate(10)
//                       ->withPath(route('classrooms.unassigned_students', $classroomId));

//     $html = view('classrooms.partials.unassigned_list_items', compact('students', 'classroomId'))->render();

//     return response()->json([
//         'html' => $html,
//         'total' => $students->total()
//     ]);
// }

// public function searchUnassignedStudents(Request $request, $classroomId)
// {
//     $query = Student::whereNull('classroom_id');

//     if ($request->filled('q')) {
//         $search = trim($request->q);
//         $query->where(function($q) use ($search) {
//             $q->where('name', 'like', '%' . $search . '%')
//               ->orWhere('nis', 'like', '%' . $search . '%')
//               ->orWhere('nisn', 'like', '%' . $search . '%');
//         });
//     }

//     $students = $query->orderBy('name', 'asc')
//                       ->select('id', 'name', 'nis', 'nisn')
//                       ->paginate(10)
//                       ->withPath(route('classrooms.unassigned_students', $classroomId));

//     $itemsHtml = view('classrooms.partials.unassigned_list_items', compact('students', 'classroomId'))->render();
    
//     // Render paginasi secara terpisah
//     $paginationHtml = $students->hasPages() 
//         ? $students->links('pagination::bootstrap-4')->render() 
//         : '';

//     return response()->json([
//         'html_items' => $itemsHtml,
//         'html_pagination' => $paginationHtml,
//         'total' => $students->total()
//     ]);
// }

// public function searchUnassignedStudents(Request $request, $classroomId)
// {
//     $user = Auth::user();
//     $userMajorId = $user->teacher->major_id ?? null;

//     // Ambil siswa yang BELUM memiliki kelas (classroom_id NULL)
//     $query = Student::whereNull('classroom_id')
//         // Jika admin memiliki jurusan, filter siswa berdasarkan jurusannya
//         ->when($userMajorId, function ($q) use ($userMajorId) {
//             return $q->where('major_id', $userMajorId);
//         });

//     if ($request->filled('q')) {
//         $search = trim($request->q);
//         $query->where(function ($q) use ($search) {
//             $q->where('name', 'like', '%' . $search . '%')
//               ->orWhere('nis', 'like', '%' . $search . '%')
//               ->orWhere('nisn', 'like', '%' . $search . '%');
//         });
//     }

//     $students = $query->orderBy('name', 'asc')
//                       ->select('id', 'name', 'nis', 'nisn')
//                       ->paginate(10)
//                       ->withPath(route('classrooms.unassigned_students', $classroomId));

//     $itemsHtml = view('classrooms.partials.unassigned_list_items', compact('students', 'classroomId'))->render();

//     $paginationHtml = $students->hasPages() 
//         ? $students->links('pagination::bootstrap-4')->render() 
//         : '';

//     return response()->json([
//         'html_items'      => $itemsHtml,
//         'html_pagination' => $paginationHtml,
//         'total'           => $students->total()
//     ]);
// }

    /**
     * Menampilkan form tambah kelas.
     */
    public function create()
    {
        $majors = Major::all();
        // Hanya tampilkan guru yang BELUM jadi wali kelas
        $assignedTeacherIds = Classroom::whereNotNull('homeroom_teacher_id')->pluck('homeroom_teacher_id');
        $teachers = Teacher::whereNotIn('id', $assignedTeacherIds)->orderBy('name')->get();

        return view('classrooms.create', compact('majors', 'teachers'));
    }

    /**
     * Menyimpan data kelas baru.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255|unique:classrooms,name',
    //         'major_id' => 'nullable|exists:majors,id',
    //         // Validasi: Wali Kelas harus unik di tabel classrooms
    //         'homeroom_teacher_id' => [
    //             'nullable',
    //             'exists:teachers,id',
    //             'unique:classrooms,homeroom_teacher_id' // Guru tidak boleh dipakai di kelas lain
    //         ],
    //         'counseling_teacher_id' => 'nullable|exists:teachers,id',
    //     ], [
    //         'homeroom_teacher_id.unique' => 'Guru tersebut sudah menjadi Wali Kelas di kelas lain.',
    //     ]);

    //     Classroom::create($request->all());

    //     return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil ditambahkan.');
    // }

    /**
     * Menyimpan data kelas baru & Otomatis Sync Role Walas
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255|unique:classrooms,name',
    //         'major_id' => 'nullable|exists:majors,id',
    //         'homeroom_teacher_id' => [
    //             'nullable',
    //             'exists:teachers,id',
    //             'unique:classrooms,homeroom_teacher_id'
    //         ],
    //         'counseling_teacher_id' => 'nullable|exists:teachers,id',
    //     ], [
    //         'homeroom_teacher_id.unique' => 'Guru tersebut sudah menjadi Wali Kelas di kelas lain.',
    //     ]);

    //     $classroom = Classroom::create($request->all());

    //     // Sinkronisasi Role Walas untuk Guru yang ditunjuk
    //     if ($classroom->homeroom_teacher_id) {
    //         $this->syncHomeroomRole($classroom->homeroom_teacher_id);
    //     }

    //     return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil ditambahkan.');
    // }

    /**
     * Menyimpan data kelas baru & Otomatis Sync Role Walas & BK
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:classrooms,name',
            'major_id' => 'nullable|exists:majors,id',
            'homeroom_teacher_id' => [
                'nullable',
                'exists:teachers,id',
                'unique:classrooms,homeroom_teacher_id'
            ],
            'counseling_teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'homeroom_teacher_id.unique' => 'Guru tersebut sudah menjadi Wali Kelas di kelas lain.',
        ]);

        $classroom = Classroom::create($request->all());

        // Sinkronisasi Role Walas & BK untuk Guru yang ditunjuk
        if ($classroom->homeroom_teacher_id) {
            $this->syncHomeroomRole($classroom->homeroom_teacher_id);
        }
        if ($classroom->counseling_teacher_id) {
            $this->syncCounselingRole($classroom->counseling_teacher_id);
        }

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit kelas.
     */
    public function edit(Classroom $classroom)
    {
        $majors = Major::all();
        // Ambil semua guru untuk edit, filtering logic bisa di handle di view atau disini lebih kompleks
        $teachers = Teacher::orderBy('name')->get();

        return view('classrooms.edit', compact('classroom', 'majors', 'teachers'));
    }

    /**
     * Memperbarui data kelas.
     */
    // public function update(Request $request, Classroom $classroom)
    // {
    //     $request->validate([
    //         'name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             Rule::unique('classrooms', 'name')->ignore($classroom->id)
    //         ],
    //         'major_id' => 'nullable|exists:majors,id',

    //         // Validasi Unik Wali Kelas (Kecuali untuk kelas ini sendiri)
    //         'homeroom_teacher_id' => [
    //             'nullable',
    //             'exists:teachers,id',
    //             Rule::unique('classrooms', 'homeroom_teacher_id')->ignore($classroom->id),
    //         ],

    //         'counseling_teacher_id' => 'nullable|exists:teachers,id',
    //         'class_leader_id' => 'nullable|exists:students,id',
    //     ], [
    //         'homeroom_teacher_id.unique' => 'Guru tersebut sudah menjadi Wali Kelas di kelas lain.',
    //     ]);

    //     $classroom->update($request->all());

    //     return redirect()->route('classrooms.index')->with('success', 'Data kelas berhasil diperbarui.');
    // }

    /**
     * Memperbarui data kelas & Otomatis Sync Role Walas
     */
    // public function update(Request $request, Classroom $classroom)
    // {
    //     $request->validate([
    //         'name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             Rule::unique('classrooms', 'name')->ignore($classroom->id)
    //         ],
    //         'major_id' => 'nullable|exists:majors,id',
    //         'homeroom_teacher_id' => [
    //             'nullable',
    //             'exists:teachers,id',
    //             Rule::unique('classrooms', 'homeroom_teacher_id')->ignore($classroom->id),
    //         ],
    //         'counseling_teacher_id' => 'nullable|exists:teachers,id',
    //         'class_leader_id' => 'nullable|exists:students,id',
    //     ], [
    //         'homeroom_teacher_id.unique' => 'Guru tersebut meurupakan Wali Kelas di kelas lain.',
    //     ]);

    //     $oldHomeroomTeacherId = $classroom->homeroom_teacher_id;

    //     $classroom->update($request->all());

    //     // Sync role untuk wali kelas lama (jika diganti/dikosongkan)
    //     if ($oldHomeroomTeacherId && $oldHomeroomTeacherId != $classroom->homeroom_teacher_id) {
    //         $this->syncHomeroomRole($oldHomeroomTeacherId);
    //     }

    //     // Sync role untuk wali kelas baru
    //     if ($classroom->homeroom_teacher_id) {
    //         $this->syncHomeroomRole($classroom->homeroom_teacher_id);
    //     }

    //     return redirect()->route('classrooms.index')->with('success', 'Data kelas berhasil diperbarui.');
    // }

    /**
     * Memperbarui data kelas & Otomatis Sync Role Walas & BK
     */
    public function update(Request $request, Classroom $classroom)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classrooms', 'name')->ignore($classroom->id)
            ],
            'major_id' => 'nullable|exists:majors,id',
            'homeroom_teacher_id' => [
                'nullable',
                'exists:teachers,id',
                Rule::unique('classrooms', 'homeroom_teacher_id')->ignore($classroom->id),
            ],
            'counseling_teacher_id' => 'nullable|exists:teachers,id',
            'class_leader_id' => 'nullable|exists:students,id',
        ], [
            'homeroom_teacher_id.unique' => 'Guru tersebut merupakan Wali Kelas di kelas lain.',
        ]);

        $oldHomeroomTeacherId = $classroom->homeroom_teacher_id;
        $oldCounselingTeacherId = $classroom->counseling_teacher_id;

        $classroom->update($request->all());

        // Sync role untuk Wali Kelas lama & baru
        if ($oldHomeroomTeacherId && $oldHomeroomTeacherId != $classroom->homeroom_teacher_id) {
            $this->syncHomeroomRole($oldHomeroomTeacherId);
        }
        if ($classroom->homeroom_teacher_id) {
            $this->syncHomeroomRole($classroom->homeroom_teacher_id);
        }

        // Sync role untuk Guru BK lama & baru
        if ($oldCounselingTeacherId && $oldCounselingTeacherId != $classroom->counseling_teacher_id) {
            $this->syncCounselingRole($oldCounselingTeacherId);
        }
        if ($classroom->counseling_teacher_id) {
            $this->syncCounselingRole($classroom->counseling_teacher_id);
        }

        return redirect()->route('classrooms.index')->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom)
    {
        if ($classroom->students()->count() > 0) {
            return back()->with('error', 'Gagal menghapus! Masih ada siswa di dalam kelas ini.');
        }

        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil dihapus.');
    }

    /**
     * Cetak Semua ID Card Siswa dalam Satu Kelas (PDF A4 Portrait)
     * Method ini yang sebelumnya hilang/undefined.
     */
    public function printAllIdsPdf($id)
    {
        // Ambil data kelas beserta siswanya, urutkan nama siswa abjad
        $classroom = Classroom::with(['students' => function($query) {
            $query->orderBy('name', 'asc');
        }])->findOrFail($id);

        // Load View PDF
        $pdf = Pdf::loadView('classrooms.pdf_all_ids', compact('classroom'))
                  ->setPaper('a4', 'portrait'); // Set kertas A4 Portrait

        return $pdf->stream('ID-CARDS-' . $classroom->name . '.pdf');
    }

    /**
 * Toggle Status Aktif/Nonaktif Rombel
 */
    public function toggleStatus(Request $request, Classroom $classroom)
    {
        $classroom->update([
            'is_active' => $request->boolean('is_active')
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status rombel ' . $classroom->name . ' berhasil diperbarui.'
        ]);
    }

    /**
     * Memasukkan siswa ke dalam kelas
     */
    public function assignClass(Request $request, $classroomId)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $student = Student::findOrFail($request->student_id);
        $student->update([
            'classroom_id' => $classroomId
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Siswa berhasil dimasukkan ke kelas.'
        ]);
    }

    /**
     * Mengeluarkan siswa dari kelas (menjadikan classroom_id NULL)
     */
    public function removeClass(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $student->update([
            'classroom_id' => null
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Siswa berhasil dikeluarkan dari kelas.'
        ]);
    }

    // Ambil data classroom untuk modal via AJAX
public function editAjax($id)
{
    $classroom = Classroom::findOrFail($id);
    $majors = Major::orderBy('name', 'asc')->get(['id', 'name', 'code']);

    return response()->json([
        'status'    => 'success',
        'classroom' => $classroom,
        'majors'    => $majors
    ]);
}

// Update major_id rombel via AJAX
public function updateMajor(Request $request, $id)
{
    $request->validate([
        'major_id' => 'required|exists:majors,id',
    ], [
        'major_id.required' => 'Silakan pilih jurusan terlebih dahulu.',
        'major_id.exists'   => 'Jurusan yang dipilih tidak valid.'
    ]);

    $classroom = Classroom::findOrFail($id);
    $classroom->update([
        'major_id' => $request->major_id
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => "Jurusan untuk rombel {$classroom->name} berhasil diperbarui!"
    ]);
}

    /**
     * Helper privat untuk sinkronisasi role 'wali_kelas' ke akun User guru
     */
    private function syncHomeroomRole($teacherId)
    {
        $teacher = Teacher::find($teacherId);
        if (!$teacher || !$teacher->user) {
            return;
        }

        // Sesuaikan dengan nama role di tabel database: 'wali_kelas'
        Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);

        $isHomeroom = Classroom::where('homeroom_teacher_id', $teacherId)->exists();

        if ($isHomeroom) {
            if (!$teacher->user->hasRole('wali_kelas')) {
                $teacher->user->assignRole('wali_kelas');
            }
        } else {
            if ($teacher->user->hasRole('wali_kelas')) {
                $teacher->user->removeRole('wali_kelas');
            }
        }
    }

    /**
     * Helper privat untuk sinkronisasi role 'guru_bk' ke akun User guru
     */
    private function syncCounselingRole($teacherId)
    {
        $teacher = Teacher::find($teacherId);
        if (!$teacher || !$teacher->user) {
            return;
        }

        // Sesuaikan dengan nama role di tabel database: 'guru_bk'
        Role::firstOrCreate(['name' => 'guru_bk', 'guard_name' => 'web']);

        $isCounseling = Classroom::where('counseling_teacher_id', $teacherId)->exists();

        if ($isCounseling) {
            if (!$teacher->user->hasRole('guru_bk')) {
                $teacher->user->assignRole('guru_bk');
            }
        } else {
            if ($teacher->user->hasRole('guru_bk')) {
                $teacher->user->removeRole('guru_bk');
            }
        }
    }
}
