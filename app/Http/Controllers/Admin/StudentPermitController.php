<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentPermit;
use App\Models\Schedule; // <-- Make sure Schedule Model is imported
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

        // 1. CARI MAPEL & GURU DARI JADWAL AKTIF UNTUK SETIAP RIWAYAT
        $permits->transform(function ($permit) {
            if ($permit->student && $permit->student->classroom_id) {
                $permitTime = Carbon::parse($permit->time_out ?? $permit->created_at)->format('H:i:s');
                $days = [
                    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
                ];
                $dayName = $days[Carbon::parse($permit->date)->format('l')] ?? 'Senin';

                $schedule = Schedule::with(['subject', 'teacher'])
                    ->where('classroom_id', $permit->student->classroom_id)
                    ->where('day', $dayName)
                    ->where('start_time', '<=', $permitTime)
                    ->where('end_time', '>=', $permitTime)
                    ->first();

                $permit->subject_name = $schedule->subject->name ?? '-';
                $permit->teacher_name = $schedule->teacher->name ?? '-';
            } else {
                $permit->subject_name = '-';
                $permit->teacher_name = '-';
            }
            return $permit;
        });

        $stats = [
            'total' => $permits->count(),
            'active' => $permits->where('status', 'active')->count(),
            'returned' => $permits->where('status', 'returned')->count(),
        ];

        return view('admin.permit.index', compact('permits', 'date', 'stats'));
    }

    // Halaman Scanner
    // Halaman Scanner
// Halaman Scanner
public function scan()
{
    $now = Carbon::now();
    $days = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    $dayName = $days[$now->format('l')] ?? 'Senin';

    $actives = StudentPermit::with(['student.classroom'])
        ->whereDate('date', $now->format('Y-m-d'))
        ->where('status', 'active')
        ->orderBy('time_out', 'desc')
        ->get();

    // Olah data untuk menyertakan nama mapel dan guru
    $actives->transform(function ($permit) use ($dayName) {
        if ($permit->student && $permit->student->classroom_id) {
            $timeOut = Carbon::parse($permit->time_out)->format('H:i:s');

            $schedule = Schedule::with(['subject', 'teacher'])
                ->where('classroom_id', $permit->student->classroom_id)
                ->where('day', $dayName)
                ->where('start_time', '<=', $timeOut)
                ->where('end_time', '>=', $timeOut)
                ->first();

            $permit->subject_name = $schedule->subject->name ?? '-';
            $permit->teacher_name = $schedule->teacher->name ?? '-';
        } else {
            $permit->subject_name = '-';
            $permit->teacher_name = '-';
        }
        return $permit;
    });

    return view('admin.permit.scan', compact('actives'));
}

    // Proses Simpan Data
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nis' => 'required_without:student_id',
                'reason' => 'required|string',
                'method' => 'required|in:barcode,face,manual',
                'image' => 'nullable',
            ]);

            $now = Carbon::now();
            $dateToday = $now->format('Y-m-d');

            // 1. Cari Siswa
            $student = null;
            if ($request->filled('nis')) {
                $nis = trim($request->nis);
                $student = Student::with('classroom')->where('nis', $nis)->first();
            }

            if (!$student) {
                return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!'], 404);
            }

            // --- CARI MATA PELAJARAN & GURU SAAT INI ---
            $days = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            $currentDay = $days[$now->format('l')] ?? 'Senin';
            $currentTime = $now->format('H:i:s');

            $activeSchedule = Schedule::with(['subject', 'teacher'])
                ->where('classroom_id', $student->classroom_id)
                ->where('day', $currentDay)
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->first();

            $subjectName = $activeSchedule->subject->name ?? 'Tidak Ada Jam Pelajaran';
            $teacherName = $activeSchedule->teacher->name ?? '-';

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
                $activePermit->update([
                    'time_in' => now(),
                    'status' => 'returned',
                ]);

                $type = 'RETURN';
                $message = "Selamat Belajar Kembali! Terima kasih sudah melapor.";

            } else {
                // KASUS: IZIN KELUAR (OUT)
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

            // KEMBALIKAN DATA DENGAN DETAIL MAPEL DAN GURU
            return response()->json([
                'status' => 'success',
                'type' => $type,
                'message' => $message,
                'student' => $student,
                'classroom' => $student->classroom->name ?? '-',
                'subject' => $subjectName,
                'teacher' => $teacherName,
                'reason' => $request->reason,
                'time' => now()->format('H:i')
            ]);

        } catch (\Exception $e) {
            Log::error("Permit Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}
