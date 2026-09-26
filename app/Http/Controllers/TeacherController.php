<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Major;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherExport;
use App\DataTables\TeacherDataTable;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{

    /**
     * [BARU] Tampilkan Detail Guru
     */
    public function show($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        return view('teachers.show', compact('teacher'));
    }
    /**
     * Tampilkan Daftar Guru
     */
    // public function index(Request $request)
    // {
    //     $query = Teacher::with('user');

    //     // Fitur Pencarian (Nama, Email, NIP)
    //     if ($request->has('search')) {
    //         $search = $request->search;
    //         $query->whereHas('user', function($q) use ($search) {
    //             $q->where('name', 'LIKE', "%{$search}%")
    //               ->orWhere('email', 'LIKE', "%{$search}%");
    //         })->orWhere('nip', 'LIKE', "%{$search}%");
    //     }

    //     // Pagination 10 data per halaman
    //     $teachers = $query->paginate(10);

    //     return view('teachers.index', compact('teachers'));
    // }

    public function index(TeacherDataTable $dataTable)
    {
        return $dataTable->render('teachers.index'); // Sesuaikan path view Anda
    }

    // public function editJson($id)
    // {
    //     $teacher = Teacher::with('user')->findOrFail($id);
    //     return response()->json($teacher);
    // }

    // public function editJson($id)
    // {
    //     try {
    //         // Gunakan with('user') karena di modal kita memanggil data.user.name
    //         $teacher = Teacher::with('user')->findOrFail($id);
    //         return response()->json($teacher);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function editJson($id)
    {
        // // Gunakan with('user') agar data akun ikut terambil
        // $teacher = Teacher::with('user')->find($id);

        // if (!$teacher) {
        //     return response()->json(['message' => 'Data tidak ditemukan'], 404);
        // }

        // return response()->json($teacher);
        // Eager loading relasi user
        // $teacher = \App\Models\Teacher::with('user')->find($id);

        // if (!$teacher) {
        //     return response()->json(['message' => 'Data tidak ditemukan'], 404);
        // }

        // return response()->json($teacher);

        // Eager load relasi 'user'
        // $teacher = \App\Models\Teacher::with('user')->where('id', $id)->first();

        // if (!$teacher) {
        //     // Jika sampai di sini, artinya ID "eee65b94..." tidak ada di kolom 'id' tabel teachers
        //     return response()->json([
        //         'message' => 'Data tidak ditemukan',
        //         'debug_id_yang_dicari' => $id 
        //     ], 404);
        // }

        // return response()->json($teacher);
        // 1. Cek tanpa relasi dulu untuk memastikan ID-nya ada
        $cekData = \DB::table('teachers')->where('id', $id)->first();
        
        if (!$cekData) {
            // Jika masuk ke sini, berarti UUID tersebut memang TIDAK ADA di tabel 'teachers'
            // Cek apakah ID tersebut sebenarnya adalah 'user_id'?
            return response()->json([
                'message' => 'Data tidak ditemukan di tabel teachers',
                'id_dicari' => $id,
                'saran' => 'Pastikan kolom ID di tabel teachers berisi UUID tersebut.'
            ], 404);
        }

        // 2. Jika ada, ambil dengan relasi user
        $teacher = \App\Models\Teacher::with('user')->find($id);

        return response()->json($teacher);
        }

    /**
     * Form Edit Guru
     */
    // public function edit($id)
    // {
    //     $majors = Major::get();
    //     $teacher = Teacher::with('user')->findOrFail($id);
    //     return view('teachers.edit', compact('teacher', 'majors'));
    // }

    public function edit($id)
{
    // Menggunakan findOrFail agar jika tidak ada langsung melempar 404 yang benar (ModelNotFound)
    // with('user') memastikan data nama dan email ikut terbawa
    $teacher = \App\Models\Teacher::with('user')->findOrFail($id);

    return view('teachers.edit', compact('teacher'));
}

    /**
     * Update Data Guru
     */
    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:255',
            'nip'    => 'nullable|string|max:50|unique:teachers,nip,' . $id,
            'phone'  => 'nullable|numeric',
            'gender' => 'nullable|in:L,P',
        ]);

        // 1. Update Data Login (Tabel User)
        $teacher->user->update([
            'name' => $request->name
        ]);

        // 2. Update Data Profil (Tabel Teacher)
        $teacher->update([
            'nip'     => $request->nip,
            'phone'   => $request->phone,
            'gender'  => $request->gender,
            'address' => $request->address,
            'major_id' => $request->major_id,
            'pangkat' => $request->pangkat,
            'golongan' => $request->golongan,
            'tugas_tambahan' => $request->tugas_tambahan,
        ]);

        return redirect()->route('teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Hapus Guru (Beserta Akun Loginnya)
     */
    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);

        // Hapus usernya, otomatis teacher terhapus karena cascade delete
        $teacher->user->delete();

        return redirect()->route('teachers.index')->with('success', 'Data guru dan akun login berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new TeacherExport, 'Data-guru.xlsx');
    }
}
