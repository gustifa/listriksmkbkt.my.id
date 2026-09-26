<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\RamadanJournal;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RamadanJournalStudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        $today = Carbon::today();

        // Cek apakah sudah mengisi hari ini
        $todayEntry = RamadanJournal::where('student_id', $student->id)
            ->where('date', $today)
            ->first();

        // Ambil History
        $histories = RamadanJournal::where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->get();

        // Statistik Sederhana
        $stats = [
            'total_fasting' => $histories->where('fasting_status', 'full')->count(),
            'total_tarawih' => $histories->where('prayer_tarawih', true)->count(),
            'total_quran'   => $histories->where('read_quran', true)->count(),
        ];

        return view('siswa.ramadan.index', compact('student', 'todayEntry', 'histories', 'stats'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        $date = Carbon::today();

        // Validasi dasar
        $request->validate([
            'fasting_status' => 'required|in:full,half,none',
            'notes' => 'nullable|string|max:500'
        ]);

        // Cek duplikasi
        if (RamadanJournal::where('student_id', $student->id)->where('date', $date)->exists()) {
            return redirect()->back()->with('error', 'Anda sudah mengisi jurnal hari ini.');
        }

        RamadanJournal::create([
            'student_id' => $student->id,
            'date' => $date,
            'fasting_status' => $request->fasting_status,
            
            // Checkbox value (jika dicentang = 1, tidak = 0)
            'prayer_subuh' => $request->has('prayer_subuh'),
            'prayer_dzuhur' => $request->has('prayer_dzuhur'),
            'prayer_ashar' => $request->has('prayer_ashar'),
            'prayer_maghrib' => $request->has('prayer_maghrib'),
            'prayer_isya' => $request->has('prayer_isya'),
            
            'prayer_tarawih' => $request->has('prayer_tarawih'),
            'prayer_witir' => $request->has('prayer_witir'),
            'prayer_dhuha' => $request->has('prayer_dhuha'),
            'prayer_tahajud' => $request->has('prayer_tahajud'),
            
            'read_quran' => $request->has('read_quran'),
            'surah_name' => $request->surah_name,
            'ayat_range' => $request->ayat_range,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Alhamdulillah, Jurnal Ramadhan hari ini berhasil disimpan.');
    }
}
