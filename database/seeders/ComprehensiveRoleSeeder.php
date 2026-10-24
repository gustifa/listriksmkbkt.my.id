<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ComprehensiveRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. RESET CACHE PERMISSION SPATIE
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. OTOMATISASI RESET SEQUENCE ID POSTGRESQL (Mencegah Unique Constraint Violation)
        if (config('database.default') === 'pgsql') {
            DB::statement("SELECT setval('permissions_id_seq', COALESCE((SELECT MAX(id) FROM permissions), 1));");
            DB::statement("SELECT setval('roles_id_seq', COALESCE((SELECT MAX(id) FROM roles), 1));");
        }

        // 2. DAFTAR PERMISSION LENGKAP
        $permissions = [
            'master_data',              // Master Data Utama
            'scan_attendance',          // Scanner Absensi (QR / Wajah)
            'view_report',              // Melihat Laporan Standar
            'daily_attendance',         // Kelola Presensi Harian
            'manage_program',           // Akses Ketua Program Keahlian (Kaprog)
            'manage_workshop',          // Akses Kepala Bengkel (Kabeng)
            'view_all_reports',         // Rekap Seluruh Laporan Sekolah
            'manage_students',          // Akses Kesiswaan (Wakasis)
            'manage_curriculum',        // Akses Kurikulum (Wakakur)
            'manage_public_relations', // Akses Humas (Wakahumas)
            'manage_counseling',        // Akses Bimbingan Konseling (Guru BK)
            'view_class_transcript',    // Cetak Transkrip Absensi (Wali Kelas & BK)
            'manage_internship',        // Akses Pokja PKL
            'manage_extracurricular',   // Akses Pembina Ekstrakurikuler
            'manage_finance',           // Akses Keuangan (Bendahara)
            'manage_administration',    // Akses Tata Usaha (Ka TU & Pegawai)
            'gate_security',            // Akses Monitor Gerbang (Security/Satpam)
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }

        // 3. DAFTAR ROLE DAN SINKRONISASI PERMISSION

        // Pimpinan & Manajemen Sekolah
        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleKepsek = Role::firstOrCreate(['name' => 'kepsek', 'guard_name' => 'web']);
        $roleKepsek->givePermissionTo(['view_report', 'view_all_reports']);

        $roleWakakur = Role::firstOrCreate(['name' => 'wakakur', 'guard_name' => 'web']);
        $roleWakakur->givePermissionTo(['master_data', 'manage_curriculum', 'view_report', 'view_all_reports']);

        $roleWakasis = Role::firstOrCreate(['name' => 'wakasis', 'guard_name' => 'web']);
        $roleWakasis->givePermissionTo(['manage_students', 'daily_attendance', 'scan_attendance', 'view_report', 'view_all_reports', 'manage_extracurricular']);

        $roleWakahumas = Role::firstOrCreate(['name' => 'wakahumas', 'guard_name' => 'web']);
        $roleWakahumas->givePermissionTo(['manage_public_relations', 'manage_internship', 'view_report', 'view_all_reports']);

        // Kejuruan & Bengkel
        $roleKaprog = Role::firstOrCreate(['name' => 'kaprog', 'guard_name' => 'web']);
        $roleKaprog->givePermissionTo(['manage_program', 'view_report', 'daily_attendance', 'scan_attendance']);

        $roleKabeng = Role::firstOrCreate(['name' => 'kabeng', 'guard_name' => 'web']);
        $roleKabeng->givePermissionTo(['manage_workshop', 'view_report', 'scan_attendance']);

        // Wali Kelas & Guru BK
        $roleWaliKelas = Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);
        $roleWaliKelas->givePermissionTo(['view_report', 'view_class_transcript', 'daily_attendance']);

        $roleGuruBk = Role::firstOrCreate(['name' => 'guru_bk', 'guard_name' => 'web']);
        $roleGuruBk->givePermissionTo(['view_report', 'view_class_transcript', 'manage_counseling', 'daily_attendance']);

        // Pokja PKL & Pembina Ekskul
        $rolePokjaPkl = Role::firstOrCreate(['name' => 'pokja_pkl', 'guard_name' => 'web']);
        $rolePokjaPkl->givePermissionTo(['manage_internship', 'view_report']);

        $rolePembinaEkskul = Role::firstOrCreate(['name' => 'pembina_ekskul', 'guard_name' => 'web']);
        $rolePembinaEkskul->givePermissionTo(['manage_extracurricular', 'daily_attendance']);

        // Tata Usaha & Keuangan
        $roleKaTu = Role::firstOrCreate(['name' => 'ka_tu', 'guard_name' => 'web']);
        $roleKaTu->givePermissionTo(['master_data', 'manage_administration', 'view_report', 'view_all_reports']);

        $rolePegawai = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);
        $rolePegawai->givePermissionTo(['manage_administration', 'daily_attendance']);

        $roleBendahara = Role::firstOrCreate(['name' => 'bendahara', 'guard_name' => 'web']);
        $roleBendahara->givePermissionTo(['manage_finance', 'view_report']);

        // Operasional & Satpam
        $roleSecurity = Role::firstOrCreate(['name' => 'security', 'guard_name' => 'web']);
        $roleSecurity->givePermissionTo(['gate_security', 'scan_attendance', 'daily_attendance']);

        $roleGuru = Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);
        $roleGuru->givePermissionTo(['scan_attendance', 'view_report']);

        $rolePiket = Role::firstOrCreate(['name' => 'piket', 'guard_name' => 'web']);
        $rolePiket->givePermissionTo(['daily_attendance', 'scan_attendance']);

        $roleSiswa = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        $this->command->info('ComprehensiveRoleSeeder Berhasil! Seluruh Role baru telah ditambahkan tanpa mengganggu seeder lama.');
    }
}