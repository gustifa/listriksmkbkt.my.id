<?php

namespace App\Traits;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Model
 */
trait BelongsToActiveAcademicYear
{
    /**
     * Boot trait untuk menambahkan Global Scope otomatis
     */
    protected static function bootBelongsToActiveAcademicYear(): void
    {
        static::addGlobalScope('active_academic_year', function (Builder $builder) {
            // Ambil ID tahun pelajaran yang sedang aktif (is_active = true)
            $activeAcademicYearId = AcademicYear::where('is_active', true)->value('id');

            if ($activeAcademicYearId) {
                // Filter query berdasarkan academic_year_id dari tabel terkait
                $builder->where($builder->getModel()->getTable() . '.academic_year_id', $activeAcademicYearId);
            } else {
                // Jika TIDAK ADA tahun pelajaran aktif, paksa query menghasilkan 0 baris
                $builder->whereRaw('1 = 0');
            }
        });
    }

    /**
     * Relasi opsional ke Model AcademicYear
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
