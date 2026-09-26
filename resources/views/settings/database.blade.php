@section('title', 'Backup & Restore Database')

<x-app-layout>
    <div class="page-content">
        <div class="container py-4">
            
            <div class="mb-4">
                <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-database me-2"></i> Pengaturan Database</h4>
                <p class="text-muted small mb-0">Kelola cadangan dan pemulihan data sistem.</p>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- CARD BACKUP -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow border-0 h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-download me-2"></i> Backup Data</h5>
                        </div>
                        <div class="card-body text-center p-5">
                            <i class="fas fa-cloud-download-alt fa-4x text-primary mb-3"></i>
                            <h4 class="fw-bold">Unduh Database</h4>
                            <p class="text-muted">
                                Sistem saat ini menggunakan database: <span class="badge bg-info text-dark">{{ config('database.default') }}</span>.
                                <br>Klik tombol di bawah untuk mengunduh file cadangan terbaru.
                            </p>
                            
                            <form action="{{ route('database.backup') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                    <i class="fas fa-file-export me-2"></i> Download Backup
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- CARD RESTORE -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow border-0 h-100">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-upload me-2"></i> Restore Data</h5>
                        </div>
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-history fa-4x text-danger mb-3"></i>
                                <h4 class="fw-bold">Pulihkan Database</h4>
                                <p class="text-muted text-start small">
                                    <strong class="text-danger">PERINGATAN:</strong> Tindakan ini akan menimpa seluruh data yang ada saat ini dengan data dari file backup. Pastikan Anda yakin sebelum melanjutkan.
                                </p>
                            </div>

                            <form action="{{ route('database.restore') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="backup_file" class="form-label fw-bold">Pilih File Backup (.sql / .sqlite)</label>
                                    <input class="form-control" type="file" id="backup_file" name="backup_file" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger btn-lg shadow" onclick="return confirm('Apakah Anda yakin ingin memulihkan database? Data saat ini akan ditimpa/hilang!');">
                                        <i class="fas fa-trash-restore me-2"></i> Restore Database
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>