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
                        <div class="card-body p-4 text-center">
                            <i class="fas fa-cloud-download-alt fa-4x text-primary mb-3"></i>
                            <h4 class="fw-bold">Unduh Database</h4>
                            <p class="text-muted small mb-3">
                                Sistem saat ini menggunakan database: <span class="badge bg-info text-dark">{{ config('database.default') }}</span>.
                            </p>
                            
                            <form action="{{ route('database.backup') }}" method="POST">
                                @csrf
                                
                                <!-- Opsi Pilihan Backup -->
                                <div class="card bg-light border-0 p-3 mb-4 text-start">
                                    <label class="form-label fw-bold mb-2"><i class="fas fa-cog me-1"></i> Pilih Opsi Cadangan:</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="include_files" id="without_files" value="0" checked>
                                        <label class="form-check-label" for="without_files">
                                            <strong>Hanya Database (.sql / .sqlite)</strong>
                                            <div class="text-muted small">Proses cepat, hanya mengunduh data tabel database saja.</div>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="include_files" id="with_files" value="1">
                                        <label class="form-check-label" for="with_files">
                                            <strong>Database + Gambar & File (.zip)</strong>
                                            <div class="text-muted small">Lengkap mencakup file database serta seluruh foto & file unggahan sistem.</div>
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 shadow">
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
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="fas fa-history fa-4x text-danger mb-3"></i>
                                <h4 class="fw-bold">Pulihkan Database</h4>
                                <p class="text-muted text-start small mb-0">
                                    <strong class="text-danger">PERINGATAN:</strong> Tindakan ini akan menimpa seluruh data yang ada saat ini dengan data dari file backup. Pastikan Anda yakin sebelum melanjutkan.
                                </p>
                            </div>

                            <form action="{{ route('database.restore') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="backup_file" class="form-label fw-bold">Pilih File Backup (.sql / .sqlite / .zip)</label>
                                    <input class="form-control @error('backup_file') is-invalid @enderror" type="file" id="backup_file" name="backup_file" accept=".sql,.sqlite,.zip" required>
                                    
                                    @error('backup_file')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
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