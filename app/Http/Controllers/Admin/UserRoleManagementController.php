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
    public function update(Request $request, $id)
    {
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'program_id' => 'nullable|uuid',
            'major_id' => 'nullable|uuid',
        ]);

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // 1. Update Scope Data Kejuruan / Bengkel
            $user->update([
                'program_id' => $request->program_id,
                'major_id' => $request->major_id,
            ]);

            // 2. Sync Multi-Roles (Mendukung Lebih dari Satu Role)
            $roles = $request->roles ?? [];
            $user->syncRoles($roles);

            // 3. Sync Direct Extra Permissions (Izin Khusus Per User)
            $permissions = $request->permissions ?? [];
            $user->syncPermissions($permissions);

            // Reset cache Spatie
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Role dan Hak Akses User ' . $user->name . ' berhasil diperbarui!'
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