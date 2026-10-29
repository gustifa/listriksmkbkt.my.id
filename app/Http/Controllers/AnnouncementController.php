<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\TelegramSubscriber;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    // Tampilkan daftar pengumuman
    public function index()
    {
        $announcements = Announcement::with('classroom')->latest()->paginate(10);
        $classrooms = Classroom::all();
        return view('admin.announcements.index', compact('announcements', 'classrooms'));
    }

    // Simpan pengumuman baru & kirim broadcast ke Telegram
    public function store(Request $request, TelegramService $telegramService)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'target_role'  => 'required|in:all,parent,student',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'attachment'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('announcements', 'public');
        }

        // Simpan ke database
        $announcement = Announcement::create([
            'title'        => $request->title,
            'content'      => $request->content,
            'target_role'  => $request->target_role,
            'classroom_id' => $request->classroom_id ?: null,
            'attachment'   => $attachmentPath,
            'created_by'   => Auth::id(),
        ]);

        // === PROSES BROADCAST KE TELEGRAM ORANG TUA / SISWA ===
        $query = TelegramSubscriber::where('is_active', true)
            ->where('approval_status', 'approved');

        if ($request->target_role === 'parent') {
            $query->where('user_type', 'parent');
        } elseif ($request->target_role === 'student') {
            $query->where(function($q) {
                $q->where('user_type', 'student')
                  ->orWhereNotNull('student_id');
            });
        }

        if ($request->filled('classroom_id')) {
            $classroomId = $request->classroom_id;
            $query->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        $subscribers = $query->get();

        // Format Pesan Telegram
        $msgText = "📢 *PENGUMUMAN SEKOLAH*\n\n" .
                   "📌 *{$announcement->title}*\n\n" .
                   "{$announcement->content}\n\n";

        // Sertakan link unduhan jika ada lampiran
        if ($announcement->attachment) {
            $fileUrl = asset('storage/' . $announcement->attachment);
            $msgText .= "📎 *Lampiran Berkas:* [Unduh Berkas]({$fileUrl})\n\n";
        }

        $msgText .= "--- *SMKN 1 Bukittinggi* ---";

        foreach ($subscribers as $sub) {
            try {
                if (!empty($sub->chat_id)) {
                    $telegramService->sendMessage($sub->chat_id, $msgText);
                }
            } catch (\Exception $e) {
                // Lewati jika gagal kirim ke chat_id tertentu
            }
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dipublikasikan dan disiarkan via Telegram!');
    }

    // Hapus pengumuman
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        if ($announcement->attachment) {
            Storage::disk('public')->delete($announcement->attachment);
        }
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    // Pratinjau daftar penerima broadcast sebelum dikirim
    public function previewRecipients(Request $request)
    {
        $targetRole  = $request->get('target_role', 'all');
        $classroomId = $request->get('classroom_id');

        $query = TelegramSubscriber::with(['student.classroom'])
            ->where('is_active', true)
            ->where('approval_status', 'approved');

        if ($targetRole === 'parent') {
            $query->where('user_type', 'parent');
        } elseif ($targetRole === 'student') {
            $query->where('user_type', 'student');
        }

        if (!empty($classroomId)) {
            $query->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        $subscribers = $query->get();

        $recipients = $subscribers->map(function ($sub) {
            $studentName = optional($sub->student)->name ?? '-';
            $className   = optional(optional($sub->student)->classroom)->name ?? 'Tanpa Kelas';
            $roleLabel   = ($sub->user_type ?? 'parent') === 'student' ? 'Siswa' : 'Orang Tua/Wali';

            return [
                'name'      => $sub->parent_name ?? 'Tanpa Nama',
                'role'      => $roleLabel,
                'student'   => $studentName,
                'class'     => $className,
                'phone'     => $sub->phone_number ? '+' . $sub->phone_number : '-',
            ];
        });

        return response()->json([
            'total'      => $recipients->count(),
            'recipients' => $recipients
        ]);
    }
}