<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

class UserImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
       // 1. Cari atau Buat User baru (Berdasarkan Email)
        // Jika email sama, data nama & password akan diupdate
        $user = User::updateOrCreate(
            ['email' => $row['email']], 
            [
                'name'     => $row['nama'],
                'password' => Hash::make($row['password']), // Enkripsi password
            ]
        );

        // 2. Assign Role Spatie
        // Pastikan di Excel kolom role isinya "guru" atau "admin" (huruf kecil)
        $roleName = strtolower($row['role']);
        
        // Cek apakah role tersebut ada di database untuk mencegah error
        if (Role::where('name', $roleName)->exists()) {
            $user->syncRoles($roleName); // Pakai sync agar role lama tertimpa
        } else {
            // Default ke guru jika salah ketik
            $user->syncRoles('guru'); 
        }

        return $user;
    }
}
