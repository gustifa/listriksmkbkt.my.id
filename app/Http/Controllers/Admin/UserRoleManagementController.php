<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Program;
use App\Models\Major;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class UserRoleManagementController extends Controller
{
    /**
     * Tampilkan halaman utama manajemen role user
     */
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $programs = Program::all();
        $majors = Major::all();

        return view('admin.user_roles.index', compact('roles', 'permissions', 'programs', 'majors'));
    }

    /**
     * Fetch Data User untuk Datatables / AJAX Table
     */
    public function data(Request $request)
    {
        $users = User::with(['roles', 'permissions', 'program', 'major'])->latest()->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'jenis_user' => $user->jenis_user ?? '-',
                'program' => $user->program ? $user->program->name : '-',
                'major' => $user->major ? $user->major->name : '-',
                'roles' => $user->roles->pluck('name')->toArray(),
                'direct_permissions' => $user->getDirectPermissions()->pluck('name')->toArray(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Ambil Detail User untuk Modal Edit (AJAX)
     */
    public function edit($id)
    {
        $user = User::with(['roles', 'permissions'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'jenis_user' => $user->jenis_user,
                'program_id' => $user->program_id,
                'major_id' => $user->major_id,
                'roles' => $user->roles->pluck('name')->toArray(),
                'direct_permissions' => $user->getDirectPermissions()->pluck('name')->toArray(),
            ]
        ]);
    }

    /**
     * Simpan Perubahan Multi-Role & Permission (AJAX)
     */
    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'roles' => 'nullable|array',
    //         'roles.*' => 'exists:roles,name',
    //         'permissions' => 'nullable|array',
    //         'permissions.*' => 'exists:permissions,name',
    //         'program_id' => 'nullable|uuid',
    //         'major_id' => 'nullable|uuid',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $user = User::findOrFail($id);

    //         // 1. Update Scope Data Kejuruan / Bengkel
    //         $user->update([
    //             'program_id' => $request->program_id,
    //             'major_id' => $request->major_id,
    //         ]);

    //         // 2. Sync Multi-Roles (Mendukung Lebih dari Satu Role)
    //         $roles = $request->roles ?? [];
    //         $user->syncRoles($roles);

    //         // 3. Sync Direct Extra Permissions (Izin Khusus Per User)
    //         $permissions = $request->permissions ?? [];
    //         $user->syncPermissions($permissions);

    //         // Reset cache Spatie
    //         app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Role dan Hak Akses User ' . $user->name . ' berhasil diperbarui!'
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal memperbarui role: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function update(Request $request, $id)
{
    $request->validate([
        'roles' => 'nullable|array',
        'roles.*' => 'exists:roles,name',
        'permissions' => 'nullable|array',
        'permissions.*' => 'exists:permissions,name',
        'program_id' => 'nullable|uuid|exists:programs,id',
        'major_id' => 'nullable|uuid|exists:majors,id',
    ]);

    DB::beginTransaction();
    try {
        $user = User::findOrFail($id);
        $roles = $request->roles ?? [];

        // 1. Tangkap input dari form
        $programId = $request->filled('program_id') ? $request->program_id : null;
        $majorId   = $request->filled('major_id')   ? $request->major_id   : null;

        // 2. Update kolom program_id dan major_id di tabel users
        $user->program_id = $programId;
        $user->major_id   = $majorId;
        $user->save();

        // 3. Sinkronisasi Dua Arah: Update tabel programs jika role kaprog dipilih
        if (in_array('kaprog', $roles) && $programId) {
            // Ambil ID data teacher yang berelasi dengan user ini
            $teacherId = $user->teacher ? $user->teacher->id : null;

            if ($teacherId) {
                // Hapus penugasan kaprog lama pada program ini (jika ada)
                \App\Models\Program::where('program_teacher_id', $teacherId)
                    ->update(['program_teacher_id' => null]);

                // Set guru ini sebagai Kaprog baru di tabel programs
                \App\Models\Program::where('id', $programId)
                    ->update(['program_teacher_id' => $teacherId]);
            }
        }

        // 4. Sinkronisasi Dua Arah: Update tabel majors jika role kabeng dipilih
        if (in_array('kabeng', $roles) && $majorId) {
            $teacherId = $user->teacher ? $user->teacher->id : null;

            if ($teacherId) {
                // Hapus penugasan kabeng lama pada major ini (jika ada)
                \App\Models\Major::where('workshop_teacher_id', $teacherId)
                    ->update(['workshop_teacher_id' => null]);

                // Set guru ini sebagai Kabeng baru di tabel majors
                \App\Models\Major::where('id', $majorId)
                    ->update(['workshop_teacher_id' => $teacherId]);
            }
        }

        // 5. Sync Multi-Role & Permissions (Spatie)
        $user->syncRoles($roles);
        $user->syncPermissions($request->permissions ?? []);

        // 6. Reset Cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Role dan Scope Keahlian user ' . $user->name . ' berhasil diperbarui!'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal memperbarui role: ' . $e->getMessage()
        ], 500);
    }
}
}