<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AddPiketRoleSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Role Baru "satpam" (jika belum ada)
        // firstOrCreate mencegah error jika seeder dijalankan 2x
        $rolePiket = Role::firstOrCreate(['name' => 'piket']);
        $rolePiket->givePermissionTo(['daily_attendance']);


        // 2. (Opsional) Berikan Permission khusus jika ada
        // $roleSatpam->givePermissionTo('scan_gerbang');

        // 3. Buat User Akun Satpam
        $piket = User::firstOrCreate(
            ['email' => 'piket@sekolah.com'], // Cek email agar tidak duplikat
            [
                'name' => 'Piket Sekolah',
                'password' => Hash::make('password'), // Password default
            ]
        );

        // 4. Assign Role ke User tersebut
        $piket->assignRole($rolePiket);

        $this->command->info('Role "Piket" berhasil dibuat dan user "Piket" siap digunakan.');
    }
}
