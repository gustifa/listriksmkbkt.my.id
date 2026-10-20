<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Teacher; // 1. Pastikan Model Teacher di-import
use App\Models\UkkPackage;
use App\Models\UkkAssessment;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ExternalExaminer; // Pastikan Model di-import di atas

class UkkController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = UkkAssessment::with(['student.classroom', 'package', 'internalExaminer', 'externalExaminer']);

        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('nis', 'ILIKE', "%{$search}%");
            });
        }

        $assessments = $query->orderBy('exam_date', 'asc')->paginate(15);
        $classrooms = Classroom::where('name', 'ILIKE', '%XII%')->orderBy('name')->get();
        $packages = UkkPackage::orderBy('package_name')->get();

        // 2. AMBIL DATA PENGUJI INTERNAL DARI TABEL TEACHERS
        $internalExaminers = Teacher::orderBy('name', 'asc')->get();

        // 3. AMBIL DATA PENGUJI EKSTERNAL DARI TABEL EXTERNAL_EXAMINERS (DUDI)
        $externalExaminers = ExternalExaminer::orderBy('company_name', 'asc')->get();

        return view('ukk.index', compact('assessments', 'classrooms', 'packages', 'internalExaminers', 'externalExaminers'));
    }

    public function generateSchedule(Request $request)
    {
        // 4. VALIDASI internal_examiner_id KE TABEL teachers
        $request->validate([
            'classroom_id'         => 'required|uuid|exists:classrooms,id',
            'ukk_package_id'       => 'required|uuid|exists:ukk_packages,id',
            'start_date'           => 'required|date',
            'quota_per_day'        => 'required|numeric|min:1',
            'internal_examiner_id' => 'required|uuid|exists:teachers,id', // <-- Validasi ke tabel teachers
            'external_examiner_id' => 'required|uuid|exists:external_examiners,id', // <-- Validasi ke tabel external_examiners
        ]);

        $students = Student::where('classroom_id', $request->classroom_id)
            ->where(function ($q) {
                $q->whereIn('status', ['1', 'active', 'aktif', 1, true])
                  ->orWhereNull('status');
            })
            ->orderBy('name')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa aktif ditemukan pada kelas yang dipilih.');
        }

        DB::beginTransaction();
        try {
            $currentDate = Carbon::parse($request->start_date);
            $count = 0;

            foreach ($students as $student) {
                while ($currentDate->isSunday()) {
                    $currentDate->addDay();
                }

                UkkAssessment::updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'ukk_package_id'       => $request->ukk_package_id,
                        'exam_date'           => $currentDate->toDateString(),
                        'internal_examiner_id' => $request->internal_examiner_id,
                        'external_examiner_id' => $request->external_examiner_id,
                        'status'               => 'Belum Diuji',
                    ]
                );

                $count++;
                if ($count % $request->quota_per_day == 0) {
                    $currentDate->addDay();
                }
            }

            DB::commit();
            return back()->with('success', "Jadwal UKK berhasil dibuat otomatis untuk {$students->count()} siswa.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat jadwal UKK: ' . $e->getMessage());
        }
    }
    /**
     * Halaman Utama Pengelolaan & Penilaian UKK (Multi-Role & UUID-bound)
     */
    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     $query = UkkAssessment::with(['student.classroom', 'package', 'internalExaminer', 'externalExaminer']);

    //     // Filter Penilaian Berdasarkan Role Penguji
    //     if ($user->hasRole('penguji_internal') || $user->role === 'penguji_internal') {
    //         $query->where('internal_examiner_id', $user->id);
    //     } elseif ($user->hasRole('penguji_eksternal') || $user->role === 'penguji_eksternal') {
    //         $query->where('external_examiner_id', $user->id);
    //     }

    //     // Filter berdasarkan Kelas
    //     if ($request->filled('classroom_id')) {
    //         $query->whereHas('student', function ($q) use ($request) {
    //             $q->where('classroom_id', $request->classroom_id);
    //         });
    //     }

    //     // Filter Pencarian (ILIKE PostgreSQL Case-Insensitive)
    //     if ($request->filled('search')) {
    //         $search = strtolower($request->search);
    //         $query->whereHas('student', function ($q) use ($search) {
    //             $q->where('name', 'ILIKE', "%{$search}%")
    //               ->orWhere('nis', 'ILIKE', "%{$search}%");
    //         });
    //     }

    //     $assessments = $query->orderBy('exam_date', 'asc')->paginate(15);
    //     $classrooms = Classroom::where('name', 'ILIKE', '%XII%')->orderBy('name')->get();
    //     $packages = UkkPackage::orderBy('package_name')->get();
    //     $examiners = User::all();

    //     // AMBIL DATA PENGUJI EKSTERNAL DARI TABEL MASTER DUDI
    //     $externalExaminers = ExternalExaminer::orderBy('company_name', 'asc')->get();

    //     return view('ukk.index', compact('assessments', 'classrooms', 'packages', 'examiners', 'externalExaminers'));
    // }

    /**
     * Admin: Generate Jadwal UKK Otomatis untuk Kelas XII TITL / TPTUP
     */
    // public function generateSchedule(Request $request)
    // {
    //     // 1. Validasi Input Form
    //     $request->validate([
    //         'classroom_id'         => 'required|exists:classrooms,id',
    //         'ukk_package_id'       => 'required|exists:ukk_packages,id',
    //         'start_date'           => 'required|date',
    //         'quota_per_day'        => 'required|numeric|min:1',
    //         'internal_examiner_id' => 'required|exists:users,id',
    //         'external_examiner_id' => 'required|exists:external_examiners,id',
    //     ]);

    //     // 2. Ambil Data Siswa Berdasarkan Kelas (Fleksibel Terhadap Status Siswa)
    //     $students = Student::where('classroom_id', $request->classroom_id)
    //         ->where(function ($q) {
    //             $q->whereIn('status', ['1', 'active', 'aktif', 1, true])
    //               ->orWhereNull('status'); // Mengantisipasi jika status null/kosong
    //         })
    //         ->orderBy('name')
    //         ->get();

    //     if ($students->isEmpty()) {
    //         return back()->with('error', 'Tidak ada siswa aktif ditemukan pada kelas yang dipilih.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $currentDate = Carbon::parse($request->start_date);
    //         $count = 0;

    //         foreach ($students as $student) {
    //             // Lewati Hari Minggu
    //             while ($currentDate->isSunday()) {
    //                 $currentDate->addDay();
    //             }

    //             UkkAssessment::updateOrCreate(
    //                 ['student_id' => $student->id],
    //                 [
    //                     'ukk_package_id'       => $request->ukk_package_id,
    //                     'exam_date'           => $currentDate->toDateString(),
    //                     'internal_examiner_id' => $request->internal_examiner_id,
    //                     'external_examiner_id' => $request->external_examiner_id,
    //                     'status'               => 'Belum Diuji',
    //                 ]
    //             );

    //             $count++;
    //             if ($count % $request->quota_per_day == 0) {
    //                 $currentDate->addDay();
    //             }
    //         }

    //         DB::commit();
    //         return back()->with('success', "Jadwal UKK berhasil dibuat otomatis untuk {$students->count()} siswa.");

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Gagal membuat jadwal UKK: ' . $e->getMessage());
    //     }
    // }

    /**
 * Admin: Generate Jadwal UKK Otomatis untuk Kelas XII
 */
// public function generateSchedule(Request $request)
// {
//     // 1. Validasi Input Form (Mendukung UUID & Tabel Master DUDI Baru)
//     $request->validate([
//         'classroom_id'         => 'required|exists:classrooms,id',
//         'ukk_package_id'       => 'required|exists:ukk_packages,id',
//         'start_date'           => 'required|date',
//         'quota_per_day'        => 'required|numeric|min:1',
//         'internal_examiner_id' => 'required|exists:users,id',
//         'external_examiner_id' => 'required|exists:external_examiners,id',
//     ]);

//     // 2. Ambil Data Siswa Berdasarkan Kelas (Mendukung variasi nilai status / NULL)
//     $students = Student::where('classroom_id', $request->classroom_id)
//         ->where(function ($q) {
//             $q->whereIn('status', ['1', 'active', 'aktif', 1, true])
//               ->orWhereNull('status');
//         })
//         ->orderBy('name')
//         ->get();

//     if ($students->isEmpty()) {
//         return back()->with('error', 'Tidak ada siswa aktif ditemukan pada kelas yang dipilih.');
//     }

//     DB::beginTransaction();
//     try {
//         $currentDate = Carbon::parse($request->start_date);
//         $count = 0;

//         foreach ($students as $student) {
//             // Lewati Hari Minggu
//             while ($currentDate->isSunday()) {
//                 $currentDate->addDay();
//             }

//             UkkAssessment::updateOrCreate(
//                 ['student_id' => $student->id],
//                 [
//                     'ukk_package_id'       => $request->ukk_package_id,
//                     'exam_date'           => $currentDate->toDateString(),
//                     'internal_examiner_id' => $request->internal_examiner_id,
//                     'external_examiner_id' => $request->external_examiner_id, // Menyimpan UUID dari external_examiners
//                     'status'               => 'Belum Diuji',
//                 ]
//             );

//             $count++;
//             if ($count % $request->quota_per_day == 0) {
//                 $currentDate->addDay();
//             }
//         }

//         DB::commit();
//         return back()->with('success', "Jadwal UKK berhasil dibuat otomatis untuk {$students->count()} siswa.");

//     } catch (\Exception $e) {
//         DB::rollBack();
//         return back()->with('error', 'Gagal membuat jadwal UKK: ' . $e->getMessage());
//     }
// }

    /**
     * Input / Update Nilai Ujian oleh Penguji (Internal & Eksternal)
     */
    public function storeScore(Request $request, $id)
    {
        $assessment = UkkAssessment::findOrFail($id);
        $user = Auth::user();

        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        // Cek Porsi Nilai Penguji
        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';
        $isInternal = ($user->id == $assessment->internal_examiner_id) || $isAdmin;
        $isExternal = ($user->id == $assessment->external_examiner_id) || $isAdmin;

        if ($isInternal) {
            $assessment->score_internal = $request->score;
        }
        if ($isExternal) {
            $assessment->score_external = $request->score;
        }

        // Hitung Rata-Rata Nilai Akhir
        $scoreInt = $assessment->score_internal ?? $request->score;
        $scoreExt = $assessment->score_external ?? $request->score;
        $finalScore = round(($scoreInt + $scoreExt) / 2, 2);

        $assessment->final_score = $finalScore;
        $assessment->status = $finalScore >= 75 ? 'Kompeten' : 'Belum Kompeten';

        if ($request->filled('notes')) {
            $assessment->notes = $request->notes;
        }

        $assessment->save();

        return back()->with('success', 'Nilai Uji Kompetensi Keahlian berhasil diperbarui.');
    }

    /**
     * Cetak Sertifikat UKK (Menampilkan Judul Paket, Kode Unit, Judul Unit, dan Elemen)
     */
    // public function printCertificate($studentId)
    // {
    //     $assessment = UkkAssessment::with(['student.classroom', 'package.units', 'internalExaminer', 'externalExaminer'])
    //         ->where('student_id', $studentId)
    //         ->firstOrFail();

    //     $settings = [
    //         'school_name' => Setting::where('key', 'school_name')->value('value') ?? 'SMK NEGERI 1 BUKITTINGGI',
    //         'headmaster'  => Setting::where('key', 'headmaster_name')->value('value') ?? 'Gustian Budiarto, M.Pd',
    //         'nip'         => Setting::where('key', 'headmaster_nip')->value('value') ?? '19740731 200801 1 002',
    //     ];

    //     return view('ukk.certificate_print', compact('assessment', 'settings'));
    // }

    // public function printCertificate($studentId)
    // {
    //     $assessment = UkkAssessment::with(['student.classroom', 'package.units', 'internalExaminer', 'externalExaminer'])
    //         ->where('student_id', $studentId)
    //         ->firstOrFail();

    //     // Buat nomor urut pad 3 digit (misal: 001, 002, dst)
    //     $certNumber = str_pad($assessment->id, 3, '0', STR_PAD_LEFT);

    //     $settings = [
    //         'school_name' => Setting::where('key', 'school_name')->value('value') ?? 'SMK NEGERI 1 BUKITTINGGI',
    //         'headmaster'  => Setting::where('key', 'headmaster_name')->value('value') ?? 'Gustian Budiarto, M.Pd',
    //         'nip'         => Setting::where('key', 'headmaster_nip')->value('value') ?? '19740731 200801 1 002',
    //     ];

    //     return view('ukk.certificate_print', compact('assessment', 'settings', 'certNumber'));
    // }

    // public function printCertificate($studentId)
    // {
    //     $assessment = UkkAssessment::with(['student.classroom', 'package.units', 'internalExaminer', 'externalExaminer'])
    //         ->where('student_id', $studentId)
    //         ->firstOrFail();

    //     // Generate Nomor Urut Rapi (misal berdasarkan urutan NIS / urutan database)
    //     // Hindari mencetak UUID langsung sebagai nomor sertifikat
    //     $indexNumber = UkkAssessment::where('created_at', '<=', $assessment->created_at)->count();
    //     $certNumber = str_pad($indexNumber, 3, '0', STR_PAD_LEFT);

    //     // Ambil logo DUDI dinamis dari relasi UUID ExternalExaminer
    //     $externalLogo = $assessment->externalExaminer->logo_url ?? asset('images/logo-dudi.png');

    //     $settings = [
    //         'school_name' => Setting::where('key', 'school_name')->value('value') ?? 'SMK NEGERI 1 BUKITTINGGI',
    //         'headmaster'  => Setting::where('key', 'headmaster_name')->value('value') ?? 'Gustian Budiarto, M.Pd',
    //         'nip'         => Setting::where('key', 'headmaster_nip')->value('value') ?? '19740731 200801 1 002',
    //     ];

    //     return view('ukk.certificate_print', compact('assessment', 'settings', 'certNumber', 'externalLogo'));
    // }

    public function printCertificate($studentId)
{
    $assessment = UkkAssessment::with(['student.classroom', 'package', 'internalExaminer', 'externalExaminer'])
        ->where('student_id', $studentId)
        ->firstOrFail();

    // Hitung nomor urut sertifikat (1, 2, 3, dst.) lalu format 3 digit (001, 002, dst.)
    $indexNumber = UkkAssessment::where('created_at', '<=', $assessment->created_at)->count();
    $certNumber = str_pad($indexNumber, 3, '0', STR_PAD_LEFT);

    $settings = [
        'school_code' => Setting::where('key', 'school_code')->value('value') ?? 'SMKN1BKT',
        'school_name' => Setting::where('key', 'school_name')->value('value') ?? 'SMK NEGERI 1 BUKITTINGGI',
        'headmaster'  => Setting::where('key', 'headmaster_name')->value('value') ?? 'Gustian Budiarto, M.Pd',
        'nip'         => Setting::where('key', 'headmaster_nip')->value('value') ?? '19740731 200801 1 002',
    ];

    return view('ukk.certificate_print', compact('assessment', 'settings', 'certNumber'));
}

//     public function printCertificate($studentId)
// {
//     // Eager load relasi externalExaminer
//     $assessment = UkkAssessment::with(['student.classroom', 'package.units', 'internalExaminer', 'externalExaminer'])
//         ->where('student_id', $studentId)
//         ->firstOrFail();

//     // Nomor urut rapi sertifikat
//     $indexNumber = UkkAssessment::where('created_at', '<=', $assessment->created_at)->count();
//     $certNumber = str_pad($indexNumber, 3, '0', STR_PAD_LEFT);

//     // Ambil path / URL logo DUDI dari relasi UUID externalExaminer
//     $externalLogo = null;
//     if ($assessment->externalExaminer && $assessment->externalExaminer->logo) {
//         // Jika logo disimpan di public storage (misal: storage/app/public/logos/...)
//         $externalLogo = asset('storage/' . $assessment->externalExaminer->logo);
//     } else {
//         // Fallback logo default jika tidak ada logo diunggah
//         $externalLogo = asset('images/logo-dudi-default.png');
//     }

//     $settings = [
//         'school_name' => Setting::where('key', 'school_name')->value('value') ?? 'SMK NEGERI 1 BUKITTINGGI',
//         'headmaster'  => Setting::where('key', 'headmaster_name')->value('value') ?? 'Gustian Budiarto, M.Pd',
//         'nip'         => Setting::where('key', 'headmaster_nip')->value('value') ?? '19740731 200801 1 002',
//     ];

//     return view('ukk.certificate_print', compact('assessment', 'settings', 'certNumber', 'externalLogo'));
// }

    public function assignExaminers(Request $request)
    {
        $request->validate([
            'student_ids'          => 'required|array|min:1',
            'student_ids.*'        => 'uuid|exists:students,id',
            'internal_examiner_id' => 'required|uuid|exists:teachers,id',
            'external_examiner_id' => 'required|uuid|exists:external_examiners,id',
        ]);

        DB::beginTransaction();
        try {
            // Update penguji untuk setiap siswa yang dicentang
            foreach ($request->student_ids as $studentId) {
                UkkAssessment::updateOrCreate(
                    ['student_id' => $studentId],
                    [
                        'internal_examiner_id' => $request->internal_examiner_id,
                        'external_examiner_id' => $request->external_examiner_id,
                    ]
                );
            }

            DB::commit();
            return back()->with('success', count($request->student_ids) . ' siswa berhasil diatur pengujinya.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengatur penguji: ' . $e->getMessage());
        }
    }
}