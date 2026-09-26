<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class GenerateStudentUsers extends Command
{
    // Nama perintah yang akan diketik di terminal
    protected $signature = 'siswa:generate-users';

    // Deskripsi perintah
    protected $description = 'Otomatis membuat akun User untuk Siswa yang belum punya akun atau mengaitkan user_id yang kosong';

    public function handle()
    {
        $this->info('Memulai proses sinkronisasi User untuk Siswa...');

        // 1. Pastikan Role Siswa Ada (Spatie Permission)
        if (!Role::where('name', 'siswa')->exists()) {
            Role::create(['name' => 'siswa']);
            $this->info('Role "siswa" berhasil dibuat.');
        }

        // 2. Ambil Siswa yang kolom user_id-nya masih KOSONG
        $students = Student::whereNull('user_id')->get();

        if ($students->isEmpty()) {
            $this->info('Semua siswa sudah memiliki akun User.');
            return;
        }

        $bar = $this->output->createProgressBar(count($students));
        $bar->start();

        foreach ($students as $student) {
            // Format Email Dummy: NIS@siswa.sekolah.id
            $email = $student->nis . '@siswa.sekolah.id';
            
            // Cek apakah user dengan email ini sudah ada di tabel users?
            $existingUser = User::where('email', $email)->first();

            if (!$existingUser) {
                // KONDISI A: User benar-benar belum ada, maka buat baru
                $newUser = User::create([
                    'name' => $student->name,
                    'email' => $email,
                    'password' => Hash::make($student->nis), // Password default = NIS
                ]);

                // Assign Role Spatie
                $newUser->assignRole('siswa');

                // Update Tabel Student (Link-kan user_id baru)
                $student->update(['user_id' => $newUser->id]);
            } else {
                // KONDISI B: Akun User sudah ada (mungkin dari import sebelumnya),
                // tapi student.user_id masih kosong. Kita cukup lakukan update/link.
                
                // Pastikan user tersebut memiliki role siswa
                if (!$existingUser->hasRole('siswa')) {
                    $existingUser->assignRole('siswa');
                }

                $student->update(['user_id' => $existingUser->id]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('BERHASIL! Semua siswa kini tersinkronisasi dengan akun login.');
        $this->info('Format Login -> Email: [NIS]@siswa.sekolah.id | Password: [NIS]');
    }
}