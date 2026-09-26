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
    // Halaman Laporan Harian
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        
        $attendances = MbgAttendance::with(['student.classroom'])
            ->whereDate('date', $date)
            ->orderBy('updated_at', 'desc')
            ->get();

        $stats = [
            'total' => $attendances->count(),
            'taken' => $attendances->where('status', 'taken')->count(),     // Sedang Makan
            'returned' => $attendances->where('status', 'returned')->count(), // Sudah Kembali
        ];

        return view('admin.mbg.index', compact('attendances', 'date', 'stats'));
    }

    // Halaman Scanner
    public function scan()
    {
        return view('admin.mbg.scan');
    }

    // Proses Simpan (Ambil & Kembali)
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nis' => 'required_without:student_id',
                'student_id' => 'nullable|exists:students,id',
                'method' => 'required|in:barcode,face,manual',
                'image' => 'nullable', 
            ]);

            $dateToday = Carbon::now()->format('Y-m-d');
            
            // 1. Cari Siswa
            $student = null;
            if ($request->filled('nis')) {
                $nis = trim($request->nis);
                $student = Student::where('nis', $nis)->first();
            } elseif ($request->filled('student_id')) {
                $student = Student::find($request->student_id);
            }

            if (!$student) {
                return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!'], 404);
            }

            // 2. Cek Status Hari Ini
            $attendance = MbgAttendance::where('student_id', $student->id)
                ->where('date', $dateToday)
                ->first();

            $imagePath = null;
            $actionType = ''; // 'TAKE' or 'RETURN'

            // --- PROSES UPLOAD GAMBAR ---
            if ($request->filled('image')) {
                try {
                    $image = $request->image;
                    if (strpos($image, 'data:image') !== false) {
                        $image = str_replace('data:image/jpeg;base64,', '', $image);
                        $image = str_replace(' ', '+', $image);
                        
                        // Pisahkan folder foto ambil & kembali agar rapi
                        $folder = $attendance ? 'mbg/return/' : 'mbg/take/'; 
                        $imageName = $folder . $dateToday . '/' . $student->nis . '_' . time() . '.jpg';
                        
                        // Pastikan folder tersedia
                        $directory = dirname($imageName);
                        if(!Storage::disk('public')->exists($directory)) {
                            Storage::disk('public')->makeDirectory($directory);
                        }
                        
                        Storage::disk('public')->put($imageName, base64_decode($image));
                        $imagePath = $imageName;
                    }
                } catch (\Exception $imgErr) {
                    Log::error("MBG Image Error: " . $imgErr->getMessage());
                }
            }

            // --- LOGIKA UTAMA: AMBIL vs KEMBALI ---
            if (!$attendance) {
                // KASUS 1: BELUM ADA DATA -> PROSES PENGAMBILAN (TAKE)
                $actionType = 'TAKE';
                
                MbgAttendance::create([
                    'id' => (string) Str::uuid(),
                    'student_id' => $student->id,
                    'date' => $dateToday,
                    
                    // Kolom Spesifik Take
                    'taken_at' => now(),
                    'taken_method' => $request->method,
                    'taken_image' => $imagePath,
                    
                    // Kolom Umum (Legacy Support)
                    'check_in_time' => now(),
                    'image_evidence' => $imagePath, 
                    'status' => 'taken',
                    'method' => $request->method,
                    'recorded_by' => Auth::user()->name ?? 'System'
                ]);

                $message = "Selamat Makan! Pengambilan tercatat.";
                
            } elseif ($attendance->status == 'taken') {
                // KASUS 2: SUDAH AMBIL (Status Taken) -> PROSES PENGEMBALIAN (RETURN)
                $actionType = 'RETURN';

                $attendance->update([
                    'returned_at' => now(),
                    'returned_method' => $request->method,
                    'returned_image' => $imagePath,
                    'status' => 'returned'
                ]);

                $message = "Terima Kasih! Pengembalian alat makan tercatat.";

            } else {
                // KASUS 3: SUDAH KEMBALI (Status Returned) -> TOLAK
                return response()->json([
                    'status' => 'error',
                    'message' => "Siswa a.n {$student->name} SUDAH SELESAI (Mengambil & Mengembalikan) hari ini.",
                    'data' => $student
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'type' => $actionType, // 'TAKE' atau 'RETURN'
                'message' => $message,
                'student' => $student,
                'time' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error("MBG System Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}