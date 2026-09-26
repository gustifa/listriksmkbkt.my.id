<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentPermit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StudentPermitController extends Controller
{
    // Halaman History / Dashboard Izin
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        $permits = StudentPermit::with(['student.classroom'])
            ->whereDate('date', $date)
            ->orderBy('updated_at', 'desc')
            ->get();

        $stats = [
            'total' => $permits->count(),
            'active' => $permits->where('status', 'active')->count(), // Masih diluar
            'returned' => $permits->where('status', 'returned')->count(), // Sudah kembali
        ];

        return view('admin.permit.index', compact('permits', 'date', 'stats'));
    }

    // Halaman Scanner
    public function scan()
    {
        return view('admin.permit.scan');
    }

    // Proses Simpan Data
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nis' => 'required_without:student_id',
                'reason' => 'required|string', // Alasan wajib dipilih di UI
                'method' => 'required|in:barcode,face,manual',
                'image' => 'nullable',
            ]);

            $dateToday = Carbon::now()->format('Y-m-d');

            // 1. Cari Siswa
            $student = null;
            if ($request->filled('nis')) {
                $nis = trim($request->nis);
                $student = Student::where('nis', $nis)->first();
            }

            if (!$student) {
                return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!'], 404);
            }

            // 2. Cek Apakah Sedang Izin (Status Active Hari Ini)
            $activePermit = StudentPermit::where('student_id', $student->id)
                ->where('date', $dateToday)
                ->where('status', 'active')
                ->latest()
                ->first();

            $imagePath = null;
            $type = '';
            $message = '';

            // --- SIMPAN FOTO BUKTI ---
            if ($request->filled('image')) {
                try {
                    $image = $request->image;
                    if (strpos($image, 'data:image') !== false) {
                        $image = str_replace('data:image/jpeg;base64,', '', $image);
                        $image = str_replace(' ', '+', $image);

                        $folder = $activePermit ? 'permits/in/' : 'permits/out/';
                        $imageName = $folder . $dateToday . '/' . $student->nis . '_' . time() . '.jpg';

                        if(!Storage::disk('public')->exists($folder . $dateToday)) {
                            Storage::disk('public')->makeDirectory($folder . $dateToday);
                        }
                        Storage::disk('public')->put($imageName, base64_decode($image));
                        $imagePath = $imageName;
                    }
                } catch (\Exception $e) { Log::error("Permit Image Error: ".$e->getMessage()); }
            }

            // --- LOGIKA UTAMA ---

            if ($activePermit) {
                // KASUS: KEMBALI KE KELAS (RETURN)
                // Jika alasannya "Pulang", tidak perlu scan kembali sebenarnya, tapi jika scan, kita tutup.

                $activePermit->update([
                    'time_in' => now(),
                    'status' => 'returned', // Tandai sudah kembali
                    // Foto bukti kembali (opsional, bisa menimpa atau kolom baru, di sini kita pakai logika sederhana)
                ]);

                $type = 'RETURN';
                $message = "Selamat Belajar Kembali! Terima kasih sudah melapor.";

            } else {
                // KASUS: IZIN KELUAR (OUT)

                // Cek apakah alasan adalah "Pulang"
                $status = ($request->reason == 'Pulang') ? 'closed' : 'active';

                StudentPermit::create([
                    'id' => (string) Str::uuid(),
                    'student_id' => $student->id,
                    'date' => $dateToday,
                    'time_out' => now(),
                    'reason' => $request->reason,
                    'status' => $status,
                    'method' => $request->method,
                    'image_evidence' => $imagePath,
                    'recorded_by' => Auth::user()->name ?? 'System'
                ]);

                $type = 'OUT';
                $message = "Izin Tercatat: " . $request->reason . ". Hati-hati!";
                if($status == 'active') $message .= " Jangan lupa scan saat kembali.";
            }

            return response()->json([
                'status' => 'success',
                'type' => $type,
                'message' => $message,
                'student' => $student,
                'reason' => $request->reason,
                'time' => now()->format('H:i')
            ]);

        } catch (\Exception $e) {
            Log::error("Permit Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}
