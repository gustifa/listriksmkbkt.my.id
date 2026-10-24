<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdditionalRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. RESET CACHE PERMISSION SPATIE
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. PERBAIKAN SEQUENCE ID POSTGRESQL (Mencegah Unique Constraint Violation ID=1)
        if (config('database.default') === 'pgsql') {
            DB::statement("SELECT setval('permissions_id_seq', COALESCE((SELECT MAX(id) FROM permissions), 1));");
            DB::statement("SELECT setval('roles_id_seq', COALESCE((SELECT MAX(id) FROM roles), 1));");
        }

        // 2. TAMBAHKAN PERMISSION BARU
        $newPermissions = [
            'manage_program',           // Akses Ketua Program Keahlian (Kaprog)
            'manage_workshop',          // Akses Kepala Bengkel (Kabeng)
            'view_all_reports',         // Akses laporan seluruh sekolah (Kepsek, Waka)
            'manage_students',          // Akses Kesiswaan (Wakasis)
            'manage_curriculum',        // Akses Kurikulum (Wakakur)
            'manage_public_relations', // Akses Humas & PKL (Wakahumas)
        ];

        foreach ($newPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }

        // 3. TAMBAHKAN ROLE BARU MANAJEMEN SEKOLAH

        // Kepala Sekolah
        $roleKepsek = Role::firstOrCreate(['name' => 'kepsek', 'guard_name' => 'web']);
        $roleKepsek->givePermissionTo(['view_report', 'view_all_reports']);

        // Wakil Kepala Sekolah Bidang Kurikulum
        $roleWakakur = Role::firstOrCreate(['name' => 'wakakur', 'guard_name' => 'web']);
        $roleWakakur->givePermissionTo(['master_data', 'manage_curriculum', 'view_report', 'view_all_reports']);

        // Wakil Kepala Sekolah Bidang Kesiswaan
        $roleWakasis = Role::firstOrCreate(['name' => 'wakasis', 'guard_name' => 'web']);
        $roleWakasis->givePermissionTo(['manage_students', 'daily_attendance', 'scan_attendance', 'view_report', 'view_all_reports']);

        // Wakil Kepala Sekolah Bidang Humas (Industri & PKL)
        $roleWakahumas = Role::firstOrCreate(['name' => 'wakahumas', 'guard_name' => 'web']);
        $roleWakahumas->givePermissionTo(['manage_public_relations', 'view_report', 'view_all_reports']);

        // Ketua Program Keahlian (11 Program Keahlian)
        $roleKaprog = Role::firstOrCreate(['name' => 'kaprog', 'guard_name' => 'web']);
        $roleKaprog->givePermissionTo(['manage_program', 'view_report', 'daily_attendance', 'scan_attendance']);

        // Kepala Bengkel (13 Konsentrasi Keahlian / Major)
        $roleKabeng = Role::firstOrCreate(['name' => 'kabeng', 'guard_name' => 'web']);
        $roleKabeng->givePermissionTo(['manage_workshop', 'view_report', 'scan_attendance']);

        // Sinkronkan seluruh permission ke Role Admin Utama
        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleAdmin->givePermissionTo(Permission::all());

        $this->command->info('AdditionalRoleSeeder Berhasil! Sequence PostgreSQL telah diperbaiki dan Role baru berhasil ditambahkan.');
    }
}