<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Classroom;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. RESET CACHE PERMISSION
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // 1. SETUP ROLE & PERMISSION
        Permission::create(['name' => 'master_data']);
        Permission::create(['name' => 'scan_attendance']);
        Permission::create(['name' => 'view_report']);
        Permission::create(['name' => 'daily_attendance']);

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleGuru = Role::create(['name' => 'guru']);
        $roleGuru->givePermissionTo(['scan_attendance', 'view_report']);

        $rolePiket = Role::create(['name' => 'piket']);
        $rolePiket->givePermissionTo(['daily_attendance']);

        $roleSiswa = Role::create(['name' => 'siswa']); // Role untuk login siswa nanti


        

        $this->command->info('Seeder Selesai! User, Kelas, Siswa, dan Jadwal sudah terhubung.');

    }
}
