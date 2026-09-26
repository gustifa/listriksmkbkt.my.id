<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Teacher;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 2. BUAT USER (ADMIN & GURU)
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@sekolah.com',
            'jenis_user' => 'admin',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // 1. Create atau Update Akun User (Login)
        $user = User::updateOrCreate(
            ['email' => 'guru@sekolah.com'], // Kunci pencarian agar tidak duplikat
            [
                'name' => 'Pak Guru Gatech',
                'jenis_user' => 'guru',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign Role Spatie
        $user->assignRole('guru');

        // 2. Create atau Update Data Guru (Profil)
        // Langkah ini WAJIB agar ScheduleController tidak error (karena butuh teacher_id)
        Teacher::updateOrCreate(
            ['user_id' => $user->id], // Hubungkan dengan user yang baru dibuat/diupdate
            [
                'nip' => 'GURU001',      // NIP Dummy
                'name' => $user->name,   // Samakan nama dengan user
                //'status' => '1'
            ]
        );

        $piket = User::create([
            'name' => 'Pak Piket Gatech',
            'email' => 'piket@sekolah.com',
            'jenis_user' => 'piket',
            'password' => Hash::make('password'),
        ]);
        $piket->assignRole('piket');

        $kelasTITL = Classroom::where('name', 'XII TITL 1')->first();
        // Siswa B: Masuk kelas TKJ (Untuk ngetes fitur "Salah Kelas")
        Student::create([
            'nis' => '67890',
            'name' => 'Budi TKJ',
            'classroom_id' => $kelasTITL->id
        ]);
    }
}
