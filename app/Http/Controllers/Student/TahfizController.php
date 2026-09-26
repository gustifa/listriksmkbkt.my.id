<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TahfizRecord;
use App\Models\Classroom;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TahfizController extends Controller
{
    /**
     * Menampilkan daftar rekapitulasi hafalan
     */
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        // Memuat relasi student dan teacher, urutkan berdasarkan tanggal terbaru
        $query = TahfizRecord::with(['student', 'teacher'])->latest('date');

        // Fitur Pencarian berdasarkan nama siswa (mencari di tabel students melalui relasi)
        if ($request->has('search') && $request->filled('search')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Jika user yang login adalah siswa, batasi tampilan hanya untuk data mereka sendiri
        if (Auth::user()->hasRole('siswa')) {
            // Mengambil ID student dari user yang sedang login
            $studentId = Auth::user()->student->id ?? null;

            if ($studentId) {
                $query->where('student_id', $studentId);
            } else {
                // Mencegah siswa yang belum terhubung ke tabel student melihat data lain
                $query->where('student_id', '00000000-0000-0000-0000-000000000000');
            }
        }

        // Paginasi dengan mempertahankan parameter query (misal saat mencari nama sambil pindah halaman)
        $records = $query->paginate(15)->appends($request->all());

        // Mengambil daftar siswa aktif untuk dropdown di form modal
        $students = Student::orderBy('name')->get();

            // Ambil data untuk dropdown
        $classes = Classroom::orderBy('name', 'asc')->get();

        // Mengambil daftar Surah Juz 30 dari Model
        $surahs = TahfizRecord::getJuz30Surahs();

        return view('siswa.tahfiz.index', compact('records', 'students', 'surahs', 'classes', 'today'));
    }

    // public function index(Request $request)
    // {
    //     $today = Carbon::today()->toDateString();

    //     // Ambil data untuk dropdown
    //     $classes = Classroom::orderBy('name', 'asc')->get();
    //     $students = Student::orderBy('name', 'asc')->get();

    //     $surahs = [
    //         'An-Naba', 'An-Naziat', 'Abasa', 'At-Takwir', 'Al-Infitar',
    //         'Al-Mutaffifin', 'Al-Inshiqaq', 'Al-Buruj', 'At-Tariq', 'Al-A\'la',
    //         'Al-Ghashiyah', 'Al-Fajr', 'Al-Balad', 'Ash-Shams', 'Al-Lail',
    //         'Ad-Duha', 'Ash-Sharh', 'At-Tin', 'Al-\'Alaq', 'Al-Qadr',
    //         'Al-Bayyinah', 'Az-Zalzalah', 'Al-\'Adiyat', 'Al-Qari\'ah', 'At-Takathur',
    //         'Al-\'Asr', 'Al-Humazah', 'Al-Fil', 'Quraish', 'Al-Ma\'un',
    //         'Al-Kauthar', 'Al-Kafirun', 'An-Nasr', 'Al-Masad', 'Al-Ikhlas',
    //         'Al-Falaq', 'An-Nas'
    //     ];

    //     // Query dengan Eager Loading student
    //     $query = TahfizRecord::with(['student', 'teacher']);

    //     // Filter Nama (Case-Insensitive PostgreSQL)
    //     if ($request->filled('search')) {
    //         $query->whereHas('student', function($q) use ($request) {
    //             $searchTerm = strtolower($request->search);
    //             $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);
    //         });
    //     }

    //     // Filter Kelas (Cari user_id/id di tabel students yang memiliki classroom_id tersebut)
    //     if ($request->filled('kelas_id')) {
    //         $query->whereIn('student_id', function($q) use ($request) {
    //             $q->select('id') // Gunakan 'id' karena foreign key Bapak merujuk ke id tabel students
    //               ->from('students')
    //               ->where('classroom_id', $request->kelas_id);
    //         });
    //     }

    //     $records = $query->latest('date')->paginate(12)->withQueryString();

    //     return view('siswa.tahfiz.index', compact('records', 'classes', 'students', 'surahs', 'today'));
    // }

    /**
     * Menyimpan data hafalan baru
     */
    // public function store(Request $request)
    // {
    //     // Validasi input form
    //     $request->validate([
    //         'student_id' => 'required|uuid|exists:students,id',
    //         'surah_name' => 'required|string',
    //         'ayat'       => 'nullable|string',
    //         'predicate'  => 'required|string',
    //         'date'       => 'required|date',
    //         'notes'      => 'nullable|string'
    //     ], [
    //         'student_id.exists' => 'Siswa tidak ditemukan di database.',
    //         'student_id.uuid'   => 'Format ID Siswa tidak valid.'
    //     ]);

    //     // Menyimpan data ke database
    //     TahfizRecord::create([
    //         'student_id' => $request->student_id,
    //         'teacher_id' => Auth::id(), // ID Guru (User yang sedang login)
    //         'surah_name' => $request->surah_name,
    //         'ayat'       => $request->ayat ?? 'Lengkap',
    //         'predicate'  => $request->predicate,
    //         'date'       => $request->date,
    //         'notes'      => $request->notes,
    //     ]);

    //     return redirect()->route('tahfiz.index')
    //         ->with('success', 'Setoran hafalan berhasil dicatat!');
    // }
//     public function store(Request $request)
// {
//     $request->validate([
//         // Pastikan student_id ada di tabel students kolom id
//         'student_id'   => 'required|exists:students,id',
//         'date'         => 'required|date',
//         // Validasi surah_name sebagai array (karena pakai multi-select)
//         'surah_name'   => 'required|array',
//         'surah_name.*' => 'string',
//         'predicate'    => 'required|string',
//     ], [
//         // Pesan error kustom (opsional)
//         'student_id.exists' => 'Siswa tidak ditemukan di database.',
//         'surah_name.array'  => 'Pilih minimal satu surah.',
//     ]);

//     try {
//         // Gabungkan array menjadi string untuk disimpan ke database
//         $surahString = implode(', ', $request->surah_name);

//         TahfizRecord::create([
//             'student_id' => $request->student_id,
//             'teacher_id' => auth()->id(),
//             'date'       => $request->date,
//             'surah_name' => $surahString,
//             'ayat'       => $request->ayat ?? 'Lengkap',
//             'predicate'  => $request->predicate,
//             'notes'      => $request->notes,
//         ]);

//         return redirect()->back()->with('success', 'Setoran berhasil dicatat!');
//     } catch (\Exception $e) {
//         return redirect()->back()->withErrors(['error' => 'Gagal simpan: ' . $e->getMessage()]);
//     }
// }

public function store(Request $request)
    {
        $request->validate([
            'student_id'   => 'required|exists:students,id',
            'date'         => 'required|date',
            'surah_name'   => 'required|array',
            'predicate'    => 'required|string',
        ]);

        try {
            $surahString = implode(', ', $request->surah_name);

            TahfizRecord::create([
                'student_id' => $request->student_id,
                'teacher_id' => Auth::id(),
                'date'       => $request->date,
                'surah_name' => $surahString,
                'ayat'       => $request->ayat ?? 'Lengkap',
                'predicate'  => $request->predicate,
                'notes'      => $request->notes,
            ]);

            return redirect()->back()->with('success', 'Setoran hafalan berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    /**
     * Memperbarui data hafalan yang sudah ada (Opsional jika form edit digunakan)
     */
    public function update(Request $request, $id)
    {
        $record = TahfizRecord::findOrFail($id);

        $request->validate([
            'student_id' => 'required|uuid|exists:students,id',
            'surah_name' => 'required|string',
            'ayat'       => 'nullable|string',
            'predicate'  => 'required|string',
            'date'       => 'required|date',
            'notes'      => 'nullable|string'
        ]);

        $record->update([
            'student_id' => $request->student_id,
            'surah_name' => $request->surah_name,
            'ayat'       => $request->ayat ?? 'Lengkap',
            'predicate'  => $request->predicate,
            'date'       => $request->date,
            'notes'      => $request->notes,
            // teacher_id tidak diubah agar tetap mencatat guru pertama yang menyimak
            // atau bisa diubah sesuai kebutuhan bisnis logic Anda
        ]);

        return redirect()->route('tahfiz.index')
            ->with('success', 'Data hafalan berhasil diperbarui!');
    }

    /**
     * Menghapus data hafalan
     */
    public function destroy($id)
    {
        $record = TahfizRecord::findOrFail($id);
        $record->delete();

        return redirect()->route('tahfiz.index')
            ->with('success', 'Data hafalan berhasil dihapus!');
    }
}
