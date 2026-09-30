@extends('layouts.app')

{{-- Import CDN Bootstrap Icons agar ikon pasti muncul --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">

            {{-- Alert Success / Error --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm d-flex align-items-center justify-content-between" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm d-flex align-items-center justify-content-between" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Card Informasi Periode Aktif --}}
            @php
                $activePeriod = isset($activePeriod) ? $activePeriod : $academicYears->firstWhere('is_active', true);
            @endphp
            <div class="card border-0 shadow-sm mb-4 bg-white">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div class="d-flex align-items-center">
                        {{-- Lingkaran Icon Kalender --}}
                        <div class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-calendar-check-fill" viewBox="0 0 16 16">
                                <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4V.5zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zm-5.146-6.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L11 8.707l-1.646 1.647a.5.5 0 0 1-.708-.708l2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-muted small d-block text-uppercase fw-bold" style="letter-spacing: 0.5px;">PERIODE AKTIF SAAT INI</span>
                            @if ($activePeriod)
                                <h6 class="mb-0 fw-bold text-dark fs-6">
                                    Tahun Pelajaran {{ $activePeriod->year }} —
                                    <span class="text-primary">Semester {{ ucfirst($activePeriod->semester) }}</span>
                                </h6>
                            @else
                                <h6 class="mb-0 fw-bold text-danger fs-6">Belum ada Tahun Pelajaran/Semester yang diaktifkan.</h6>
                            @endif
                        </div>
                    </div>
                    @if ($activePeriod)
                        <span class="badge bg-success px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-circle-fill"></i> Aktif
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card Table Data --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">Daftar Tahun Pelajaran & Semester</h5>
                    <a href="{{ route('settings.academic-years.create') }}" class="btn btn-light btn-sm fw-bold shadow-sm text-primary d-inline-flex align-items-center gap-1 px-3 py-2">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Tambah Data</span>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">#</th>
                                    <th>Tahun Pelajaran</th>
                                    <th>Semester</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 200px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($academicYears as $key => $item)
                                    <tr class="{{ $item->is_active ? 'table-success bg-opacity-10' : '' }}">
                                        <td class="text-center fw-bold text-muted">
                                            {{ method_exists($academicYears, 'firstItem') && $academicYears->firstItem() ? $academicYears->firstItem() + $key : $key + 1 }}
                                        </td>
                                        <td class="fw-bold text-dark">{{ $item->year }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark px-2 py-1">
                                                Semester {{ ucfirst($item->semester) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($item->is_active)
                                                <span class="badge bg-success px-3 py-2 d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-check-circle-fill"></i> Aktif
                                                </span>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                {{-- Tombol Edit dengan Icon --}}
                                                <a href="{{ route('settings.academic-years.edit', $item->id) }}"
                                                   class="btn btn-warning btn-sm text-white fw-bold d-inline-flex align-items-center gap-1 px-3 py-1-5"
                                                   title="Edit Data">
                                                    <i class="bi bi-pencil-square"></i>
                                                    <span>Edit</span>
                                                </a>

                                                {{-- Tombol Hapus dengan Icon --}}
                                                <form action="{{ route('settings.academic-years.destroy', $item->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger btn-sm fw-bold d-inline-flex align-items-center gap-1 px-3 py-1-5"
                                                            title="Hapus Data">
                                                        <i class="bi bi-trash-fill"></i>
                                                        <span>Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                            Belum ada data Tahun Pelajaran & Semester.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                @if (method_exists($academicYears, 'links') && $academicYears->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-end">
                            {{ $academicYears->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
