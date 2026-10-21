@extends('layouts.app')

@section('title', 'Generator Lisensi Aplikasi')

@section('content')
<div class="page-content container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white p-3 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-key fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1 text-white">Generator Lisensi Klien</h5>
                            <p class="text-white-50 small mb-0">Buat file lisensi (.lic) sah untuk pembeli aplikasi Anda</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.license-generator.generate') }}" method="POST">
                        @csrf
                        
                        <!-- Nama Klien / Instansi -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase">
                                <i class="fas fa-building me-1 text-primary"></i> Nama Klien / Sekolah
                            </label>
                            <input type="text" name="client_name" class="form-control bg-light" 
                                   placeholder="Contoh: SMK Negeri 1 Bukittinggi" value="{{ old('client_name') }}" required>
                        </div>

                        <!-- Server Signature Klien -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase">
                                <i class="fas fa-fingerprint me-1 text-primary"></i> Server Signature Klien
                            </label>
                            <input type="text" name="server_signature" class="form-control bg-light fw-bold text-uppercase" 
                                   placeholder="Contoh: 73CAF30E797802287D79B99A656FCA96" value="{{ old('server_signature') }}" required>
                            <div class="form-text small text-muted">
                                Tempelkan kode Server Signature yang didapatkan dari halaman <code>/license</code> pada aplikasi klien.
                            </div>
                        </div>

                        <!-- Masa Berlaku Lisensi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase">
                                <i class="fas fa-calendar-alt me-1 text-primary"></i> Masa Berlaku Sampai
                            </label>
                            <input type="date" name="expires_at" class="form-control bg-light" 
                                   value="{{ old('expires_at', date('Y-m-d', strtotime('+1 year'))) }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                            <i class="fas fa-download me-2"></i> Generate & Unduh File (.lic)
                        </button>
                    </form>
                </div>

                <div class="card-footer bg-light p-3 text-center text-muted small">
                    <i class="fas fa-lock me-1"></i> File <code>.lic</code> yang diunduh siap dikirimkan langsung ke klien.
                </div>
            </div>

        </div>
    </div>
</div>
@endsection