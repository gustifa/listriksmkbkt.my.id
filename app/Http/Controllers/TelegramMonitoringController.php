<?php

namespace App\Http\Controllers;

use App\Models\TelegramNotification;
use App\Models\TelegramSubscriber;
use App\Models\Student;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;

class TelegramMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil ID Kelas yang berhak diakses oleh User yang sedang login (Admin/Guru)
        $accessibleClassroomIds = Classroom::accessibleBy($user)->pluck('id');

        // 2. Metric Summary (Disesuaikan dengan cakupan kelas user)
        $totalSubscribers = TelegramSubscriber::whereHas('student', function ($q) use ($accessibleClassroomIds) {
            $q->whereIn('classroom_id', $accessibleClassroomIds);
        })->orWhereNull('student_id')->where('is_active', true)->count();

        $totalSentSuccess = TelegramNotification::where('status', 'success')->count();
        $totalSentFailed  = TelegramNotification::where('status', 'failed')->count();
        $todaySentCount   = TelegramNotification::whereDate('created_at', today())->count();

        // 3. Query Log Notifikasi
        $logsQuery = TelegramNotification::with('subscriber')->latest();

        if ($request->filled('status')) {
            $logsQuery->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $logsQuery->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $logsQuery->where(function ($q) use ($search) {
                $q->where('recipient_name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('chat_id', 'like', "%{$search}%");
            });
        }

        $logs = $logsQuery->paginate(15, ['*'], 'logs_page');

        // 4. Query Subscriber Pengguna (Orang Tua & Siswa)
        $subscribersQuery = TelegramSubscriber::with(['student.classroom'])
            ->where(function ($query) use ($accessibleClassroomIds) {
                $query->whereHas('student', function ($q) use ($accessibleClassroomIds) {
                    $q->whereIn('classroom_id', $accessibleClassroomIds);
                })
                ->orWhereNull('student_id');
            })
            ->latest();

        if ($request->filled('sub_search')) {
            $subSearch = $request->sub_search;
            $subscribersQuery->where(function ($q) use ($subSearch) {
                $q->where('parent_name', 'like', "%{$subSearch}%")
                  ->orWhere('chat_id', 'like', "%{$subSearch}%")
                  ->orWhere('phone_number', 'like', "%{$subSearch}%")
                  ->orWhere('user_type', 'like', "%{$subSearch}%")
                  ->orWhereHas('student', function ($sq) use ($subSearch) {
                      $sq->where('name', 'like', "%{$subSearch}%")
                        ->orWhere('nisn', 'like', "%{$subSearch}%");
                  });
            });
        }

        $subscribers = $subscribersQuery->paginate(15, ['*'], 'sub_page');

        // 5. Ambil daftar siswa untuk Modal Dropdown Select2
        $accessibleStudents = Student::with('classroom')
            ->whereIn('classroom_id', $accessibleClassroomIds)
            ->orderBy('name')
            ->get();

        return view('monitoring.telegram', compact(
            'totalSubscribers',
            'totalSentSuccess',
            'totalSentFailed',
            'todaySentCount',
            'logs',
            'subscribers',
            'accessibleStudents'
        ));
    }

    /**
     * Menyetujui Pendaftaran Akun & Menghubungkan ke Siswa
     */
    public function approveSubscriber(Request $request, $id, TelegramService $telegramService)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $subscriber = TelegramSubscriber::findOrFail($id);
        $student    = Student::with('classroom')->findOrFail($request->student_id);

        // Update status subscriber menjadi aktif, approved & isi timestamp subscribed_at
        $subscriber->update([
            'student_id'      => $student->id,
            'is_active'       => true,
            'approval_status' => 'approved',
            'subscribed_at'   => now()->format('Y-m-d H:i:s'),
        ]);

        // Hubungkan telegram_chat_id ke tabel student
        $student->update(['telegram_chat_id' => $subscriber->chat_id]);

        $roleLabel = ($subscriber->user_type ?? 'parent') === 'student' ? 'Siswa' : 'Orang Tua/Wali';

        // Kirim konfirmasi ke Telegram
        $pesan = "🎉 *Pendaftaran Disetujui Admin!*\n\n" .
                 "Akun Telegram Anda telah diverifikasi dan dihubungkan sebagai *{$roleLabel}*:\n" .
                 "👤 Nama Siswa: *{$student->name}*\n" .
                 "🏫 Kelas: " . ($student->classroom ? $student->classroom->name : '-') . "\n\n" .
                 "Notifikasi presensi harian sudah aktif.";

        $telegramService->sendMessage($subscriber->chat_id, $pesan, 'pengumuman', 'Verifikasi Pendaftaran');

        return back()->with('success', "Pendaftaran untuk {$subscriber->parent_name} berhasil disetujui!");
    }

    /**
     * Menolak/Membatalkan Pengajuan Pendaftaran
     */
    public function rejectSubscriber($id, TelegramService $telegramService)
    {
        $subscriber = TelegramSubscriber::findOrFail($id);

        $subscriber->update([
            'is_active'       => false,
            'approval_status' => 'rejected',
        ]);

        // Kirim penolakan ke Telegram
        $pesan = "❌ *Pendaftaran Ditolak Admin*\n\n" .
                 "Mohon maaf, pengajuan pendaftaran akun Telegram Anda (`+{$subscriber->phone_number}`) tidak dapat diverifikasi.\n\n" .
                 "Silakan hubungi Pihak Sekolah / Admin SISFO untuk pembaruan data nomor HP.";

        $telegramService->sendMessage($subscriber->chat_id, $pesan, 'pengumuman', 'Verifikasi Pendaftaran');

        return back()->with('success', "Pengajuan Pendaftaran {$subscriber->parent_name} berhasil ditolak.");
    }

    // Hapus 1 item log berdasarkan ID (UUID)
    public function destroyLog($id)
    {
        $log = TelegramNotification::findOrFail($id);
        $log->delete();

        return redirect()->back()->with('success', 'Log riwayat pesan berhasil dihapus.');
    }

    // Hapus seluruh log pesan
    public function clearLogs()
    {
        TelegramNotification::truncate();

        return redirect()->back()->with('success', 'Semua log riwayat pesan berhasil dibersihkan.');
    }

    /**
     * Membatalkan/Menonaktifkan Akun Telegram yang Sudah Terdaftar
     */
    public function cancelSubscriber($id, TelegramService $telegramService)
    {
        $subscriber = TelegramSubscriber::findOrFail($id);

        // 1. Kosongkan telegram_chat_id di tabel student jika ada
        if ($subscriber->student_id) {
            Student::where('id', $subscriber->student_id)->update(['telegram_chat_id' => null]);
        }

        // 2. Ubah status subscriber menjadi nonaktif & rejected
        $subscriber->update([
            'student_id'      => null,
            'is_active'       => false,
            'approval_status' => 'rejected',
        ]);

        // 3. Kirim notifikasi ke Telegram
        $pesan = "⚠️ *Pendaftaran Telegram Dinonaktifkan Admin*\n\n" .
                 "Akun Telegram Anda telah dilepaskan dari sistem notifikasi presensi sekolah oleh Admin SISFO.\n\n" .
                 "Jika ini adalah kekeliruan, silakan hubungi pihak sekolah atau ketik /start untuk mendaftar kembali.";

        $telegramService->sendMessage($subscriber->chat_id, $pesan, 'pengumuman', 'Penonaktifan Akun');

        return back()->with('success', "Pendaftaran Telegram untuk {$subscriber->parent_name} berhasil dibatalkan!");
    }
}