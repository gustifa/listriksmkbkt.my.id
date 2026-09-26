<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Classroom;
use App\Models\Subject; // <--- Pastikan Model Subject di-import
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class ScheduleController extends Controller
{
    /**
     * Menampilkan Daftar Jadwal Guru
     */
    public function index()
    {
       // Ambil jadwal milik guru yang sedang login
        $schedules = Schedule::with(['classroom', 'subject'])
                        // Hitung jumlah kehadiran HANYA untuk tanggal HARI INI
                        ->withCount(['attendances' => function ($query) {
                            $query->where('date', date('Y-m-d'));
                        }])
                        ->where('teacher_id', Auth::id())
                        ->get();



        $daysOrder = [
            'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3,
            'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7
        ];


        $schedules = $schedules->sortBy(function ($schedule) use ($daysOrder) {
            return $daysOrder[$schedule->day] ?? 99;
        })->sortBy('start_time');


        return view('schedule.index', compact('schedules'));
    }


    /**
     * Form Tambah Jadwal Baru (UPDATED)
     * Mengambil data Kelas dan Mata Pelajaran untuk Dropdown
     */
    public function create()
    {
        // Ambil daftar kelas urut nama
        $classrooms = Classroom::orderBy('name')->get();

        // Ambil daftar mata pelajaran urut nama
        $subjects = Subject::orderBy('name')->get();

        return view('schedule.create', compact('classrooms', 'subjects'));
    }


    /**
     * Simpan Jadwal ke Database
     */
    public function store(Request $request)
    {
        $request->validate([
            // 'subject_name' => 'required|string|max:255', // Kita simpan Nama Mapelnya
            'subject_id'   => 'required|exists:subjects,id', // Kita simpan Nama Mapelnya
            'classroom_id' => 'required|exists:classrooms,id',
            'day'          => 'required',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);


        Schedule::create([
            'teacher_id'   => Auth::id(),
            'classroom_id' => $request->classroom_id,
            'subject_id' => $request->subject_id, // Simpan nama dari dropdown
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
        // 1. Cari Jadwal & Validasi Pemilik
        $schedule = Schedule::with('classroom', 'subject')
                    ->where('id', $id)
                    ->where('teacher_id', Auth::id())
                    ->firstOrFail();

        // 2. Ambil Data Absensi HARI INI untuk jadwal tersebut
        $attendances = Attendance::with('student')
                        ->where('schedule_id', $id)
                        ->where('date', date('Y-m-d'))
                        ->orderBy('check_in_time', 'desc') // Yang baru absen ada di paling atas
                        ->get();


        return view('schedule.show', compact('schedule', 'attendances'));
    }


    /**
     * Hapus Jadwal
     */
    public function destroy($id)
    {
        $schedule = Schedule::where('id', $id)->where('teacher_id', Auth::id())->firstOrFail();
        
        // Hapus jadwal (Data absensi lama akan ikut terhapus jika onCascadeDelete aktif di database)
        $schedule->delete();


        return redirect()->route('schedule.index')->with('success', 'Jadwal Berhasil Dihapus!');
    }
}
