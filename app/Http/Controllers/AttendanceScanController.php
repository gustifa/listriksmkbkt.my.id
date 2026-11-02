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
public function review($id)
{
    // Ambil data scan berdasarkan UUID
    $scan = AttendanceScan::findOrFail($id);

    // Eksekusi ulang OCR & Matching data siswa
    $ocrResults =$this->ocrService->processImage($scan->image_path);$matchedData = $this->ocrService->matchStudents($ocrResults);

    return view('attendance.review', compact('scan', 'matchedData'));
}
public function commit(Request $request, $id)
{
    $scan = AttendanceScan::findOrFail($id);

    // Validasi dengan pesan custom Bahasa Indonesia
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'attendances' => 'required|array|min:1',
        'attendances.*.student_id' => 'required|uuid|exists:students,id',
        'attendances.*.status' => 'required|string',
    ], [
        'attendances.required' => 'Tidak ada data siswa yang dapat disimpan. Pastikan hasil scan terdeteksi.',
        'attendances.min'      => 'Minimal harus ada 1 data siswa yang terdeteksi.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    // ... sisa kode commit sama seperti sebelumnya
}


}