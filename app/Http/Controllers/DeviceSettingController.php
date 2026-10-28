<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceSettingController extends Controller
{
    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     $settings = SystemSetting::pluck('value', 'key');

    //     // 1. Ambil ID Kelas yang berhak diakses oleh User/Guru via Scope AccessibleBy
    //     $accessibleClassroomIds = Classroom::accessibleBy($user)->pluck('id');

    //     // 2. Query Data Siswa (Dibatasi hanya siswa di kelas yang berhak diakses)
    //     $query = User::role('siswa')
    //         ->whereHas('student', function ($q) use ($accessibleClassroomIds) {
    //             $q->whereIn('classroom_id', $accessibleClassroomIds);
    //         })
    //         ->with('student.classroom');

    //     // Filter 1: Cari Nama / Email
    //     if ($request->filled('search')) {
    //         $search = trim($request->search);
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'ILIKE', "%{$search}%")
    //               ->orWhere('email', 'ILIKE', "%{$search}%");
    //         });
    //     }

    //     // Filter 2: Berdasarkan Kelas (classroom_id di tabel students)
    //     if ($request->filled('classroom_id')) {
    //         $classId = $request->classroom_id;
            
    //         // Validasi keamanan: Pastikan classroom_id yang dipilih terdaftar di kelas yang diizinkan
    //         if ($accessibleClassroomIds->contains($classId)) {
    //             $query->whereHas('student', function ($q) use ($classId) {
    //                 $q->where('classroom_id', $classId);
    //             });
    //         } else {
    //             // Jika mencoba akses kelas di luar otoritasnya, kunci query ke 0 hasil
    //             $query->whereRaw('1 = 0');
    //         }
    //     }

    //     $students = $query->orderBy('name', 'asc')
    //                       ->paginate(15)
    //                       ->appends($request->all());

    //     // 3. Ambil data kelas terpilih (jika ada filter)
    //     $selectedClassroom = null;
    //     if ($request->filled('classroom_id') && $accessibleClassroomIds->contains($request->classroom_id)) {
    //         $selectedClassroom = Classroom::find($request->classroom_id);
    //     }

    //     return view('admin.device_settings.index', compact('settings', 'students', 'selectedClassroom'));
    // }

    // public function __construct()
    // {
    //     // IZINKAN ROLE-ROLE TERKAIT UNTUK MENGAKSES CONTROLLER INI
    //     $this->middleware(['role:admin|super_admin|super-admin|wali_kelas|wali-kelas|walas|guru_bk|bk|kabeng|kaprog|kaproka']);
        
    //     // ATAU JIKA MENGGUNAKAN SPATIE PERMISSION (Pilih salah satu sesuai arsitektur Anda):
    //     // $this->middleware('permission:device-settings.view');
    // }

    public function index(Request $request)
    {
        $user = Auth::user();
        $settings = SystemSetting::pluck('value', 'key');

        // 1. Ambil ID Kelas yang berhak diakses via scopeAccessibleBy
        $accessibleClassroomIds = Classroom::accessibleBy($user)->pluck('id');

        // 2. Query Data Siswa (Dibatasi hanya siswa di kelas yang berhak diakses)
        $query = User::role('siswa')
            ->whereHas('student', function ($q) use ($accessibleClassroomIds) {
                $q->whereIn('classroom_id', $accessibleClassroomIds);
            })
            ->with('student.classroom');

        // Filter 1: Cari Nama / Email
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Filter 2: Berdasarkan Kelas (classroom_id di tabel students)
        if ($request->filled('classroom_id')) {
            $classId = $request->classroom_id;
            
            // Validasi keamanan: Pastikan classroom_id terdaftar di kelas yang diizinkan
            if ($accessibleClassroomIds->contains($classId)) {
                $query->whereHas('student', function ($q) use ($classId) {
                    $q->where('classroom_id', $classId);
                });
            } else {
                // Kunci query ke 0 hasil jika di luar otoritas
                $query->whereRaw('1 = 0');
            }
        }

        $students = $query->orderBy('name', 'asc')
                          ->paginate(15)
                          ->appends($request->all());

        // 3. Ambil data kelas terpilih (jika ada filter)
        $selectedClassroom = null;
        if ($request->filled('classroom_id') && $accessibleClassroomIds->contains($request->classroom_id)) {
            $selectedClassroom = Classroom::find($request->classroom_id);
        }

        return view('admin.device_settings.index', compact('settings', 'students', 'selectedClassroom'));
    }

    /**
     * Endpoint Select2 AJAX Filter Kelas (Dibatasi sesuai Role AccessibleBy)
     */
    public function getClasses(Request $request)
    {
        $search = trim($request->input('q', ''));
        $user   = Auth::user();

        // Terapkan scopeAccessibleBy pada pencarian kelas Select2
        $query = Classroom::accessibleBy($user);

        if (!empty($search)) {
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        $classes = $query->orderBy('name', 'asc')->limit(30)->get();

        $results = $classes->map(function ($class) {
            return [
                'id'   => (string) $class->id, // UUID
                'text' => $class->name,        // Nama Kelas
            ];
        });

        return response()->json([
            'results' => $results
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'enable_device_lock'  => 'required|in:0,1',
            'max_allowed_devices' => 'required|in:1,2',
        ]);

        SystemSetting::updateOrCreate(['key' => 'enable_device_lock'], ['value' => $request->enable_device_lock]);
        SystemSetting::updateOrCreate(['key' => 'max_allowed_devices'], ['value' => $request->max_allowed_devices]);

        return redirect()->back()->with('success', 'Pengaturan kunci perangkat berhasil diperbarui.');
    }

    public function resetDevice(Request $request, $id)
    {
        $user = Auth::user();
        $student = User::role('siswa')->with('student')->findOrFail($id);

        // Validasi keamanan: Pastikan guru hanya bisa reset HP siswa di kelas otoritasnya
        $accessibleClassroomIds = Classroom::accessibleBy($user)->pluck('id');
        
        if ($student->student && !$accessibleClassroomIds->contains($student->student->classroom_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mereset perangkat siswa di kelas ini.',
            ], 403);
        }

        $student->forceFill([
            'device_id_1' => null,
            'device_id_2' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => "Tautan perangkat milik {$student->name} berhasil di-reset.",
        ]);
    }
}