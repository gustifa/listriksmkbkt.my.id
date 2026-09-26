<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Classroom;
use App\Models\User;

class MapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Classroom::firstOrCreate(['name' => 'XIII TITL 1']); 
        $kelasTITL = Classroom::where('name', 'XII TITL 1')->first();
        $mapelTITL = Subject::where('name', 'Dasar Program Keahlian')->first();
        $guru = User::where('name', 'Pak Guru Gatech')->first();
        Schedule::create([
            'teacher_id' => $guru->id,       // Milik Pak Budi
            'classroom_id' => $kelasTITL->id, // Untuk Kelas XII RPL 1
            'subject_id' => $mapelTITL->id,
            'day' => 'Sabtu',                // Sesuaikan dengan hari testing Anda
            'start_time' => '07:00:00',
            'end_time' => '23:59:00',
        ]);
    }
}
