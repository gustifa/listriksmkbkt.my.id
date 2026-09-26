<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScheduleImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // 1. Validasi Baris Kosong
        if (!isset($row['email_guru']) || !isset($row['kelas']) || !isset($row['mapel'])) {
            return null;
        }

        // 2. CARI ID GURU (Berdasarkan Email)
        // Guru harus sudah terdaftar di sistem. Jika tidak ada, skip baris ini.
        $guru = User::where('email', $row['email_guru'])->first();
        if (!$guru) {
            // Opsional: Anda bisa log error ini atau abaikan
            return null; 
        }

        // 3. CARI / BUAT ID KELAS (Berdasarkan Nama)
        $classroom = Classroom::firstOrCreate(
            ['name' => strtoupper(trim($row['kelas']))]
        );

        // 4. CARI / BUAT ID MAPEL (Berdasarkan Nama)
        $subject = Subject::firstOrCreate(
            ['name' => trim($row['mapel'])]
        );

        // 5. SIMPAN JADWAL
        return new Schedule([
            'teacher_id'   => $guru->id,
            'classroom_id' => $classroom->id,
            'subject_id'   => $subject->id,
            'day'          => ucfirst(strtolower($row['hari'])), // Senin, Selasa...
            'start_time'   => $row['jam_mulai'], // Format Excel text "07:00"
            'end_time'     => $row['jam_selesai'],
        ]);
    }
}