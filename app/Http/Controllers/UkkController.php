<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\User;
use App\Models\UkkPackage;
use App\Models\UkkAssessment;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UkkController extends Controller
{
    /**
     * Halaman Utama Pengelolaan & Penilaian UKK (Multi-Role & UUID-bound)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = UkkAssessment::with(['student.classroom', 'package', 'internalExaminer', 'externalExaminer']);

        // Filter Penilaian Berdasarkan Role Penguji
        if ($user->hasRole('penguji_internal') || $user->role === 'penguji_internal') {
            $query->where('internal_examiner_id', $user->id);
        } elseif ($user->hasRole('penguji_eksternal') || $user->role === 'penguji_eksternal') {
            $query->where('external_examiner_id', $user->id);
        }

        // Filter berdasarkan Kelas
        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        // Filter Pencarian (ILIKE PostgreSQL Case-Insensitive)
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
        $examiners = User::all();

        return view('ukk.index', compact('assessments', 'classrooms', 'packages', 'examiners'));
    }

    /**
     * Admin: Generate Jadwal UKK Otomatis untuk Kelas XII TITL / TPTUP
     */
    public function generateSchedule(Request $request)
    {
        $request->validate([
            'classroom_id'         => 'required',
            'ukk_package_id'       => 'required|exists:ukk_packages,id',
            'start_date'           => 'required|date',
            'quota_per_day'        => 'required|integer|min:1',
            'internal_examiner_id' => 'required|exists:users,id',
            'external_examiner_id' => 'required|exists:users,id',
        ]);

        $students = Student::where('classroom_id', $request->classroom_id)
            ->where(function ($q) {
                $q->where('status', '1')->orWhere('status', 'active')->orWhere('status', 'aktif');
            })
            ->orderBy('name')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa aktif ditemukan pada kelas ini.');
        }

        DB::beginTransaction();
        try {
            $currentDate = Carbon::parse($request->start_date);
            $count = 0;

            foreach ($students as $student) {
                // Lewati Hari Minggu
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
    public function printCertificate($studentId)
    {
        $assessment = UkkAssessment::with(['student.classroom', 'package.units', 'internalExaminer', 'externalExaminer'])
            ->where('student_id', $studentId)
            ->firstOrFail();

        $settings = [
            'school_name' => Setting::where('key', 'school_name')->value('value') ?? 'SMK NEGERI 1 BUKITTINGGI',
            'headmaster'  => Setting::where('key', 'headmaster_name')->value('value') ?? 'Gustian Budiarto, M.Pd',
            'nip'         => Setting::where('key', 'headmaster_nip')->value('value') ?? '19740731 200801 1 002',
        ];

        return view('ukk.certificate_print', compact('assessment', 'settings'));
    }
}