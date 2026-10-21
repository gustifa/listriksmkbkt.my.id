@extends('layouts.app')

@section('title', 'Aktivasi Lisensi Aplikasi')

@section('content')
<div class="page-content container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <!-- Card Utama -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <div class="bg-white bg-opacity-25 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                        <i class="fas fa-shield-alt fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Aktivasi Lisensi Aplikasi</h4>
                    <p class="small text-white-50 mb-0 mt-1">Masukkan berkas lisensi sah untuk mengaktifkan seluruh fitur sistem</p>
                </div>

                <div class="card-body p-4">
                    
                    <!-- Alert Status Lisensi -->
                    @if(isset($check) && !$check['status'])
                        <div class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis d-flex align-items-center rounded-3 p-3 mb-4">
                            <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
                            <div class="small">
                                <strong>Peringatan:</strong> {{ $check['message'] }}
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
                            <i class="fas fa-info-circle me-1"></i> Salin kode signature di atas dan berikan kepada penyedia aplikasi untuk mendapatkan berkas lisensi (<code>.lic</code>).
                        </div>
                    </div>

                    <hr class="my-4 opacity-10">

                    <!-- Form Upload File Lisensi -->
                    <form action="{{ route('license.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label text-dark fw-bold small text-uppercase">
                                <i class="fas fa-file-contract me-1 text-primary"></i> Upload Berkas Lisensi (.lic)
                            </label>
                            <input type="file" name="license_file" class="form-control form-control-lg bg-light" required accept=".lic">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                            <i class="fas fa-key me-2"></i> Aktifkan Lisensi Sekarang
                        </button>
                    </form>

                </div>

                <div class="card-footer bg-light text-center py-3 text-muted small">
                    &copy; {{ date('Y') }} Sistem Presensi Sekolah. All rights reserved.
                </div>
            </div>

        </div>
    </div>
</div>

<!-- SweetAlert2 & Script Salin -->
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