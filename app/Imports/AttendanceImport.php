<?php

namespace App\Imports;

use App\Models\InternshipAttendance;
use App\Models\Internship;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AttendanceImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Cari data magang (internship) siswa berdasarkan NISN
        $internship = Internship::whereHas('student', function ($query) use ($row) {
            $query->where('nisn', $row['nisn']);
        })->first();

        if (!$internship) {
            return null; // Dilewati jika NISN tidak terdaftar pada data magang aktif
        }

        // Format tanggal & jam
        $date    = Carbon::parse($row['tanggal'])->format('Y-m-d');
        $timeIn  = !empty($row['jam_masuk']) ? Carbon::parse($row['jam_masuk'])->format('H:i:s') : null;
        $timeOut = !empty($row['jam_pulang']) ? Carbon::parse($row['jam_pulang'])->format('H:i:s') : null;

        // Pemetaan status absensi
        $statusInput = strtolower($row['status'] ?? 'present');
        $statusMap = [
            'hadir'   => 'present',
            'present' => 'present',
            'sakit'   => 'sick',
            'sick'    => 'sick',
            'izin'    => 'permit',
            'permit'  => 'permit',
        ];
        $status = $statusMap[$statusInput] ?? 'present';

        // Simpan atau update data absensi
        return InternshipAttendance::updateOrCreate(
            [
                'internship_id' => $internship->id,
                'date'          => $date,
            ],
            [
                'student_id'     => $internship->student_id,
                'time'           => $timeIn,
                'check_out_time' => $timeOut,
                'status'         => $status,
                'activity_log'   => $row['jurnal'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'nisn'       => 'required|exists:students,nisn',
            'tanggal'    => 'required|date',
            'status'     => 'required|in:present,sick,permit,hadir,sakit,izin',
            'jam_masuk'  => 'nullable',
            'jam_pulang' => 'nullable',
            'jurnal'     => 'nullable|string',
        ];
    }
}
