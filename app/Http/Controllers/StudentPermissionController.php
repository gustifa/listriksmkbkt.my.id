<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentPermission; // Pastikan buat Model ini
use App\Models\DailyAttendance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class StudentPermissionController extends Controller
{
    /**
     * Menampilkan detail izin (Method yang error sebelumnya).
     * Digunakan jika Anda menggunakan Route::resource atau mengakses /permissions/{id}
     */
    public function show($id)
    {
        // Kita gunakan tampilan yang sama dengan print untuk detailnya
        return $this->print($id);
    }

    // Cek status siswa sebelum izin
    public function check(Request $request)
    {
        $student = Student::with('classroom')->where('nis', $request->nis)->first();
        if (!$student) return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan'], 404);

        $today = Carbon::today();

        // 1. Cek apakah siswa sedang izin keluar (belum kembali)
        $activePermit = StudentPermission::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->whereNull('time_back')
            ->first();

        if ($activePermit) {
            return response()->json([
                'status' => 'active_permission',
                'data' => $activePermit->load('student')
            ]);
        }

        // 2. Cek apakah siswa sudah absen masuk hari ini (Syarat izin: harus sudah masuk)
        $attendance = DailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->whereNotNull('arrival_time')
            ->first();

        if (!$attendance) {
            return response()->json(['status' => 'error', 'message' => 'Siswa belum melakukan absen masuk hari ini!']);
        }

        if ($attendance->departure_time) {
            return response()->json(['status' => 'error', 'message' => 'Siswa sudah melakukan absen pulang!']);
        }

        return response()->json([
            'status' => 'can_leave',
            'student' => [
                'name' => $student->name,
                'classroom' => $student->classroom->name ?? '-'
            ]
        ]);
    }

    // Simpan Izin Keluar
    // public function store(Request $request)
    // {
    //     $student = Student::where('nis', $request->nis)->firstOrFail();

    //     $permit = StudentPermission::create([
    //         'student_id' => $student->id,
    //         'date' => now(),
    //         'time_out' => now(),
    //         'reason' => $request->reason,
    //         'status' => 'out'
    //     ]);

    //     return response()->json(['status' => 'success', 'id' => $permit->id]);
    // }


    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required',
            'reason' => 'required'
        ]);

        $student = Student::where('nis', $request->nis)->firstOrFail();

        // 1. Simpan Data Izin ke DB dulu
        $permit = StudentPermission::create([
            'student_id' => $student->id,
            'date' => now()->format('Y-m-d'),
            'time_out' => now()->format('H:i:s'),
            'reason' => $request->reason,
            'status' => 'out'
        ]);

        $school = $this->getSchoolData();

        // 2. Generate PDF dari View (Gunakan view yang sama dengan print)
        // Pastikan view 'reports.permission_print' sudah ada dan rapi
        $pdf = Pdf::loadView('report.permission_print', compact('permit', 'school'));

        // 3. Simpan File PDF ke Storage
        // Nama file: IZIN-{NIS}-{TIMESTAMP}.pdf
        $filename = 'IZIN-' . $student->nis . '-' . $student->name . '-' . time() . '.pdf';
        $path = 'permissions/' . $filename;

        // Simpan ke storage/app/public/permissions/
        Storage::disk('public')->put($path, $pdf->output());

        // 4. Update Database dengan Path File
        $permit->update(['file_path' => $path]);

        return response()->json([
            'status' => 'success',
            'id' => $permit->id,
            'file_url' => asset('storage/' . $path) // Opsional: kirim URL file
        ]);
    }

    // Simpan Siswa Kembali
    public function markReturn(Request $request)
    {
        $permit = StudentPermission::findOrFail($request->id);
        $permit->update([
            'time_back' => now(),
            'status' => 'returned'
        ]);

        return response()->json(['status' => 'success']);
    }

    // Cetak Surat Izin (Format sesuai permintaan upload)
    public function print($id)
    {
        // $school = $this->getSchoolData();
        $permit = StudentPermission::with(['student.classroom'])->findOrFail($id);
        // FIX: Menambahkan variabel $school
        $school = $this->getSchoolData();
        return view('report.permission_print', compact('permit', 'school' ));
    }

    private function getSchoolData()
    {
        return [
            // Identitas Sekolah
            'name'       => Setting::value('school_name', 'SMK DEFAULT'),
            'address'    => Setting::value('school_address', 'Alamat Sekolah'),
            'phone'      => Setting::value('school_phone', '-'),
            'web'        => Setting::value('school_web', '-'),
            'email'      => Setting::value('school_email', '-'),
            'logo_left'  => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),

            // Pengaturan Kertas
            'paper_size'        => Setting::value('paper_size', 'a4'),
            'paper_orientation' => Setting::value('paper_orientation', 'portrait'),

            // Pengaturan Margin (Tambahkan satuan cm/mm untuk CSS)
            'margin_top'    => Setting::value('margin_top', '2.5') . 'cm',
            'margin_right'  => Setting::value('margin_right', '2.5') . 'cm',
            'margin_bottom' => Setting::value('margin_bottom', '2.5') . 'cm',
            'margin_left'   => Setting::value('margin_left', '2.5') . 'cm',

            // Tanda Tangan
            'sign_city'  => Setting::value('signature_city', 'Jakarta'),
            'sign_title' => Setting::value('signature_title', 'Kepala Sekolah'),
            'sign_name'  => Setting::value('signature_name', 'Administrator'),
            'sign_nip'   => Setting::value('signature_nip', '-'),
        ];
    }
}
