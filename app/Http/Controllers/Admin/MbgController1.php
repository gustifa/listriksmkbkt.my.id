<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\MbgAttendance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MbgController extends Controller
{
    // Halaman Utama / History
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        $attendances = MbgAttendance::with(['student.classroom']) // Asumsi ada relasi classroom di student
            ->whereDate('date', $date)
            ->orderBy('check_in_time', 'desc')
            ->get();

        $stats = [
            'total' => $attendances->count(),
            'barcode' => $attendances->where('method', 'barcode')->count(),
            'face' => $attendances->where('method', 'face')->count(),
            'manual' => $attendances->where('method', 'manual')->count(),
        ];

        return view('admin.mbg.index', compact('attendances', 'date', 'stats'));
    }

    // Halaman Scanner (Kamera)
    public function scan()
    {
        return view('admin.mbg.scan');
    }

    // Proses Simpan Absen MBG
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nis' => 'required_without:student_id|string',
    //         'student_id' => 'nullable|exists:students,id',
    //         'method' => 'required|in:barcode,face,manual',
    //         'image' => 'nullable|string', // Base64 image
    //     ]);

    //     $dateToday = Carbon::now()->format('Y-m-d');

    //     // 1. Cari Siswa (Bisa via NIS dari Barcode atau ID langsung)
    //     if ($request->filled('nis')) {
    //         $student = Student::where('nis', $request->nis)->first();
    //     } else {
    //         $student = Student::find($request->student_id);
    //     }

    //     if (!$student) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Siswa tidak ditemukan! Periksa NIS.'
    //         ], 404);
    //     }

    //     // 2. Cek Double Dipping (Apakah sudah ambil hari ini?)
    //     $alreadyTaken = MbgAttendance::where('student_id', $student->id)
    //         ->where('date', $dateToday)
    //         ->first();

    //     if ($alreadyTaken) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => "ALARM! Siswa a.n {$student->name} SUDAH MENGAMBIL jatah hari ini pada jam " . Carbon::parse($alreadyTaken->check_in_time)->format('H:i'),
    //             'data' => $student
    //         ], 400);
    //     }

    //     // 3. Simpan Bukti Foto (Jika ada)
    //     $imagePath = null;
    //     if ($request->filled('image')) {
    //         $image = $request->image;  // your base64 encoded
    //         $image = str_replace('data:image/jpeg;base64,', '', $image);
    //         $image = str_replace(' ', '+', $image);
    //         $imageName = 'mbg/' . $dateToday . '/' . $student->nis . '_' . time() . '.jpg';

    //         Storage::disk('public')->put($imageName, base64_decode($image));
    //         $imagePath = $imageName;
    //     }

    //     // 4. Simpan Data
    //     MbgAttendance::create([
    //         'id' => (string) Str::uuid(),
    //         'student_id' => $student->id,
    //         'date' => $dateToday,
    //         'check_in_time' => now(),
    //         'status' => 'taken',
    //         'method' => $request->method,
    //         'image_evidence' => $imagePath,
    //         'recorded_by' => Auth::user()->name ?? 'System'
    //     ]);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => "Berhasil! {$student->name} silahkan makan.",
    //         'student' => $student,
    //         'time' => now()->format('H:i:s')
    //     ]);
    // }

    // Proses Simpan Absen MBG (Updated: Error Handling)
    public function store(Request $request)
    {
        // Gunakan Try-Catch agar error 500 tetap mereturn JSON
        try {
            $request->validate([
                'nis' => 'required_without:student_id', // Hapus validasi string ketat
                'student_id' => 'nullable|exists:students,id',
                'method' => 'required|in:barcode,face,manual',
                'image' => 'nullable',
            ]);

            $dateToday = Carbon::now()->format('Y-m-d');

            // 1. Cari Siswa
            $student = null;
            if ($request->filled('nis')) {
                // Trim NIS untuk menghapus spasi tidak sengaja
                $nis = trim($request->nis);
                $student = Student::where('nis', $nis)->first();
            } elseif ($request->filled('student_id')) {
                $student = Student::find($request->student_id);
            }

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Siswa tidak ditemukan! Periksa NIS (' . ($request->nis ?? '-') . ').'
                ], 404);
            }

            // 2. Cek Double Dipping (Sudah ambil?)
            $alreadyTaken = MbgAttendance::where('student_id', $student->id)
                ->where('date', $dateToday)
                ->first();

            if ($alreadyTaken) {
                return response()->json([
                    'status' => 'error',
                    'message' => "ALARM! Siswa a.n {$student->name} SUDAH MENGAMBIL jatah hari ini pada jam " . Carbon::parse($alreadyTaken->check_in_time)->format('H:i'),
                    'data' => $student
                ], 400); // 400 Bad Request
            }

            // 3. Simpan Bukti Foto
            $imagePath = null;
            if ($request->filled('image')) {
                try {
                    $image = $request->image;
                    if (strpos($image, 'data:image') !== false) {
                        $image = str_replace('data:image/jpeg;base64,', '', $image);
                        $image = str_replace(' ', '+', $image);
                        $imageName = 'mbg/' . $dateToday . '/' . $student->nis . '_' . time() . '.jpg';

                        // Pastikan folder ada
                        if(!Storage::disk('public')->exists('mbg/' . $dateToday)) {
                            Storage::disk('public')->makeDirectory('mbg/' . $dateToday);
                        }

                        Storage::disk('public')->put($imageName, base64_decode($image));
                        $imagePath = $imageName;
                    }
                } catch (\Exception $imgErr) {
                    Log::error("MBG Image Error: " . $imgErr->getMessage());
                    // Lanjut saja meski gambar gagal, jangan stop proses
                }
            }

            // 4. Simpan Data ke Database
            MbgAttendance::create([
                'id' => (string) Str::uuid(),
                'student_id' => $student->id,
                'date' => $dateToday,
                'check_in_time' => now(),
                'status' => 'taken',
                'method' => $request->method,
                'image_evidence' => $imagePath,
                'recorded_by' => Auth::user()->name ?? 'System'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil! {$student->name} silahkan makan.",
                'student' => $student,
                'time' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            // Tangkap Error Sistem (Misal: Tabel tidak ada)
            Log::error("MBG System Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi Kesalahan Server: ' . $e->getMessage()
            ], 500);
        }
    }
}
