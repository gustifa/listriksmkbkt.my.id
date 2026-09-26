<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeachingJournal;
use Illuminate\Support\Facades\Storage;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeachingJournalController extends Controller
{
    /**
     * Halaman Utama Jurnal Guru
     * Menampilkan jadwal pada tanggal tertentu (default hari ini)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return redirect()->back()->with('error', 'Akses khusus Guru.');
        }

        // Default: Hari ini, atau tanggal yang dipilih filter
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // Mapping Hari Inggris ke Indo untuk query jadwal
        $dayMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $dayName = $dayMap[$date->format('l')];

        // Ambil Jadwal Guru pada Hari Tersebut
        $schedules = Schedule::with(['classroom', 'subject'])
                        ->where('teacher_id', $teacher->id)
                        ->where('day', $dayName)
                        ->orderBy('start_time')
                        ->get();

        // Ambil Jurnal yang sudah diisi pada tanggal tersebut (untuk status "Sudah Diisi")
        $filledJournals = TeachingJournal::whereIn('schedule_id', $schedules->pluck('id'))
                            ->whereDate('date', $date)
                            ->get()
                            ->keyBy('schedule_id');

        return view('guru.journal.index', compact('schedules', 'filledJournals', 'date'));
    }

    // Simpan atau Update Jurnal
    // public function store(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'schedule_id' => 'required|exists:schedules,id',
    //             'topic'       => 'required|string|max:255',
    //             'activity'    => 'nullable|string',
    //         ]);

    //         // Cek apakah jurnal untuk jadwal ini sudah ada (update) atau belum (create)
    //         $journal = TeachingJournal::updateOrCreate(
    //             ['schedule_id' => $request->schedule_id],
    //             [
    //                 'topic'    => $request->topic,
    //                 'activity' => $request->activity,
    //                 'notes'    => $request->notes,
    //             ]
    //         );

    //         // Handle Upload Foto (Jika ada)
    //         if ($request->hasFile('photo')) {
    //             // Hapus foto lama jika ada
    //             if ($journal->photo_evidence) {
    //                 Storage::disk('public')->delete($journal->photo_evidence);
    //             }

    //             $path = $request->file('photo')->store('journals', 'public');
    //             $journal->update(['photo_evidence' => $path]);
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Jurnal pembelajaran berhasil disimpan!',
    //             'data' => $journal
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal menyimpan jurnal: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // Ambil data jurnal (untuk ditampilkan di modal saat dibuka)
    // public function show($scheduleId)
    // {
    //     $journal = TeachingJournal::where('schedule_id', $scheduleId)->first();

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $journal
    //     ]);
    // }

    /**
     * Simpan / Update Jurnal (AJAX)
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'topic'       => 'required|string|max:255',
                'activity'    => 'nullable|string',
                'date'        => 'nullable|date', // Tanggal opsional, default hari ini
            ]);

            // Gunakan tanggal yang dikirim atau default hari ini
            $journalDate = $request->date ?? Carbon::today()->format('Y-m-d');

            // Cek apakah jurnal untuk JADWAL ini pada TANGGAL ini sudah ada
            // FIX: Menggunakan kombinasi schedule_id AND date agar data historis aman
            $journal = TeachingJournal::updateOrCreate(
                [
                    'schedule_id' => $request->schedule_id,
                    'date'        => $journalDate // Kunci Unik
                ],
                [
                    'topic'    => $request->topic,
                    'activity' => $request->activity,
                    'notes'    => $request->notes,
                ]
            );

            // Handle Upload Foto (Opsional)
            if ($request->hasFile('photo')) {
                if ($journal->photo_evidence) {
                    Storage::disk('public')->delete($journal->photo_evidence);
                }
                $path = $request->file('photo')->store('journals', 'public');
                $journal->update(['photo_evidence' => $path]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Jurnal berhasil disimpan untuk tanggal ' . Carbon::parse($journalDate)->translatedFormat('d F Y'),
                'data' => $journal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil data jurnal (untuk Edit Modal)
     */
    public function show(Request $request, $scheduleId)
    {
        // Ambil tanggal dari request (penting agar edit data yang benar) atau default hari ini
        $date = $request->date ?? Carbon::today()->format('Y-m-d');

        $journal = TeachingJournal::where('schedule_id', $scheduleId)
                    ->whereDate('date', $date) // Filter Tanggal
                    ->first();

        return response()->json([
            'status' => 'success',
            'data' => $journal
        ]);
    }
}
