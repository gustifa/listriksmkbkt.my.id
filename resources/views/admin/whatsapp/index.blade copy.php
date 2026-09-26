@section('title', 'WhatsApp Multi-Gateway')

<x-app-layout>
    <div class="page-content">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 text-primary fw-bold"><i class="fab fa-whatsapp me-2"></i> WhatsApp Gateway Manager</h4>
                <p class="text-muted small mb-0">Kelola banyak nomor WhatsApp untuk notifikasi sekolah.</p>
            </div>
            
            <form action="{{ route('whatsapp.store') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success shadow-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Gateway Baru
                </button>
            </form>
        </div>

        <!-- Alert Success -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <!-- Alert Error (Session) -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Alert Validation Errors (NEW - FIX) -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-exclamation-circle me-2"></i> <strong>Perhatian!</strong>
                </div>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- List Gateway Cards -->
        <div class="row">
            @forelse($gateways as $gw)
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card shadow-sm h-100 border-top border-4 {{ $gw->status == 'connected' ? 'border-success' : 'border-warning' }}">
                        <div class="card-body text-center p-4">
                            
                            <!-- Icon Status -->
                            <div class="mb-3 position-relative d-inline-block">
                                <div class="bg-light p-3 rounded-circle">
                                    <i class="fab fa-whatsapp fa-3x {{ $gw->status == 'connected' ? 'text-success' : 'text-warning' }}"></i>
                                </div>
                                @if($gw->status == 'connected')
                                    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle"></span>
                                @else
                                    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle animate-pulse"></span>
                                @endif
                            </div>

                            <h5 class="fw-bold mb-1">{{ $gw->name }}</h5>
                            <p class="text-muted font-monospace small mb-3">
                                {{ $gw->number ?? 'Belum Terhubung' }}
                                <br>
                                <span class="badge bg-light text-secondary border mt-1">ID: {{ $gw->session_id }}</span>
                            </p>

                            <!-- Status Badge -->
                            <div class="mb-4">
                                @if($gw->status == 'connected')
                                    <span class="badge bg-success px-3 py-2 rounded-pill">
                                        <i class="fas fa-check me-1"></i> TERHUBUNG
                                    </span>
                                @elseif($gw->status == 'scan_needed')
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill animate-pulse">
                                        <i class="fas fa-qrcode me-1"></i> BUTUH SCAN
                                    </span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2 rounded-pill">
                                        <i class="fas fa-power-off me-1"></i> DISCONNECTED
                                    </span>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <a href="{{ route('whatsapp.scan', $gw->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-qrcode me-1"></i> Scan / Cek Status
                                </a>
                                
                                <form action="{{ route('whatsapp.destroy', $gw->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus gateway ini? Sesi WhatsApp akan diputus.');">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fas fa-trash me-1"></i> Hapus Gateway
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <img src="https://img.icons8.com/ios/100/cccccc/whatsapp.png" width="80" class="mb-3 opacity-50">
                            <h5 class="text-muted">Belum ada gateway terdaftar.</h5>
                            <p class="text-muted small">Klik tombol "Tambah Gateway Baru" di pojok kanan atas untuk mulai menghubungkan nomor.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        
        <!-- Info Panel -->
        <div class="alert alert-info border-0 shadow-sm mt-3 d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x me-3 opacity-50"></i>
            <div>
                <h6 class="fw-bold mb-1">Cara Kerja Multi-Gateway</h6>
                <p class="mb-0 small">
                    Sistem akan menggunakan semua gateway yang berstatus <strong>TERHUBUNG</strong> secara bergantian (Load Balancing) saat mengirim pesan notifikasi massal. 
                    Jika salah satu nomor terblokir atau mati, sistem otomatis menggunakan nomor lain yang tersedia.
                </p>
            </div>
        </div>

    </div>

    <style>
        .animate-pulse { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; }
        }
    </style>
</x-app-layout>