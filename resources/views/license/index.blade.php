@extends('layouts.app')

@section('title', 'Aktivasi & Informasi Lisensi')

@section('content')
<div class="page-content container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">
            
            <!-- Card Utama -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-primary text-white text-center py-4">
                    <div class="bg-white bg-opacity-25 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                        <i class="fas fa-shield-alt fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Aktivasi Lisensi Aplikasi</h4>
                    <p class="small text-white-50 mb-0 mt-1">Kelola lisensi & status keamanan Sistem Presensi Sekolah</p>
                </div>

                <div class="card-body p-4">
                    
                    <!-- Widget Status Lisensi Saat Ini -->
                    @if(isset($check) && $check['status'])
                        <div class="alert alert-success border-0 bg-success-subtle text-success-emphasis rounded-3 p-3 mb-4 d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Status: Lisensi Aktif</h6>
                                <p class="small mb-0">Terdaftar atas nama <strong>{{ $check['client_name'] ?? '-' }}</strong> hingga <strong>{{ \Carbon\Carbon::parse($check['expires_at'])->translatedFormat('d F Y (H:i)') }}</strong>.</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis rounded-3 p-3 mb-4 d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Status: Belum Teraktivasi / Kadaluarsa</h6>
                                <p class="small mb-0">{{ $check['message'] ?? 'Aplikasi memerlukan file lisensi (.lic) yang sah untuk digunakan.' }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Box Server Signature -->
                    <div class="mb-4">
                        <label class="form-label text-dark fw-bold small text-uppercase">
                            <i class="fas fa-fingerprint me-1 text-primary"></i> Server Signature Anda
                        </label>
                        <div class="input-group">
                            <input type="text" id="serverSignatureInput" class="form-control bg-light fw-bold text-primary border-end-0" 
                                   value="{{ $serverSignature }}" readonly>
                            <button type="button" class="btn btn-outline-primary fw-semibold" onclick="copySignature()">
                                <i class="fas fa-copy me-1"></i> Salin
                            </button>
                        </div>
                        <div class="form-text text-muted small mt-2">
                            <i class="fas fa-info-circle me-1"></i> Berikan kode signature ini kepada penyedia aplikasi untuk mendapatkan berkas lisensi (<code>.lic</code>).
                        </div>
                    </div>

                    <hr class="my-4 opacity-10">

                    <!-- Form Upload File Lisensi Baru -->
                    <form action="{{ route('license.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label text-dark fw-bold small text-uppercase">
                                <i class="fas fa-file-contract me-1 text-primary"></i> Upload / Perbarui Berkas Lisensi (.lic)
                            </label>
                            <input type="file" name="license_file" class="form-control form-control-lg bg-light" required accept=".lic">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                            <i class="fas fa-key me-2"></i> {{ isset($check) && $check['status'] ? 'Perbarui Lisensi' : 'Aktifkan Lisensi Sekarang' }}
                        </button>
                    </form>

                </div>
            </div>

            <!-- Widget Informasi Aplikasi & Lisensi Saat Ini -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-light fw-bold text-dark p-3">
                    <i class="fas fa-info-circle me-1 text-primary"></i> Tentang Aplikasi & Lisensi
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Nama Aplikasi</small>
                                <span class="fw-bold text-dark">Sistem Presensi Sekolah</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Versi Sistem</small>
                                <span class="fw-bold text-dark">v2.1.0 (Laravel {{ app()->version() }})</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Pemegang Lisensi</small>
                                <span class="fw-bold text-primary">{{ $check['client_name'] ?? 'Belum Terdaftar' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Masa Berlaku Sampai</small>
                                <span class="fw-bold {{ isset($check) && $check['status'] ? 'text-success' : 'text-danger' }}">
                                    {{ isset($check['expires_at']) ? \Carbon\Carbon::parse($check['expires_at'])->translatedFormat('d F Y') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-center py-3 text-muted small">
                    &copy; {{ date('Y') }} Sistem Presensi Sekolah. All rights reserved.
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function copySignature() {
    var copyText = document.getElementById("serverSignatureInput");
    navigator.clipboard.writeText(copyText.value);
    
    Swal.fire({
        icon: 'success',
        title: 'Tersalin!',
        text: 'Server Signature berhasil disalin ke clipboard.',
        timer: 2000,
        showConfirmButton: false
    });
}
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        confirmButtonColor: '#0d6efd'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal Aktivasi',
        text: "{{ session('error') }}",
        confirmButtonColor: '#dc3545'
    });
</script>
@endif
@endsection