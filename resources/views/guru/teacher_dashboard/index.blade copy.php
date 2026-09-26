@section('title', 'Dashboard Guru')

<x-app-layout>
    <div class="page-content">
        
        <!-- Header Sambutan -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-chalkboard-teacher me-2"></i> Ruang Guru
                </h4>
                <p class="text-muted mb-0">Selamat Datang, <strong>{{ $teacher->name }}</strong> ({{ $teacher->nip ?? 'NIP. -' }})</p>
            </div>
            <div>
                <span class="badge bg-info text-dark">
                    <i class="fas fa-calendar-alt me-1"></i> Tahun Ajaran Aktif
                </span>
            </div>
        </div>

        <!-- Daftar Jadwal Mengajar -->
        <div class="row">
            @forelse($assignments as $schedule)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 border-start border-4 border-primary hover-scale">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $schedule->classroom->name }}</h5>
                                    <span class="badge bg-secondary">{{ $schedule->classroom->major->code ?? 'Umum' }}</span>
                                </div>
                                <div class="bg-light p-2 rounded-circle text-primary">
                                    <i class="fas fa-book-open fa-lg"></i>
                                </div>
                            </div>
                            
                            <h6 class="text-dark fw-bold">{{ $schedule->subject->name }}</h6>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-code-branch me-1"></i> Kode: {{ $schedule->subject->code ?? '-' }}
                            </p>

                            <hr class="my-3">

                            <p class="small text-muted mb-2 fw-bold">Menu Pembelajaran:</p>

                            <div class="d-grid gap-2">
                                <!-- 1. Fitur Membuat/Mengatur Jadwal -->
                                <!-- Anda bisa menghubungkan ini ke route create schedule nanti -->
                                <a href="#" class="btn btn-outline-primary btn-sm text-start">
                                    <i class="fas fa-calendar-plus me-2"></i> Buat/Atur Jadwal
                                </a>

                                <!-- 2. Fitur Absensi dengan 3 Opsi (Manual, QR, Face) -->
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-check me-1"></i> Mulai Absensi
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li><h6 class="dropdown-header">Pilih Metode:</h6></li>
                                        <!-- Absensi Manual (Checklist) -->
                                        <li>
                                            <a class="dropdown-item" href="#">
                                                <i class="fas fa-clipboard-list me-2 text-secondary"></i> Input Manual
                                            </a>
                                        </li>
                                        <!-- Absensi QR Code -->
                                        <li>
                                            <a class="dropdown-item" href="#">
                                                <i class="fas fa-qrcode me-2 text-dark"></i> Scan QR Code
                                            </a>
                                        </li>
                                        <!-- Absensi Wajah -->
                                        <li>
                                            <a class="dropdown-item" href="#">
                                                <i class="fas fa-camera me-2 text-primary"></i> Face Recognition
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                
                                <a href="#" class="btn btn-outline-secondary btn-sm text-start">
                                    <i class="fas fa-history me-2"></i> Riwayat & Jurnal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center py-5">
                        <img src="https://img.icons8.com/ios/100/cccccc/empty-box.png" class="mb-3 opacity-50" width="80">
                        <h5 class="fw-bold text-muted">Belum Ada Jadwal</h5>
                        <p class="mb-0">Anda belum memiliki jadwal mengajar yang dipetakan (mapping).<br>Silakan hubungi Kurikulum/Admin.</p>
                    </div>
                </div>
            @endforelse
        </div>

    </div>

    <style>
        .hover-scale { transition: transform 0.2s; }
        .hover-scale:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    </style>
</x-app-layout>