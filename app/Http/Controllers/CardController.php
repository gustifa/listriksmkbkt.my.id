<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Setting;
use App\Models\Teacher;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class CardController extends Controller
{
    /**
     * Halaman Pemilihan Kelas (Dashboard Cetak)
     */
    public function index()
    {
        $classrooms = Classroom::withCount('students')->orderBy('name')->get();
        return view('print.select_class', compact('classrooms'));
    }

    /**
     * Cetak Kartu Berdasarkan Kelas Spesifik
     */
    public function printByClass($id)
    {
        // Cek apakah kelas ada
        $classroom = Classroom::find($id);
        if (!$classroom) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        $settings = Setting::pluck('value', 'key')->toArray();

        // Ambil siswa
        $students = Student::where('classroom_id', $id)->orderBy('name')->get();

        // VALIDASI: Cek jika kelas kosong
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak dapat mencetak. Kelas ini belum memiliki data siswa.');
        }

        return view('print.all_cards', compact('students', 'classroom', 'settings'));
    }

    /**
     * Cetak Semua Kartu (Massal Satu Sekolah)
     */
    public function printAll()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $students = Student::with('classroom')->orderBy('classroom_id')->orderBy('name')->get();

        // VALIDASI: Cek jika database kosong
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Database siswa masih kosong. Belum ada data untuk dicetak.');
        }

        return view('print.all_cards', compact('students', 'settings'));
    }

    /**
     * Cetak Satu Kartu Saja (Perorangan)
     */
    public function printSingle($id)
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        // Gunakan find (bukan fail) agar kita bisa redirect cantik dengan SweetAlert
        $student = Student::with('classroom')->find($id);

        if (!$student) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $qrcode = QrCode::size(120)->generate($student->nis);

        return view('print.single_card', compact('student', 'qrcode', 'settings'));
    }

    /**
     * [BARU] Halaman Pilih Siswa (Checkbox)
     */
    public function selectStudents($id)
    {
        $classroom = Classroom::find($id);

        if (!$classroom) {
            return redirect()->route('cards.index')->with('error', 'Kelas tidak valid.');
        }

        $settings = Setting::pluck('value', 'key')->toArray();
        $students = Student::where('classroom_id', $id)->orderBy('name')->get();

        if ($students->isEmpty()) {
            return redirect()->route('cards.index')->with('error', 'Kelas ini kosong, tidak ada siswa untuk dipilih.');
        }

        return view('print.select_students', compact('classroom', 'students', 'settings'));
    }

    /**
     * [BARU] Proses Cetak Siswa Terpilih
     */
    public function printSelected(Request $request)
    {
        // VALIDASI INPUT
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ], [
            // Custom Error Message Bahasa Indonesia
            'student_ids.required' => 'Anda belum memilih siswa satupun.',
            'student_ids.exists'   => 'Data siswa yang dipilih tidak valid.'
        ]);

        $settings = Setting::pluck('value', 'key')->toArray();

        $students = Student::with('classroom')
                    ->whereIn('id', $request->student_ids)
                    ->orderBy('name')
                    ->get();

        // Validasi double check (jaga-jaga)
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Gagal memuat data siswa yang dipilih.');
        }

        $classroom = $students->first()->classroom;

        return view('print.all_cards', compact('students', 'classroom', 'settings'));
    }

    /**
     * Halaman Utama Pilih Filter Cetak Kartu
     */
    public function card()
    {
        $classrooms = Classroom::orderBy('name')->get();
        return view('cards.index', compact('classrooms'));
    }

    /**
     * Proses Cetak Kartu (PDF)
     */
    /**
     * Proses Cetak Kartu (PDF)
     */
    // public function print(Request $request)
    // {
    //     $request->validate([
    //         'type' => 'required|in:student,teacher',
    //         'title_1' => 'required|string|max:50', // Baris 1: KARTU PESERTA
    //         'title_2' => 'required|string|max:50', // Baris 2: UJIAN SEMESTER GANJIL
    //     ]);

    //     $users = collect();
    //     $type = $request->type;

    //     // 1. Ambil Data Berdasarkan Tipe
    //     if ($type == 'student') {
    //         $query = Student::with('classroom')->orderBy('name');

    //         if ($request->filled('classroom_id')) {
    //             $query->where('classroom_id', $request->classroom_id);
    //         }

    //         $users = $query->get();
    //     } else {
    //         // Data Guru
    //         $users = Teacher::with('user')->get()->sortBy(function($t) {
    //             return $t->user->name;
    //         });
    //     }

    //     if ($users->isEmpty()) {
    //         return redirect()->back()->with('error', 'Data tidak ditemukan untuk kriteria tersebut.');
    //     }

    //     // 2. Data Sekolah & Kop Surat
    //     $school = $this->getSchoolData();

    //     // 3. Info Kartu
    //     $cardInfo = [
    //         'title_1' => strtoupper($request->title_1),
    //         'title_2' => strtoupper($request->title_2),
    //         'date'    => $request->date ?? date('d-m-Y'),
    //         'type'    => $type
    //     ];

    //     // 4. Generate PDF
    //     $pdf = Pdf::loadView('pdf.cards', compact('users', 'school', 'cardInfo'));

    //     // Set kertas A4 Portrait
    //     $pdf->setPaper('a4', 'portrait');
    //     $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

    //     return $pdf->stream('Kartu_' . $type . '_' . time() . '.pdf');
    // }

     /**
     * Proses Cetak Kartu (PDF)
     */
   /**
     * Proses Cetak Kartu (PDF)
     */
    public function print(Request $request)
    {
        $request->validate([
            'type' => 'required|in:student,teacher',
            'title_1' => 'required|string|max:50',
            'title_2' => 'required|string|max:50',
        ]);

        $users = collect();
        $type = $request->type;

        // 1. Ambil Data
        if ($type == 'student') {
            $query = Student::with('classroom')->orderBy('name');
            if ($request->filled('classroom_id')) {
                $query->where('classroom_id', $request->classroom_id);
            }
            $users = $query->get();
        } else {
            $users = Teacher::with('user')->get()->sortBy(function($t) {
                return $t->user->name;
            });
        }

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $school = $this->getSchoolData();

        $cardInfo = [
            'title_1' => strtoupper($request->title_1),
            'title_2' => strtoupper($request->title_2),
            'date'    => $request->date ?? date('d-m-Y'),
            'type'    => $type
        ];

        // 4. CHUNKING (PAGINASI MANUAL)
        // Kita set 10 kartu per halaman (2 kolom x 5 baris)
        $chunks = $users->chunk(10);

        // 5. Generate PDF
        $pdf = Pdf::loadView('pdf.cards', compact('chunks', 'school', 'cardInfo'));

        // Set kertas A4 Portrait (Tegak) agar muat 2 kolom
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true, 'chroot' => public_path()]);

        return $pdf->stream('Kartu_' . $type . '_' . time() . '.pdf');
    }

    // --- HELPER IMAGE TO BASE64 (Agar Logo Tampil Aman) ---
    private function imageToBase64($path) {
        if (!$path || $path == '-') return null;
        $cleanPath = str_replace('storage/', '', $path);

        $pathsToCheck = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($path)
        ];

        foreach ($pathsToCheck as $fullPath) {
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        return null;
    }

    private function getSchoolData()
    {
        return [
            'school_name'    => Setting::value('school_name', 'SMK DEFAULT'),
            'school_address' => Setting::value('school_address', 'Alamat Sekolah'),
            'logo_left'      => $this->imageToBase64(Setting::value('logo_left')),
            'logo_right'     => $this->imageToBase64(Setting::value('logo_right')),
            'sign_name'      => Setting::value('signature_name', 'Kepala Sekolah'),
            'sign_nip'       => Setting::value('signature_nip', '-'),
            'sign_image'     => $this->imageToBase64(Setting::value('signature_image')),
        ];
    }
}
