<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student'); // ID siswa yang sedang di-update

        return [
            // Identitas Pribadi
            'nis'               => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($studentId)],
            'nisn'              => ['nullable', 'string', 'max:50', Rule::unique('students', 'nisn')->ignore($studentId)],
            'nik'               => ['nullable', 'string', 'max:16', Rule::unique('students', 'nik')->ignore($studentId)],
            'name'              => ['required', 'string', 'max:255'],
            'gender'            => ['required', 'in:L,P'],
            'pob'               => ['nullable', 'string', 'max:100'],
            'dob'               => ['nullable', 'date'],
            'religion'          => ['nullable', 'string', 'max:50'],
            
            // Kontak & Alamat
            'phone'             => ['nullable', 'string', 'max:20'], // Nomor HP Orang Tua/Wali
            'student_phone'     => ['nullable', 'string', 'max:20'], // Nomor HP Pribadi Siswa
            'email'             => ['nullable', 'email', 'max:255'],
            'address'           => ['nullable', 'string'],
            'rt'                => ['nullable', 'string', 'max:10'],
            'rw'                => ['nullable', 'string', 'max:10'],
            'dusun'             => ['nullable', 'string', 'max:100'],
            'subdistrict'       => ['nullable', 'string', 'max:100'],
            'district'          => ['nullable', 'string', 'max:100'],
            'postal_code'       => ['nullable', 'string', 'max:10'],

            // Akademik
            'classroom_id'      => ['required', 'exists:classrooms,id'],
            'status'            => ['required', 'string'],
            'is_promoted'       => ['boolean'],
            'previous_school'   => ['nullable', 'string', 'max:255'],

            // Orang Tua (Ayah)
            'father_name'       => ['nullable', 'string', 'max:255'],
            'father_nik'        => ['nullable', 'string', 'max:16'],
            'father_education'  => ['nullable', 'string', 'max:50'],
            'father_job'        => ['nullable', 'string', 'max:100'],
            'father_income'     => ['nullable', 'string', 'max:100'],

            // Orang Tua (Ibu)
            'mother_name'       => ['nullable', 'string', 'max:255'],
            'mother_nik'        => ['nullable', 'string', 'max:16'],
            'mother_education'  => ['nullable', 'string', 'max:50'],
            'mother_job'        => ['nullable', 'string', 'max:100'],
            'mother_income'     => ['nullable', 'string', 'max:100'],

            // Wali Siswa
            'guardian_name'     => ['nullable', 'string', 'max:255'],
            'guardian_nik'      => ['nullable', 'string', 'max:16'],
            'guardian_education'=> ['nullable', 'string', 'max:50'],
            'guardian_job'      => ['nullable', 'string', 'max:100'],
            'guardian_income'   => ['nullable', 'string', 'max:100'],
        ];
    }
}