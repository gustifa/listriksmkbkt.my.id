<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DailyAttendancePermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Reset Cache Permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Permission Baru 'daily_attendance'
        // Gunakan firstOrCreate agar tidak error jika dijalankan berulang
        $permission = Permission::firstOrCreate(['name' => 'daily_attendance']);

        // 3. Assign Permission ke Role yang Relevan

        // Admin (Wajib punya semua)
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->givePermissionTo($permission);

        // Guru (Bisa scan gerbang juga kalau piket)
        $roleGuru = Role::firstOrCreate(['name' => 'guru']);
        $roleGuru->givePermissionTo($permission);

        // Satpam (Tugas utamanya scan gerbang)
        $roleSatpam = Role::firstOrCreate(['name' => 'piket']);
        $roleSatpam->givePermissionTo($permission);

        $this->command->info('Permission "daily_attendance" berhasil dibuat dan diberikan ke Admin, Guru, & Satpam.');
    }
}
