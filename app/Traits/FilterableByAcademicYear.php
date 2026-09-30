<?php

namespace App\Traits;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Builder;

trait FilterableByAcademicYear
{
    /**
     * Scope untuk menyaring data berdasarkan tahun pelajaran aktif
     */
    public function scopeActiveAcademicYear(Builder $query): Builder
    {
        $activeId = AcademicYear::where('is_active', true)->value('id');

        if ($activeId) {
            return $query->where($this->getTable() . '.academic_year_id', $activeId);
        }

        // Jika tidak ada yang aktif, paksa kosong
        return $query->whereRaw('1 = 0');
    }
}
