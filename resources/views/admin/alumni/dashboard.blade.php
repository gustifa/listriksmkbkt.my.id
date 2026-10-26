@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Banner Selamat Datang -->
    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white mb-4 overflow-hidden" style="background: linear-gradient(135deg, #0d6efd, #0a58ca);">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 mb-3 fw-semibold">Portal Tracer Study Alumni</span>
                    <h3 class="fw-bold mb-2">Selamat Datang Kembali, {{ Auth::user()->name }}!</h3>
                    <p class="mb-0 opacity-75">Bantu sekolah memetakan keberhasilan alumni dengan terus memperbarui data status kerja dan portofolio kompetensimu.</p>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0">
                    <a href="{{ route('alumni.my-profile') }}" class="btn btn-light rounded-pill px-4 fw-bold text-primary shadow-sm">
                        <i class="bx bx-edit me-1"></i> Update Data Saya
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Status Penyerapan Kerja Saat Ini -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-user-circle text-primary me-2"></i>Status Penyerapan Kamu</h6>
                    <span class="badge bg-light text-primary border">Tracer Study</span>
                </div>
                <div class="card-body p-4 text-center">
                    @php
                        $profile = Auth::user()->student->alumniProfile ?? null;
                        $statusText = [
                            'working' => 'Bekerja',
                            'studying' => 'Kuliah / Studi Lanjut',
                            'entrepreneur' => 'Wirausaha',
                            'searching' => 'Mencari Kerja'
                        ][$profile->current_status ?? 'searching'] ?? 'Belum Diisi';

                        $statusBadgeClass = [
                            'working' => 'bg-success',
                            'studying' => 'bg-info text-dark',
                            'entrepreneur' => 'bg-warning text-dark',
                            'searching' => 'bg-secondary'
                        ][$profile->current_status ?? 'searching'] ?? 'bg-secondary';
                    @endphp

                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="bx bx-briefcase-alt-2 fs-1 text-primary"></i>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">{{ $profile->current_company ?? 'Belum ada data tempat kerja/kampus' }}</h5>
                    <p class="text-muted small mb-3">{{ $profile->position ?? 'Perbarui jabatan/program studi Anda' }}</p>

                    <span class="badge {{ $statusBadgeClass }} rounded-pill px-3 py-2 fs-6 mb-3">{{ $statusText }}</span>

                    <hr class="my-3">

                    <div class="d-flex justify-content-around text-start">
                        <div>
                            <small class="text-muted d-block">Tahun Lulus</small>
                            <span class="fw-bold text-dark">{{ $profile->graduation_year ?? '-' }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">File CV</small>
                            @if(!empty($profile->cv_file))
                                <a href="{{ asset('storage/'.$profile->cv_file) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                    <i class="bx bx-file me-1"></i>Lihat CV
                                </a>
                            @else
                                <span class="text-danger small">Belum Upload</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Lowongan Kerja BKK Terbaru -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-briefcase text-primary me-2"></i>Lowongan Kerja BKK Terbaru</h6>
                    <a href="{{ route('alumni.portal.jobs') }}" class="text-primary text-decoration-none small fw-bold">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <!-- Contoh Data Lowongan Kerja BKK -->
                        <div class="list-group-item p-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-light p-2 text-center border" style="width: 50px; height: 50px;">
                                    <i class="bx bx-building fs-2 text-secondary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Teknisi Otomasi Industri</h6>
                                    <small class="text-muted">PT. PLN (Persero) - Bukittinggi</small>
                                    <div class="mt-1">
                                        <span class="badge bg-light text-dark border me-1">TITL</span>
                                        <span class="badge bg-light text-dark border">Full Time</span>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3">Lamar / Detail</button>
                        </div>

                        <div class="list-group-item p-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-light p-2 text-center border" style="width: 50px; height: 50px;">
                                    <i class="bx bx-building fs-2 text-secondary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Junior Maintenance Staff</h6>
                                    <small class="text-muted">PT. Semen Padang</small>
                                    <div class="mt-1">
                                        <span class="badge bg-light text-dark border me-1">Semua Jurusan</span>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3">Lamar / Detail</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection