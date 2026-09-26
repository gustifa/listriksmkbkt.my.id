<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Teacher; 
use App\Models\TeachingAssignment; // Model Mapping
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Helper untuk mendapatkan Guru yang sedang login
     */
    private function getTeacher()
    {
        return Teacher::where('user_id', Auth::id())->first();
    }

    /**
     * Menampilkan Daftar Jadwal Guru
     */
    public function index()
    {
        $teacher = $this->getTeacher();
        
        if (!$teacher) {
            return redirect()->back()->with('error', 'Akun Anda tidak terdaftar sebagai Guru.');
        }

        // Ambil jadwal guru
        $schedules = Schedule::with(['classroom', 'subject'])
            // Hitung jumlah siswa yang sudah diabsen pada jadwal ini (HARI INI)
            ->withCount(['attendances' => function ($query) {
                // Asumsi tabel attendances menggunakan timestamps created_at untuk tanggal
                $query->whereDate('created_at', Carbon::today());
            }])
            ->where('teacher_id', $teacher->id)
            ->get();

        // Urutkan berdasarkan Hari (Senin -> Minggu) dan Jam Mulai
        $daysOrder = [
            'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3,
            'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7
        ];

        $schedules = $schedules->sortBy(function ($schedule) use ($daysOrder) {
            return ($daysOrder[$schedule->day] ?? 99) * 10000 + (int)str_replace(':', '', $schedule->start_time);
        });

        return view('schedule.index', compact('schedules'));
    }

    /**
     * Form Tambah Jadwal Baru (FILTERED BY MAPPING)
     */
    public function create()
    {
        $teacher = $this->getTeacher();
        if (!$teacher) abort(403, 'Akses khusus Guru');

        // 1. Ambil semua Mapping milik Guru ini
        // Kita load classroom dan subject agar bisa ditampilkan di dropdown
        $assignments = TeachingAssignment::with(['classroom', 'subject'])
                        ->where('teacher_id', $teacher->id)
                        ->get();

        // 2. Ambil daftar Kelas Unik dari mapping tersebut untuk dropdown pertama
        $classrooms = $assignments->pluck('classroom')->unique('id')->sortBy('name');

        // Kirim $assignments ke view untuk filter JS dinamis (Kelas -> Mapel)
        return view('schedule.create', compact('classrooms', 'assignments'));
    }

    /**
     * Simpan Jadwal ke Database
     */
    public function store(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'day'          => 'required',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        $teacher = $this->getTeacher();

        // Validasi Tambahan: Pastikan Guru benar-benar punya mapping di kelas & mapel ini
        $isValid = TeachingAssignment::where('teacher_id', $teacher->id)
                    ->where('classroom_id', $request->classroom_id)
                    ->where('subject_id', $request->subject_id)
                    ->exists();

        if (!$isValid) {
            return back()
                ->withErrors(['subject_id' => 'Anda tidak terdaftar mengajar mapel ini di kelas tersebut (Cek Mapping).'])
                ->withInput();
        }

        // Simpan Jadwal
        Schedule::create([
            'teacher_id'   => $teacher->id,
            'classroom_id' => $request->classroom_id,
            'subject_id'   => $request->subject_id,
            'day'          => $request->day,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
        ]);

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Dibuat!');
    }

    /**
     * Lihat Detail Absensi
     */
    public function show($id)
    {
        $teacher = $this->getTeacher();

        $schedule = Schedule::with(['classroom', 'subject'])
                    ->where('id', $id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();

        // Ambil Absensi Hari Ini (Mapel)
        $attendances = Attendance::with('student')
                        ->where('schedule_id', $id)
                        ->whereDate('created_at', Carbon::today())
                        ->latest()
                        ->get();

        return view('schedule.show', compact('schedule', 'attendances'));
    }

    /**
     * Hapus Jadwal
     */
    public function destroy($id)
    {
        $teacher = $this->getTeacher();
        
        // Pastikan hanya pemilik jadwal yang bisa menghapus
        $schedule = Schedule::where('id', $id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();
        
        $schedule->delete();

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Dihapus!');
    }
}