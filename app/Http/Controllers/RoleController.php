<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    // 1. Tampilkan Semua Role
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    // 2. Form Tambah Role
    public function create()
    {
        // Ambil semua permission agar bisa dicentang
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    // 3. Simpan Role Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array'
        ]);

        $role = Role::create(['name' => strtolower($request->name)]);
        
        // Assign permission yang dicentang ke role ini
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role Berhasil Dibuat!');
    }

    // 4. Form Edit Role
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        
        // Ambil permission yang SUDAH dimiliki role ini (untuk auto-check checkbox)
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    // 5. Update Role
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$id,
            'permissions' => 'required|array'
        ]);

        $role = Role::findOrFail($id);
        
        // Cegah edit nama role admin (biar aman)
        if($role->name != 'admin') {
            $role->name = strtolower($request->name);
            $role->save();
        }

        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role Berhasil Diupdate!');
    }

    // 6. Hapus Role
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Cegah hapus role admin
        if ($role->name == 'admin') {
            return redirect()->back()->with('error', 'Role Admin tidak boleh dihapus!');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role Berhasil Dihapus!');
    }
}
