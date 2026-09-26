<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Major;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule
use Barryvdh\DomPDF\Facade\Pdf;

class ClassroomController extends Controller
{
    /**
     * Menampilkan daftar kelas.
     */
    public function index(Request $request)
    {
        $query = Classroom::with(['major', 'students', 'homeroomTeacher', 'counselingTeacher', 'classLeader']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $classrooms = $query->latest()->paginate(10);

        return view('classrooms.index', compact('classrooms'));
    }

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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:classrooms,name',
            'major_id' => 'nullable|exists:majors,id',
            // Validasi: Wali Kelas harus unik di tabel classrooms
            'homeroom_teacher_id' => [
                'nullable',
                'exists:teachers,id',
                'unique:classrooms,homeroom_teacher_id' // Guru tidak boleh dipakai di kelas lain
            ],
            'counseling_teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'homeroom_teacher_id.unique' => 'Guru tersebut sudah menjadi Wali Kelas di kelas lain.',
        ]);

        Classroom::create($request->all());

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

            // Validasi Unik Wali Kelas (Kecuali untuk kelas ini sendiri)
            'homeroom_teacher_id' => [
                'nullable',
                'exists:teachers,id',
                Rule::unique('classrooms', 'homeroom_teacher_id')->ignore($classroom->id),
            ],

            'counseling_teacher_id' => 'nullable|exists:teachers,id',
            'class_leader_id' => 'nullable|exists:students,id',
        ], [
            'homeroom_teacher_id.unique' => 'Guru tersebut sudah menjadi Wali Kelas di kelas lain.',
        ]);

        $classroom->update($request->all());

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
}
