<?php

namespace App\Http\Controllers;

use App\Models\TelegramNotification;
use App\Models\TelegramSubscriber;
use App\Models\Student;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class TelegramMonitoringController extends Controller
{
    // public function index(Request $request)
    // {
    //     // Metric Summary
    //     $totalSubscribers = TelegramSubscriber::where('is_active', true)->count();
    //     $totalSentSuccess = TelegramNotification::where('status', 'success')->count();
    //     $totalSentFailed  = TelegramNotification::where('status', 'failed')->count();
    //     $todaySentCount   = TelegramNotification::whereDate('created_at', today())->count();

    //     // Query Log Notifikasi
    //     $logsQuery = TelegramNotification::with('subscriber')->latest();

    //     if ($request->filled('status')) {
    //         $logsQuery->where('status', $request->status);
    //     }

    //     if ($request->filled('type')) {
    //         $logsQuery->where('type', $request->type);
    //     }

    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $logsQuery->where(function ($q) use ($search) {
    //             $q->where('recipient_name', 'like', "%{$search}%")
    //               ->orWhere('title', 'like', "%{$search}%")
    //               ->orWhere('chat_id', 'like', "%{$search}%");
    //         });
    //     }

    //     $logs = $logsQuery->paginate(15, ['*'], 'logs_page');

    //     // Query Subscriber (Orang Tua)
    //     $subscribersQuery = TelegramSubscriber::with('student')->latest();

    //     if ($request->filled('sub_search')) {
    //         $subSearch = $request->sub_search;
    //         $subscribersQuery->where(function ($q) use ($subSearch) {
    //             $q->where('parent_name', 'like', "%{$subSearch}%")
    //               ->orWhere('chat_id', 'like', "%{$subSearch}%")
    //               ->orWhere('phone_number', 'like', "%{$subSearch}%");
    //         });
    //     }

    //     $subscribers = $subscribersQuery->paginate(15, ['*'], 'sub_page');

    //     return view('monitoring.telegram', compact(
    //         'totalSubscribers',
    //         'totalSentSuccess',
    //         'totalSentFailed',
    //         'todaySentCount',
    //         'logs',
    //         'subscribers'
    //     ));
    // }
    public function index(Request $request)
{
    // 1. Metric Summary
    $totalSubscribers = TelegramSubscriber::where('is_active', true)->count();
    $totalSentSuccess = TelegramNotification::where('status', 'success')->count();
    $totalSentFailed  = TelegramNotification::where('status', 'failed')->count();
    $todaySentCount   = TelegramNotification::whereDate('created_at', today())->count();

    // 2. Query Log Notifikasi
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

    // 3. Query Subscriber (Orang Tua)
    $subscribersQuery = TelegramSubscriber::with('student.classroom')->latest();

    if ($request->filled('sub_search')) {
        $subSearch = $request->sub_search;
        $subscribersQuery->where(function ($q) use ($subSearch) {
            $q->where('parent_name', 'like', "%{$subSearch}%")
              ->orWhere('chat_id', 'like', "%{$subSearch}%")
              ->orWhere('phone_number', 'like', "%{$subSearch}%");
        });
    }

    $subscribers = $subscribersQuery->paginate(15, ['*'], 'sub_page');

    // 4. [BARU] Ambil Siswa yang Berada di Kelas Sesuai Hak Akses User via Scope accessibleBy
    $accessibleClassroomIds = Classroom::accessibleBy(Auth::user())->pluck('id');

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
        'accessibleStudents' // <--- Oper variabel ini ke View
    ));
}

    /**
     * Menyetujui Pendaftaran Orang Tua & Menghubungkan ke Siswa
     */
    public function approveSubscriber(Request $request, $id, TelegramService $telegramService)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $subscriber = TelegramSubscriber::findOrFail($id);
        $student    = Student::with('classroom')->findOrFail($request->student_id);

        // Update status subscriber menjadi aktif & approved
        $subscriber->update([
            'student_id'      => $student->id,
            'is_active'       => true,
            'approval_status' => 'approved',
        ]);

        // Hubungkan telegram_chat_id ke tabel student
        $student->update(['telegram_chat_id' => $subscriber->chat_id]);

        // Send confirmation back to parent's Telegram
        $pesan = "🎉 *Pendaftaran Disetujui Admin!*\n\n" .
                 "Akun Telegram Anda telah diverifikasi dan dihubungkan dengan siswa:\n" .
                 "👤 Nama: *{$student->name}*\n" .
                 "🏫 Kelas: " . ($student->classroom ? $student->classroom->name : '-') . "\n\n" .
                 "Notifikasi presensi harian sudah aktif.";

        $telegramService->sendMessage($subscriber->chat_id, $pesan, 'pengumuman', 'Verifikasi Pendaftaran');

        return back()->with('success', "Pendaftaran untuk {$subscriber->parent_name} berhasil disetujui!");
    }

    /**
     * Menolak/Membatalkan Pengajuan Pendaftaran Orang Tua
     */
    public function rejectSubscriber($id, TelegramService $telegramService)
    {
        $subscriber = TelegramSubscriber::findOrFail($id);

        $subscriber->update([
            'is_active'       => false,
            'approval_status' => 'rejected',
        ]);

        // Send rejection notification to parent's Telegram
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

    // Hapus seluruh log pesan (Kosongkan riwayat)
    public function clearLogs()
    {
        TelegramNotification::truncate();

        return redirect()->back()->with('success', 'Semua log riwayat pesan berhasil dibersihkan.');
    }

    /**
 * Membatalkan/Menonaktifkan Akun Telegram Orang Tua yang Sudah Terdaftar
 */
public function cancelSubscriber($id, TelegramService $telegramService)
{
    $subscriber = TelegramSubscriber::findOrFail($id);

    // 1. Kosongkan telegram_chat_id di tabel student jika ada
    if ($subscriber->student_id) {
        Student::where('id', $subscriber->student_id)->update(['telegram_chat_id' => null]);
    }

    // 2. Ubah status subscriber menjadi nonaktif & rejected/cancelled
    $subscriber->update([
        'student_id'      => null,
        'is_active'       => false,
        'approval_status' => 'rejected',
    ]);

    // 3. Kirim notifikasi ke Telegram Orang Tua bahwa akunnya telah dinonaktifkan Admin
    $pesan = "⚠️ *Pendaftaran Telegram Dinonaktifkan Admin*\n\n" .
             "Akun Telegram Anda telah dilepaskan dari sistem notifikasi presensi siswa oleh Admin SISFO.\n\n" .
             "Jika ini adalah kekeliruan, silakan hubungi pihak sekolah atau ketik /start untuk mendaftar kembali.";

    $telegramService->sendMessage($subscriber->chat_id, $pesan, 'pengumuman', 'Penonaktifan Akun');

    return back()->with('success', "Pendaftaran Telegram untuk {$subscriber->parent_name} berhasil dibatalkan!");
}
}