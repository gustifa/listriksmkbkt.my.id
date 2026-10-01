<?php

namespace App\Rules;

use App\Models\AcademicYear;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveAcademicYearExists implements ValidationRule
{
    /**
     * Jalankan aturan validasi.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $hasActiveYear = AcademicYear::where('is_active', true)->exists();

        if (!$hasActiveYear) {
            $fail('Gagal memproses form! Belum ada Tahun Pelajaran / Semester yang diaktifkan oleh Admin.');
        }
    }
}
