<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    /**
     * Tampilkan daftar Role dan Permission
     */
    public function index()
    {
        // Ambil semua role beserta permission yang dimilikinya (kecuali super_admin jika ingin disembunyikan, tapi di sini kita tampilkan semua)
        $roles = Role::with('permissions')->withCount('users')->get();
        $permissions = Permission::all();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Simpan Role Baru
     */
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        Role::create(['name' => strtolower(str_replace(' ', '_', $request->name))]);

        return redirect()->back()->with('success', 'Role baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan Halaman Edit Permission untuk suatu Role
     */
    public function editRole($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get();

        // Kelompokkan permission agar rapi di tampilan (berdasarkan prefix pertama dari nama permission)
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            // Misal: 'manage_users' -> prefix 'manage' atau kita grouping manual berdasarkan keyword
            $group = 'Lainnya';
            if (str_contains($permission->name, 'dashboard')) $group = 'Dashboard';
            elseif (str_contains($permission->name, 'attendance') || str_contains($permission->name, 'prayer')) $group = 'Absensi';
            elseif (str_contains($permission->name, 'journal')) $group = 'Jurnal';
            elseif (str_contains($permission->name, 'guidance')) $group = 'Bimbingan Konseling (BK)';
            elseif (str_contains($permission->name, 'pkl')) $group = 'PKL';
            elseif (str_contains($permission->name, 'master_data') || str_contains($permission->name, 'user') || str_contains($permission->name, 'role')) $group = 'Data Master';

            $groupedPermissions[$group][] = $permission;
        }

        return view('admin.roles.edit', compact('role', 'groupedPermissions'));
    }

    /**
     * Update/Sync Permission ke Role
     */
    public function updateRolePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        // Super Admin tidak boleh diubah hak aksesnya (selalu punya semua)
        if ($role->name === 'super_admin') {
            return redirect()->route('admin.roles.index')->with('error', 'Role Super Admin tidak dapat dimodifikasi.');
        }

        // Sinkronisasi permission (hapus yang tidak dicentang, tambah yang dicentang)
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Hak akses untuk Role ' . strtoupper($role->name) . ' berhasil diperbarui.');
    }

    /**
     * Hapus Role
     */
    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'super_admin' || $role->name === 'admin') {
            return redirect()->back()->with('error', 'Role sistem dasar (Admin/Super Admin) tidak boleh dihapus.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh ' . $role->users()->count() . ' pengguna.');
        }

        $role->delete();

        return redirect()->back()->with('success', 'Role berhasil dihapus.');
    }
}