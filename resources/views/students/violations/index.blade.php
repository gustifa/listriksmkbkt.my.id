@extends('layouts.app')

@section('content')
<div class="page-content">
    <!-- HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Riwayat Pelanggaran & Poin</h3>
            <p class="text-muted small mb-0">Pantau catatan pelanggaran dan akumulasi poin kedisiplinan Anda.</p>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 bg-danger text-white h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 text-uppercase fw-semibold d-block mb-1 fs-7">Total Poin Pelanggaran</span>
                        <h2 class="fw-bold mb-0 display-6 text-white">{{ $totalPoints }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 54px; height: 54px;">
                        <i class="bx bx-error-circle fs-2 text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 text-uppercase fw-semibold d-block mb-1 fs-7">Total Kejadian</span>
                        <h2 class="fw-bold mb-0 display-6 text-white">{{ $violations->count() }}</h2>
                    </div>
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 54px; height: 54px;">
                        <i class="bx bx-list-check fs-2 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL RIWAYAT -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 ps-md-4" style="width: 50px;">No</th>
                            <th style="min-width: 130px;">Tanggal</th>
                            <th style="min-width: 180px;">Jenis Pelanggaran</th>
                            <th class="text-center" style="width: 90px;">Poin</th>
                            <th style="min-width: 250px;">Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($violations as $index => $violation)
                        <tr>
                            <td class="ps-3 ps-md-4 text-muted font-monospace">{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-semibold text-dark">
                                    {{ \Carbon\Carbon::parse($violation->date)->translatedFormat('d F Y') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 text-wrap text-start fs-7">
                                    {{ $violation->type->name ?? 'Tanpa Keterangan' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger px-2 py-1 fs-7">
                                    +{{ $violation->type->points ?? 0 }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $violation->note ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bx bx-check-shield text-success display-4 d-block mb-2"></i>
                                <span class="fw-bold text-dark d-block">Tidak Ada Catatan Pelanggaran</span>
                                <small>Bagus! Pertahankan kedisiplinan dan tata tertib sekolah.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection