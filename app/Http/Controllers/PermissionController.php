<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    // 1. Tampilkan Semua Permission
    public function index()
    {
        $permissions = Permission::orderBy('created_at', 'desc')->get();
        return view('permissions.index', compact('permissions'));
    }

    // 2. Form Tambah Permission
    public function create()
    {
        return view('permissions.create');
    }

    // 3. Simpan Permission Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        // Simpan nama permission (biasanya huruf kecil & underscore, misal: edit_siswa)
        Permission::create(['name' => strtolower($request->name)]);

        return redirect()->route('permissions.index')->with('success', 'Permission Berhasil Dibuat!');
    }

    // 4. Form Edit Permission
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('permissions.edit', compact('permission'));
    }

    // 5. Update Permission
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,'.$id
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update(['name' => strtolower($request->name)]);

        return redirect()->route('permissions.index')->with('success', 'Permission Berhasil Diupdate!');
    }

    // 6. Hapus Permission
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission Berhasil Dihapus!');
    }
}
