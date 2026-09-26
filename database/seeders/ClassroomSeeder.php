<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classroom;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar Tingkat Kelas
        $grades = ['X', 'XI', 'XII'];

        // Daftar Jurusan (Kode Jurusan)
        // $majors = ['RPL', 'TKJ', 'MM', 'AKL', 'OTKP'];
        $majors = ['TITL', 'TPTUP'];

        // Daftar Rombel (Misal setiap jurusan punya kelas 1 dan 2)
        $groups = ['1', '2'];

        foreach ($grades as $grade) {
            foreach ($majors as $major) {
                foreach ($groups as $group) {
                    // Format Nama: XII RPL 1
                    $className = "{$grade} {$major} {$group}";

                    // Gunakan firstOrCreate agar tidak duplikat jika seeder dijalankan 2x
                    Classroom::firstOrCreate([
                        'name' => $className
                    ]);
                }
            }
        }

        // Tambahan Manual (Jika ada kelas khusus)
        Classroom::firstOrCreate(['name' => 'XIII SIJA 1']); // Kelas 13 Program 4 Tahun
        

        $this->command->info('Data Kelas (Classrooms) berhasil digenerate!');
    }
}
