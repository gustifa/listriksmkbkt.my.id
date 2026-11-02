<?php

namespace App\Http\Controllers;

use App\Models\AttendanceScan;
use App\Services\AttendanceOcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DailyAttendance;
use App\Models\Attendance; // Tabel `attendances` (Pembelajaran)
use App\Models\PrayerAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AttendanceScanController extends Controller
{
    protected $ocrService;

    public function __construct(AttendanceOcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Menampilkan Halaman Upload
     */
    public function index()
    {
        return view('attendance.upload');
    }

    /**
     * Handle AJAX Upload Foto Absensi
     */
    public function upload(Request $request)
    {
        // 1. Validasi Input Request
        $validator = Validator::make($request->all(), [
            'scan_type' => 'required|in:daily,learning,prayer',
            'scan_date' => 'required|date',
            'image'     => 'required|image|mimes:jpeg,jpg,png|max:10240', // Max 10MB
        ], [
            'scan_type.required' => 'Pilih tipe absensi terlebih dahulu.',
            'scan_date.required' => 'Tanggal absensi wajib diisi.',
            'image.required'     => 'Foto lembar absensi wajib diunggah.',
            'image.image'        => 'Berkas harus berupa gambar.',
            'image.mimes'        => 'Format gambar harus JPG, JPEG, atau PNG.',
            'image.max'          => 'Ukuran foto maksimal adalah 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // 2. Simpan Foto ke Storage (`storage/app/public/attendance_scans`)
            $file = $request->file('image');
            $imagePath = $file->store('attendance_scans', 'public');

            // 3. Simpan Log Scan ke Database
            $scan = AttendanceScan::create([
                'scan_type'   => $request->scan_type,
                'scan_date'   => $request->scan_date,
                'image_path'  => $imagePath,
                'uploaded_by' => auth()->id(),
                'status'      => 'pending',
            ]);

            // 4. Eksekusi OCR Service & Fuzzy Match Siswa
            $ocrResults = $this->ocrService->processImage($imagePath);
            $matchedData = $this->ocrService->matchStudents($ocrResults);

            // 5. Kembalikan Respon JSON untuk AJAX & SweetAlert2
            return response()->json([
                'success'      => true,
                'message'      => 'Foto daftar hadir berhasil diuraikan.',
                'scan_id'      => $scan->id,
                'redirect_url' => route('attendance.scans.review', $scan->id),
                'data'         => $matchedData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Menampilkan Halaman Verifikasi / Review Hasil Scan OCR
 */
// public function review($id)
// {
//     // Ambil data scan berdasarkan UUID
//     $scan = AttendanceScan::findOrFail($id);

//     // Eksekusi ulang OCR & Matching data siswa
//     $ocrResults =$this->ocrService->processImage($scan->image_path);$matchedData = $this->ocrService->matchStudents($ocrResults);

//     return view('attendance.review', compact('scan', 'matchedData'));
// }

public function review($id)
{
    $scan = AttendanceScan::findOrFail($id);

    // Ambil classroom_id dari record scan (jika diset saat upload)
    $classroomId = $scan->classroom_id ?? $scan->class_id ?? null;

    // Ekstrak teks & lakukan matching terarah hanya ke kelas tersebut
    $ocrResults  = $this->ocrService->processImage($scan->image_path);
    $matchedData = $this->ocrService->matchStudents($ocrResults, $classroomId);

    // Fallback: Jika OCR kosong, ambil seluruh siswa yang ada di KELAS TERSEBUT saja
    if (empty($matchedData)) {
        $studentQuery = Student::select('id', 'nis', 'name', 'classroom_id')->orderBy('name', 'asc');

        if ($classroomId) {
            $studentQuery->where('classroom_id', $classroomId);
        }

        $students = $studentQuery->get();

        foreach ($students as $student) {
            $matchedData[] = [
                'raw_text'             => $student->nis ? "{$student->nis} - {$student->name}" : $student->name,
                'matched_student_id'   => $student->id,
                'matched_student_name' => $student->name,
                'similarity_score'     => 100,
                'status'               => 'hadir',
                'is_verified'          => true,
            ];
        }
    }

    return view('attendance.review', compact('scan', 'matchedData'));
}


/**
 * Simpan Hasil Absensi yang Diverifikasi ke Database Utama
 */
/**
 * Simpan Hasil Absensi yang Diverifikasi ke Database Utama
 */
public function commit(Request $request, $id)
{
    $scan = AttendanceScan::findOrFail($id);
    $attendances = $request->input('attendances', []);

    if (empty($attendances)) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada data siswa yang dikirim untuk disimpan.'
        ], 422);
    }

    // Mapping status dari OCR/Form ke format PostgreSQL Constraint
    $statusMap = [
        'present'    => 'hadir',
        'late'       => 'terlambat',
        'permission' => 'izin',
        'sick'       => 'sakit',
        'absent'     => 'alpa',
        // Jika input sudah huruf kecil bahasa Indonesia
        'hadir'      => 'hadir',
        'terlambat'  => 'terlambat',
        'izin'       => 'izin',
        'sakit'      => 'sakit',
        'alpa'       => 'alpa',
    ];

    DB::beginTransaction();
    try {
        $currentTime = now()->format('H:i:s');
        $recordedBy  = auth()->user()->name ?? 'Administrator';
        $savedCount  = 0;

        foreach ($attendances as $item) {
            if (empty($item['student_id'])) {
                continue;
            }

            $studentId  = $item['student_id'];
            $rawStatus  = strtolower($item['status'] ?? 'hadir');
            $dbStatus   = $statusMap[$rawStatus] ?? 'hadir'; // Fallback ke 'hadir'

            switch ($scan->scan_type) {
                case 'daily':
                default:
                    DailyAttendance::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'date'       => $scan->scan_date,
                        ],
                        [
                            'arrival_time' => $currentTime,
                            'status'       => $dbStatus, // Menggunakan status yang sudah di-mapping ('hadir')
                            'recorded_by'  => $recordedBy,
                            'is_synced'    => true,
                        ]
                    );
                    break;

                case 'learning':
                    Attendance::updateOrCreate(
                        [
                            'student_id'  => $studentId,
                            'date'        => $scan->scan_date,
                            'schedule_id' => $request->input('schedule_id'),
                        ],
                        [
                            'subject_id'    => $request->input('subject_id'),
                            'check_in_time' => $currentTime,
                            'status'        => $dbStatus,
                            'recorded_by'   => $recordedBy,
                            'is_synced'     => true,
                        ]
                    );
                    break;

                case 'prayer':
                    PrayerAttendance::updateOrCreate(
                        [
                            'student_id'  => $studentId,
                            'date'        => $scan->scan_date,
                            'prayer_name' => $request->input('prayer_name', 'Dzuhur'),
                        ],
                        [
                            'check_in_time' => $currentTime,
                            'status'        => $dbStatus,
                            'location_type' => 'school',
                            'is_synced'     => true,
                        ]
                    );
                    break;
            }

            $savedCount++;
        }

        $scan->update(['status' => 'processed']);
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyimpan {$savedCount} data absensi siswa untuk tanggal {$scan->scan_date}."
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Commit Attendance Error: " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()
        ], 500);
    }
}

}