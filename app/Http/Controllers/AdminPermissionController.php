<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AdminPermissionController extends Controller
{
    // Menampilkan halaman daftar izin
    public function index()
    {
        $permissions = StudentPermission::with('student')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.permissions.index', compact('permissions'));
    }

    // Proses klik tombol Approve
    public function approve($id)
    {
        $izin = StudentPermission::with('student')->findOrFail($id);

        DB::transaction(function () use ($izin) {
            // 1. Ubah status jadi approved
            $izin->update(['status' => 'approved']);

            // 2. Masukkan ke tabel attendances (sesuai struktur listrik_1.sql)
            // Catatan: Jika tabel attendances butuh schedule_id / subject_id,
            // pastikan Anda menyesuaikan default value-nya di sini
            DB::table('attendances')->insert([
                'id' => Str::uuid(),
                'student_id' => $izin->student_id,
                'schedule_id' => 'ISI_DEFAULT_UUID_JIKA_ADA', // Sesuaikan
                'subject_id' => 'ISI_DEFAULT_UUID_JIKA_ADA',  // Sesuaikan
                'date' => $izin->date,
                'check_in_time' => now()->format('H:i:s'),
                'status' => 'izin',
                'recorded_by' => 'Sistem WA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        // 3. Notifikasi balasan ke WA grup / orang tua bahwa izin di-ACC
        if ($izin->wa_number) {
            Http::post('http://127.0.0.1:3000/send-message', [
                'number' => $izin->wa_number,
                'message' => "✅ Izin untuk *{$izin->student->name}* pada tanggal {$izin->date} dengan alasan '{$izin->reason}' telah *DISETUJUI* oleh Admin.",
            ]);
        }

        return redirect()->back()->with('success', 'Izin berhasil disetujui dan masuk ke data absensi.');
    }
}
