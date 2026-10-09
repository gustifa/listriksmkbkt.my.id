<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFaceDescriptor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Classroom; // Import model Classroom

class FaceRegistrationController extends Controller
{
    // READ: Daftar siswa & status sampel
    // READ: Daftar siswa dengan Filter Kelas & Pencarian Nama
    public function index(Request $request)
    {
        $query = Student::with(['classroom', 'faceDescriptors'])
                        ->withCount('faceDescriptors');

        // Filter berdasarkan Kelas
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Filter berdasarkan Nama atau NIS (Support PostgreSQL iLIKE)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'iLIKE', "%{$search}%")
                  ->orWhere('nis', 'iLIKE', "%{$search}%");
            });
        }

        $students = $query->paginate(15)->withQueryString();

        // Jika Request dari AJAX, kirimkan partial view tabel saja
        if ($request->ajax()) {
            return view('face_registration._table', compact('students'))->render();
        }

        $classrooms = Classroom::orderBy('name', 'asc')->get();

        return view('face_registration.index', compact('students', 'classrooms'));
    }

    // CREATE: Form perekaman kamera
    public function create(string $student_id)
    {
        $student = Student::findOrFail($student_id);
        return view('face_registration.create', compact('student'));
    }

    // STORE: Simpan multi-descriptor UUID
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'descriptors' => 'required|array|min:1',
            'labels' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->descriptors as $index => $descriptorJson) {
                $descriptorArray = json_decode($descriptorJson, true);
                $label = $request->labels[$index] ?? 'Sampel ' . ($index + 1);

                StudentFaceDescriptor::create([
                    'student_id' => $request->student_id,
                    'descriptor' => $descriptorArray,
                    'label'      => $label,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menyimpan ' . count($request->descriptors) . ' sampel wajah!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    // EDIT: Kelola sampel tersimpan
    public function edit(string $student_id)
    {
        $student = Student::with('faceDescriptors')->findOrFail($student_id);
        return view('face_registration.edit', compact('student'));
    }

    // DESTROY: Hapus 1 sampel descriptor
    public function destroy(string $descriptor_id): JsonResponse
    {
        $descriptor = StudentFaceDescriptor::findOrFail($descriptor_id);
        $descriptor->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sampel wajah berhasil dihapus.'
        ]);
    }

    // API: Kirim data multidimensi array ke Scanner Wajah
    public function getAllDescriptorsFace(): JsonResponse
    {
        $students = Student::with('faceDescriptors')->has('faceDescriptors')->get();

        $formattedData = $students->map(function ($student) {
            return [
                'label' => $student->nis . ' - ' . $student->name,
                'descriptor' => $student->faceDescriptors->pluck('descriptor')->toArray(),
            ];
        });

        return response()->json($formattedData);
    }
}
