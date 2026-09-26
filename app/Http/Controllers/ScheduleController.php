<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\TeachingAssignment; // Model Mapping
use App\Models\Classroom; // Tambahan untuk Admin View
use App\Models\Subject;   // Tambahan untuk Admin View
use App\Models\Room;      // Tambahkan Import Model Room
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;


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

        // Ambil jadwal guru (tambahkan 'room' di eager loading)
        $schedules = Schedule::with(['classroom', 'subject', 'room'])
            // Hitung jumlah siswa yang sudah diabsen pada jadwal ini (HARI INI)
            ->withCount(['attendances' => function ($query) {
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

        return view('guru.schedule.index', compact('schedules'));
    }

    /**
     * Form Tambah Jadwal Baru (FILTERED BY MAPPING)
     */
    public function create()
    {
        $teacher = $this->getTeacher();
        if (!$teacher) abort(403, 'Akses khusus Guru');

        // 1. Ambil semua Mapping milik Guru ini
        $assignments = TeachingAssignment::with(['classroom', 'subject'])
                        ->where('teacher_id', $teacher->id)
                        ->get();

        // 2. Ambil daftar Kelas Unik dari mapping tersebut
        $classrooms = $assignments->pluck('classroom')->unique('id')->sortBy('name');

        // 3. Ambil daftar Ruangan (Semua Ruangan)
        $rooms = Room::orderBy('name')->get();

        return view('guru.schedule.create', compact('classrooms', 'assignments', 'rooms'));
    }

    /**
     * Simpan Jadwal ke Database dengan VALIDASI BENTROK
     */
    public function store(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'room_id'      => 'nullable|exists:rooms,id', // Validasi Room (Optional/Nullable)
            'day'          => 'required',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        $teacher = $this->getTeacher();

        // 1. Cek Validitas Mapping (Guru memang mengajar mapel ini di kelas ini)
        $isValidMapping = TeachingAssignment::where('teacher_id', $teacher->id)
                    ->where('classroom_id', $request->classroom_id)
                    ->where('subject_id', $request->subject_id)
                    ->exists();

        if (!$isValidMapping) {
            return back()
                ->withErrors(['subject_id' => 'Anda tidak terdaftar mengajar mapel ini di kelas tersebut (Cek Mapping).'])
                ->withInput();
        }

        // 2. Cek Bentrok Jadwal GURU
        if ($this->checkConflict($teacher->id, $request)) {
             return back()->withErrors(['start_time' => 'Jadwal bentrok! Anda sudah mengajar di kelas lain pada jam tersebut.'])->withInput();
        }

        // 3. Cek Bentrok Jadwal KELAS
        // (Logic ada di dalam checkConflict bagian bawah, tapi dipanggil terpisah jika ingin pesan spesifik)
        // Disini saya gabung di checkConflict agar lebih ringkas, atau bisa dipisah jika ingin pesan error beda.
        // checkConflict di bawah sudah mengcover keduanya.

        // Jika lolos semua validasi, Simpan
        Schedule::create([
            'teacher_id'   => $teacher->id,
            'classroom_id' => $request->classroom_id,
            'subject_id'   => $request->subject_id,
            'room_id'      => $request->room_id, // Simpan Room ID
            'day'          => $request->day,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
        ]);

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Dibuat!');
    }


    /**
     * Form Edit Jadwal
     */
    public function edit($id)
    {
        $teacher = $this->getTeacher();

        // Pastikan jadwal milik guru yang login
        $schedule = Schedule::where('id', $id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();

        // Ambil data mapping untuk dropdown
        $assignments = TeachingAssignment::with(['classroom', 'subject'])
                        ->where('teacher_id', $teacher->id)
                        ->get();

        $classrooms = $assignments->pluck('classroom')->unique('id')->sortBy('name');

        // Ambil data ruangan
        $rooms = Room::orderBy('name')->get();

        return view('guru.schedule.edit', compact('schedule', 'classrooms', 'assignments', 'rooms'));
    }

    /**
     * Update Jadwal
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'room_id'      => 'nullable|exists:rooms,id', // Validasi Room
            'day'          => 'required',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        $teacher = $this->getTeacher();
        $schedule = Schedule::where('id', $id)->where('teacher_id', $teacher->id)->firstOrFail();

        // 1. Cek Validitas Mapping
        $isValidMapping = TeachingAssignment::where('teacher_id', $teacher->id)
                    ->where('classroom_id', $request->classroom_id)
                    ->where('subject_id', $request->subject_id)
                    ->exists();

        if (!$isValidMapping) {
            return back()->withErrors(['subject_id' => 'Mapping tidak valid.'])->withInput();
        }

        // 2. Cek Bentrok (Kirim ID jadwal saat ini untuk dikecualikan)
        if ($this->checkConflict($teacher->id, $request, $id)) {
             return back()->withErrors(['start_time' => 'Jadwal bentrok dengan jadwal lain!'])->withInput();
        }

        $schedule->update([
            'classroom_id' => $request->classroom_id,
            'subject_id'   => $request->subject_id,
            'room_id'      => $request->room_id, // Update Room ID
            'day'          => $request->day,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
        ]);

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Diperbarui!');
    }

    /**
     * Lihat Detail Absensi
     */
    public function show($id)
    {
        $teacher = $this->getTeacher();

        $schedule = Schedule::with(['classroom', 'subject', 'room'])
                    ->where('id', $id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();

        // Ambil Absensi Hari Ini (Mapel)
        $attendances = Attendance::with('student')
                        ->where('schedule_id', $id)
                        ->whereDate('created_at', Carbon::today())
                        ->latest()
                        ->get();

        return view('guru.schedule.show', compact('schedule', 'attendances'));
    }

    /**
     * Hapus Jadwal
     */
    public function destroy($id)
    {
        $teacher = $this->getTeacher();

        $schedule = Schedule::where('id', $id)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();

        $schedule->delete();

        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Dihapus!');
    }


    // --- FITUR BARU: ADMIN JADWAL SEMUA GURU ---

    /**
     * Menampilkan Kalender Semua Guru
     */
    public function allSchedules(Request $request)
    {
        // 1. Ambil Semua Jadwal
        $query = Schedule::with(['teacher', 'classroom', 'subject', 'room']); // Load room juga

        // Filter by Teacher (Optional)
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $schedules = $query->get();

        // 2. Data untuk Modal Tambah Jadwal (Admin Mode)
        $teachers = Teacher::orderBy('name')->get();
        $allAssignments = TeachingAssignment::with(['classroom', 'subject'])->get();
        $rooms = Room::orderBy('name')->get(); // Data ruangan untuk admin

        return view('guru.schedule.all', compact('schedules', 'teachers', 'allAssignments', 'rooms'));
    }

    /**
     * Simpan Jadwal oleh Admin (Bisa pilih guru)
     */
    public function storeAsAdmin(Request $request)
    {
        $request->validate([
            'teacher_id'   => 'required|exists:teachers,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'room_id'      => 'nullable|exists:rooms,id', // Validasi Room
            'day'          => 'required',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        // Cek Bentrok
        if ($this->checkConflict($request->teacher_id, $request)) {
             return back()->withErrors(['start_time' => 'Jadwal bentrok! Guru atau Kelas sedang sibuk.'])->withInput();
        }

        Schedule::create($request->all());

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan oleh Admin!');
    }

     // Helper Cek Bentrok (Updated dengan ignore ID)
    private function checkConflict($teacherId, $request, $ignoreId = null) {
        // 1. Cek Bentrok Jadwal GURU
        $queryTeacher = Schedule::where('teacher_id', $teacherId)
            ->where('day', $request->day)
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time);

        if ($ignoreId) {
            $queryTeacher->where('id', '!=', $ignoreId);
        }

        $teacherConflict = $queryTeacher->exists();

        // 2. Cek Bentrok Jadwal KELAS
        $queryClass = Schedule::where('classroom_id', $request->classroom_id)
            ->where('day', $request->day)
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time);

        if ($ignoreId) {
            $queryClass->where('id', '!=', $ignoreId);
        }

        $classConflict = $queryClass->exists();

        // 3. (Opsional) Cek Bentrok RUANGAN jika room_id diisi
        // Jika ruangan tidak boleh dipakai 2 kelas sekaligus
        $roomConflict = false;
        if ($request->filled('room_id')) {
             $queryRoom = Schedule::where('room_id', $request->room_id)
                ->where('day', $request->day)
                ->where('start_time', '<', $request->end_time)
                ->where('end_time', '>', $request->start_time);

             if ($ignoreId) {
                $queryRoom->where('id', '!=', $ignoreId);
             }

             $roomConflict = $queryRoom->exists();
        }


        return $teacherConflict || $classConflict || $roomConflict;
    }

}
