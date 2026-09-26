<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class FaceController extends Controller
{
    /**
     * Halaman Utama: Pilih Kelas / Siswa untuk Registrasi Wajah
     */
    public function index(Request $request)
    {
        $classrooms = Classroom::orderBy('name')->get();
        $students = [];

        if ($request->has('classroom_id')) {
            $students = Student::where('classroom_id', $request->classroom_id)
                        ->orderBy('name')
                        ->get();
        }

        return view('face.index', compact('classrooms', 'students'));
    }

    /**
     * Halaman Ambil Foto Wajah (Webcam)
     */
    public function register($id)
    {
        $student = Student::with('classroom')->findOrFail($id);
        return view('face.register', compact('student'));
    }

    /**
     * Simpan Descriptor Wajah (AJAX)
     * UPDATE: Menggunakan try-catch dan forceFill untuk debugging yang lebih baik
     */
    public function store(Request $request, $id)
    {
        try {
            $request->validate([
                'descriptor' => 'required' // JSON String dari Face API
            ]);

            $student = Student::findOrFail($id);

            // Menggunakan forceFill agar tetap tersimpan meskipun user lupa
            // menambahkan 'face_descriptor' ke $fillable di Model Student
            $student->forceFill([
                'face_descriptor' => $request->descriptor
            ])->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Wajah berhasil didaftarkan!'
            ]);

        } catch (\Exception $e) {
            // Kembalikan pesan error asli agar bisa dilihat di Console Browser/SweetAlert
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Simpan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Ambil Data Wajah Satu Kelas (Untuk Scanner)
     * Dipanggil saat Guru membuka halaman Scan Wajah
     */
    public function getDescriptors($schedule_id)
    {
        try {
            // Cari jadwal
            $schedule = Schedule::findOrFail($schedule_id);

            // Ambil siswa di kelas tersebut yang SUDAH punya data wajah
            $students = Student::where('classroom_id', $schedule->classroom_id)
                        ->whereNotNull('face_descriptor')
                        ->select('nis', 'name', 'face_descriptor')
                        ->get();

            // Format data untuk Face API
            $labeledDescriptors = [];
            foreach($students as $student) {
                // Pastikan data JSON valid sebelum dikirim
                $descriptor = json_decode($student->face_descriptor);

                if (is_array($descriptor) && count($descriptor) > 0) {
                    $labeledDescriptors[] = [
                        'label' => $student->nis . ' - ' . $student->name,
                        'descriptor' => $descriptor
                    ];
                }
            }

            return response()->json($labeledDescriptors);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Scanner Khusus Wajah
     */
    public function scan($schedule_id)
    {

        $teacher = Teacher::where('user_id', Auth::id())->first();

        // Validasi jika akun tidak terhubung ke data guru
        if (!$teacher) {
            abort(403, 'Akun Anda tidak terdaftar sebagai Guru.');
        }

        $schedule = Schedule::with('classroom')
                    ->where('id', $schedule_id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();
                    

        return view('face.scan', compact('schedule'));
    }

    // =============================================================
    // [BARU] FITUR ABSENSI WAJAH HARIAN (GERBANG)
    // =============================================================

    /**
     * Halaman Scanner Wajah Harian (Tanpa Jadwal - Datang/Pulang)
     * Route: /daily-face-scan
     */
    public function dailyScan()
    {
        return view('face.daily_scan');
    }

    /**
     * API: Ambil SELURUH Data Wajah Siswa (Untuk Scanner Gerbang)
     * Route: /face/all-descriptors
     */
    public function getAllDescriptors()
    {
        // Ambil semua siswa yang sudah punya data wajah tanpa filter kelas
        return $this->fetchDescriptors();
    }

    /**
     * Helper Private untuk mengambil dan memformat data wajah
     */
    private function fetchDescriptors($conditions = [])
    {
        try {
            $query = Student::whereNotNull('face_descriptor');

            if (!empty($conditions)) {
                $query->where($conditions);
            }

            // Ambil kolom yang diperlukan saja agar ringan
            $students = $query->select('nis', 'name', 'face_descriptor')->get();

            $labeledDescriptors = [];
            foreach($students as $student) {
                // Decode JSON string dari database
                $descriptor = json_decode($student->face_descriptor);

                // Pastikan data valid sebelum dikirim
                if (is_array($descriptor) && count($descriptor) > 0) {
                    $labeledDescriptors[] = [
                        // Format Label: "NIS - NAMA" (Penting untuk parsing di JS)
                        'label' => $student->nis . ' - ' . $student->name,
                        'descriptor' => $descriptor
                    ];
                }
            }

            return response()->json($labeledDescriptors);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
