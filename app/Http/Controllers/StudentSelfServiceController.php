<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentFaceDescriptor;
use Illuminate\Support\Facades\Auth;

class StudentSelfServiceController extends Controller
{
    // READ: Menampilkan daftar sampel wajah milik siswa yang sedang login
    public function index()
    {
        $student = Auth::user()->student; // Sesuaikan dengan relasi User -> Student
        $descriptors = StudentFaceDescriptor::where('student_id', $student->id)->latest()->get();

        return view('students.face_index', compact('student', 'descriptors'));
    }

    

   

    // DELETE: Menghapus sampel wajah milik siswa sendiri
    public function destroy($id)
    {
        $student = Auth::user()->student;

        // Pastikan hanya bisa menghapus sampel miliknya sendiri
        $descriptor = StudentFaceDescriptor::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $descriptor->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sampel wajah berhasil dihapus.'
        ]);
    }


    // Halaman Pendaftaran Wajah Sendiri
    public function registerFaceView()
    {
        $student = Auth::user()->student; // Sesuaikan relasi User -> Student
        return view('students.register_face', compact('student'));
    }

    public function storeFace(Request $request)
    {
        // Validasi minimal 3 sampel descriptor
        $request->validate([
            'descriptors' => 'required|array|min:3',
            'labels'      => 'nullable|array',
        ], [
            'descriptors.min' => 'Anda wajib mengambil minimal 3 sampel foto wajah!',
            'descriptors.required' => 'Belum ada sampel wajah yang diambil.'
        ]);

        $student = Auth::user()->student;

        foreach ($request->descriptors as $index => $descriptorJson) {
            StudentFaceDescriptor::create([
                'student_id' => $student->id,
                'descriptor' => json_decode($descriptorJson, true),
                'label'      => $request->labels[$index] ?? 'Mandiri ' . ($index + 1),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menyimpan 3 sampel wajah!'
        ]);
    }

    // Halaman Scan Mandiri dengan GPS
    public function scanView()
    {
        $student = Auth::user()->student->load('classroom');
        return view('student.scan', compact('student'));
    }

    // Proses Presensi & Hitung Haversine Distance (Radius GPS)
    public function processScan(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'image' => 'required',
        ]);

        $student = Auth::user()->student->load('classroom');
        
        // Titik Koordinat Sekolah (Dari DB / Default)
        $schoolLat = $student->classroom->latitude ?? -0.923412; // Contoh Lat Sekolah
        $schoolLng = $student->classroom->longitude ?? 100.362123; // Contoh Lng Sekolah
        $maxRadius = $student->classroom->radius_meters ?? 100; // Radius Maksimal dalam meter

        // Hitung Jarak GPS Menggunakan Rumus Haversine
        $distance = $this->calculateDistance($request->latitude, $request->longitude, $schoolLat, $schoolLng);

        if ($distance > $maxRadius) {
            return response()->json([
                'status' => 'error',
                'message' => 'Posisi Anda di luar area sekolah! Jarak Anda: ' . round($distance) . ' meter dari lokasi.'
            ], 422);
        }

        // Jalankan Logika Simpan Presensi Masuk
        // ... simpan ke tabel presensi ...

        return response()->json([
            'status' => 'success',
            'message' => 'Presensi berhasil disimpan! Jarak: ' . round($distance) . 'm dari lokasi.'
        ]);
    }

    // Formula Haversine (Mendapatkan jarak dalam satuan meter)
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                 cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}