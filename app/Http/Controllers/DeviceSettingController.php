<?php

namespace App\Http\Controllers;
use App\Models\Classroom;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;

class DeviceSettingController extends Controller
{
    public function index(Request $request)
    {
        $settings = SystemSetting::pluck('value', 'key');

        // Load relasi student beserta classroom-nya
        $query = User::role('siswa')->with('student.classroom');

        // Filter 1: Nama / Email
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
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('classroom_id', $classId);
            });
        }

        $students = $query->orderBy('name', 'asc')
                          ->paginate(15)
                          ->appends($request->all());

        $selectedClassroom = null;
        if ($request->filled('classroom_id')) {
            $selectedClassroom = Classroom::find($request->classroom_id);
        }

        return view('admin.device_settings.index', compact('settings', 'students', 'selectedClassroom'));
    }

    // public function getClasses(Request $request)
    // {
    //     $search = trim($request->input('q', ''));
    //     $query = Classroom::query();

    //     if (!empty($search)) {
    //         $query->where('name', 'ILIKE', "%{$search}%");
    //     }

    //     $classes = $query->orderBy('name', 'asc')->limit(20)->get();

    //     $results = $classes->map(function ($class) {
    //         return [
    //             'id'   => $class->id,
    //             'text' => $class->name,
    //         ];
    //     });

    //     return response()->json(['results' => $results]);
    // }

    public function getClasses(Request $request)
{
    $search = trim($request->input('q', ''));

    $query = Classroom::query();

    if (!empty($search)) {
        $query->where('name', 'ILIKE', "%{$search}%");
    }

    $classes = $query->orderBy('name', 'asc')->limit(30)->get();

    $results = $classes->map(function ($class) {
        return [
            'id'   => (string) $class->id,   // UUID
            'text' => $class->name,          // Nama Kelas
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
        $student = User::findOrFail($id);

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