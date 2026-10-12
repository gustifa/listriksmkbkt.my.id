<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Major;
use App\Models\AdditionalTask;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherExport;
use App\DataTables\TeacherDataTable;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{

    /**
     * Tampilkan Detail Guru
     */
    public function show($id)
    {
        $teacher = Teacher::with(['user', 'additionalTask'])->findOrFail($id);
        return view('teachers.show', compact('teacher'));
    }

    /**
     * Tampilkan Daftar Guru
     */
    public function index(TeacherDataTable $dataTable)
    {
        return $dataTable->render('teachers.index');
    }

    /**
     * Fetch Data JSON untuk Modal Edit
     */
    public function editJson($id)
    {
        $cekData = DB::table('teachers')->where('id', $id)->first();
        
        if (!$cekData) {
            return response()->json([
                'message' => 'Data tidak ditemukan di tabel teachers',
                'id_dicari' => $id,
            ], 404);
        }

        // Load relasi user dan additionalTask
        $teacher = Teacher::with(['user', 'additionalTask'])->find($id);

        return response()->json($teacher);
    }

    /**
     * Form Edit Guru
     */
    public function edit($id)
    {
        $teacher = Teacher::with(['user', 'additionalTask'])->findOrFail($id);
        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Update Data Guru
     */
    // public function update(Request $request, $id)
    // {
    //     $teacher = Teacher::findOrFail($id);

    //     $request->validate([
    //         'name'               => 'required|string|max:255',
    //         'email'              => 'required|email|unique:users,email,' . $teacher->user_id,
    //         'nip'                => 'nullable|string|max:50|unique:teachers,nip,' . $id,
    //         'phone'              => 'nullable|numeric',
    //         'gender'             => 'nullable|in:L,P',
    //         'additional_task_id' => 'nullable|exists:additional_tasks,id',
    //     ]);

    //     // 1. Update Data Login (Tabel User)
    //     $teacher->user->update([
    //         'name'  => $request->name,
    //         'email' => $request->email,
    //     ]);

    //     if ($request->filled('password')) {
    //         $teacher->user->update([
    //             'password' => bcrypt($request->password),
    //         ]);
    //     }

    //     // 2. Sync / Backup Nama Tugas Tambahan
    //     $tugasNama = $request->tugas_tambahan;
    //     if ($request->filled('additional_task_id')) {
    //         $task = AdditionalTask::find($request->additional_task_id);
    //         if ($task) {
    //             $tugasNama = $task->name;
    //         }
    //     }

    //     // 3. Update Data Profil (Tabel Teacher)
    //     $teacher->update([
    //         'nip'                => $request->nip,
    //         'phone'              => $request->phone,
    //         'gender'             => $request->gender,
    //         'address'            => $request->address,
    //         'education_level'    => $request->education_level,
    //         'major_id'           => $request->major_id,
    //         'pangkat'            => $request->pangkat,
    //         'golongan'           => $request->golongan,
    //         'tugas_tambahan'     => $tugasNama,
    //         'additional_task_id' => $request->additional_task_id,
    //     ]);

    //     return redirect()->route('teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    // }

    public function update(Request $request, $id)
{
    $teacher = Teacher::findOrFail($id);

    $request->validate([
        'name'               => 'required|string|max:255',
        'email'              => 'required|email|unique:users,email,' . $teacher->user_id,
        'password'           => 'nullable|string|min:6',
        'nip'                => 'nullable|string|max:50|unique:teachers,nip,' . $id,
        'phone'              => 'nullable|numeric',
        'gender'             => 'nullable|in:L,P',
        'place_of_birth'     => 'nullable|string|max:255',
        'date_of_birth'      => 'nullable|date',
        'education_level'    => 'nullable|string|max:255',
        'pangkat'            => 'nullable|string|max:255',
        'golongan'           => 'nullable|string|max:255',
        'address'            => 'nullable|string',
        'additional_task_id' => 'nullable|exists:additional_tasks,id',
    ]);

    // 1. Update Data Login (Tabel User)
    $userData = [
        'name'  => $request->name,
        'email' => $request->email,
    ];

    if ($request->filled('password')) {
        $userData['password'] = bcrypt($request->password);
    }

    $teacher->user->update($userData);

    // 2. Sync / Backup Nama Tugas Tambahan
    $tugasNama = $request->tugas_tambahan;
    if ($request->filled('additional_task_id')) {
        $task = \App\Models\AdditionalTask::find($request->additional_task_id);
        if ($task) {
            $tugasNama = $task->name;
        }
    }

    // 3. Update Seluruh Data Profil (Tabel Teacher)
    $teacher->update([
        'nip'                => $request->nip,
        'phone'              => $request->phone,
        'gender'             => $request->gender,
        'place_of_birth'     => $request->place_of_birth,
        'date_of_birth'      => $request->date_of_birth,
        'education_level'    => $request->education_level,
        'pangkat'            => $request->pangkat,
        'golongan'           => $request->golongan,
        'address'            => $request->address,
        'tugas_tambahan'     => $tugasNama,
        'additional_task_id' => $request->additional_task_id,
    ]);

    return redirect()->route('teachers.index')->with('success', 'Seluruh data guru berhasil diperbarui.');
}

    /**
     * Hapus Guru (Beserta Akun Loginnya)
     */
    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->user->delete();

        return redirect()->route('teachers.index')->with('success', 'Data guru dan akun login berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new TeacherExport, 'Data-guru.xlsx');
    }
}