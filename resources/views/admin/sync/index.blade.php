@extends('layouts.app')

@section('title', 'Pusat Sinkronisasi Data')

@section('content')
<div class="page-content container-fluid py-3">
    <!-- Header Title -->
    <div class="d-flex align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-1">
                <i class="fas fa-sync-alt me-2"></i>Pusat Sinkronisasi Data
            </h3>
            <p class="text-muted mb-0">
                Kelola sinkronisasi dua arah (Mode Server: 
                <span class="badge {{ $serverRole === 'online' ? 'bg-success' : 'bg-primary' }} text-uppercase px-2 py-1">
                    {{ $serverRole }}
                </span>)
            </p>
        </div>
    </div>

    <!-- Card Pengaturan Server & Token -->
    <div class="row mb-4">
        <!-- URL Server Online (Hanya Tampil di Server Lokal) -->
        @if($serverRole === 'local')
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-primary text-white p-3 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-globe fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1">URL Server Online (Cloud)</h5>
                                <p class="text-muted small mb-0">Alamat domain atau IP server pusat tempat data akan disinkronkan.</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.sync.update-url') }}" method="POST" class="mt-3">
                            @csrf
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-link text-muted"></i>
                                </span>
                                <input type="url" name="server_url" class="form-control bg-light border-start-0 fw-bold text-dark" 
                                       value="{{ $serverUrl ?? '' }}" 
                                       placeholder="https://sekolah.sch.id" required>
                                <button type="submit" class="btn btn-primary fw-bold px-3">
                                    <i class="fas fa-save me-1"></i> Simpan URL
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Card API Token Sinkronisasi (Dinamis: Online vs Lokal) -->
        <div class="{{ $serverRole === 'local' ? 'col-md-6' : 'col-md-12' }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-warning text-white p-3 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-key fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">API Token Sinkronisasi</h5>
                            <p class="text-muted small mb-0">
                                @if($serverRole === 'online')
                                    Generate Token API rahasia di Server Online ini lalu berikan ke Admin Server Lokal.
                                @else
                                    Tempelkan Token API yang di-generate dari Server Online di sini.
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($serverRole === 'online')
                        <!-- TAMPILAN DI SERVER ONLINE: Tombol Generate & Copy Token -->
                        <div class="row align-items-center mt-3">
                            <div class="col-md-8 mb-2 mb-md-0">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="text" id="apiTokenInput" class="form-control bg-light border-start-0 border-end-0 fw-bold text-primary" 
                                           value="{{ session('plain_token') ?? 'Klik button di samping untuk generate token baru' }}" 
                                           readonly>
                                    <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="toggleTokenVisibility()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary w-50 fw-semibold" onclick="copyToken()">
                                    <i class="fas fa-copy me-1"></i> Salin
                                </button>
                                
                                <form action="{{ route('admin.sync.generate-token') }}" method="POST" class="w-50">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100 fw-bold text-dark">
                                        <i class="fas fa-sync-alt me-1"></i> Generate
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- TAMPILAN DI SERVER LOKAL: Form Input/Tempel Token -->
                        <form action="{{ route('admin.sync.update-token') }}" method="POST" class="mt-3">
                            @csrf
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-key text-muted"></i>
                                </span>
                                <input type="password" id="apiTokenInput" name="api_token" 
                                       class="form-control bg-light border-start-0 border-end-0 fw-bold text-primary" 
                                       value="{{ $syncToken ?? '' }}" 
                                       placeholder="Tempelkan API Token dari Server Online di sini" required>
                                <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="toggleTokenVisibility()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                                <button type="submit" class="btn btn-warning fw-bold text-dark px-3">
                                    <i class="fas fa-save me-1"></i> Simpan Token
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Sinkronisasi Dua Arah -->
    <div class="row mb-4">
        <!-- Panel Kiri: Lokal ke Online (Push) -->
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-primary mb-0">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Lokal ke Online (Push)
                            </h5>
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                                {{ $totalUnsynced ?? 0 }} Belum Disinkron
                            </span>
                        </div>

                        <p class="text-muted small mb-4">
                            Mengirim data transaksi harian (absensi gerbang, mapel, & pelanggaran) yang dicatat di komputer lokal ke server online.
                        </p>

                        <div class="list-group list-group-flush mb-4">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="text-secondary small">Absensi Harian (Gerbang)</span>
                                <span class="badge bg-secondary rounded-pill px-3 py-1">{{ $unsyncedDaily ?? 0 }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="text-secondary small">Absensi Pembelajaran</span>
                                <span class="badge bg-secondary rounded-pill px-3 py-1">{{ $unsyncedAttendances ?? 0 }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                                <span class="text-secondary small">Pelanggaran Siswa</span>
                                <span class="badge bg-secondary rounded-pill px-3 py-1">{{ $unsyncedViolations ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <form id="formPushData" action="{{ route('admin.sync.push') }}" method="POST">
                        @csrf
                        <button type="button" id="btnPushData" class="btn btn-primary w-100 py-2 fw-bold text-white shadow-sm" {{ ($totalUnsynced ?? 0) == 0 ? 'disabled' : '' }}>
                            <i class="fas fa-cloud-upload-alt me-1"></i> Sinkron Sekarang (Push)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Online ke Lokal (Pull) -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-success mb-0">
                                <i class="fas fa-cloud-download-alt me-2"></i>Online ke Lokal (Pull)
                            </h5>
                            <span class="badge bg-info text-white px-3 py-2 rounded-pill fw-bold">Master Data & Absensi</span>
                        </div>

                        <p class="text-muted small mb-3">
                            Menarik pembaruan data master, transaksi absensi, dan arsip foto presensi dari server online ke database lokal.
                        </p>

                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis p-3 rounded-3 mb-3">
                            <div class="d-flex">
                                <i class="fas fa-info-circle fa-lg me-2 mt-1"></i>
                                <div class="small">
                                    Gunakan tombol <strong>Tarik Foto Presensi</strong> secara berkala untuk memindahkan foto ke server lokal dan menghemat penyimpanan hosting online.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Pull Group -->
                    <div class="d-flex flex-column gap-2 mt-2">
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <!-- Form Pull Master -->
                            <form id="formPullMaster" action="{{ route('admin.sync.pull') }}" method="POST" class="w-100">
                                @csrf
                                <button type="button" id="btnPullMaster" class="btn btn-success fw-bold text-white w-100 py-2">
                                    <i class="fas fa-cloud-download-alt me-1"></i> Tarik Data Master
                                </button>
                            </form>

                            <!-- Form Pull Absensi -->
                            <form id="formPullAttendances" action="{{ route('admin.sync.pull-attendances') }}" method="POST" class="w-100">
                                @csrf
                                <button type="button" id="btnPullAttendances" class="btn btn-warning fw-bold text-white w-100 py-2">
                                    <i class="fas fa-download me-1"></i> Tarik Data Absensi
                                </button>
                            </form>
                        </div>

                        <!-- Form Pull Foto Presensi (Cleanup Hosting) -->
                        <form id="formPullPhotos" action="{{ route('admin.sync.pull-photos') }}" method="POST" class="w-100">
                            @csrf
                            <button type="button" id="btnPullPhotos" class="btn btn-info fw-bold text-white w-100 py-2 shadow-sm">
                                <i class="fas fa-images me-1"></i> Tarik Foto Presensi (Cleanup Hosting)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Sinkronisasi -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-history me-2 text-primary"></i>Riwayat Sinkronisasi Terakhir
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-sm">
                <thead class="bg-light text-uppercase text-secondary">
                    <tr style="font-size: 0.75rem;">
                        <th class="ps-4">Waktu</th>
                        <th>Tipe</th>
                        <th>Objek Data</th>
                        <th class="text-center">Jumlah Data</th>
                        <th class="text-center">Status</th>
                        <th>Catatan / Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs ?? [] as $log)
                        <tr>
                            <td class="ps-4 fw-semibold text-dark">
                                {{ $log->synced_at ? $log->synced_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td>
                                @if($log->type == 'push')
                                    <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                                        <i class="fas fa-arrow-up me-1"></i>Push
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                        <i class="fas fa-arrow-down me-1"></i>Pull
                                    </span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">{{ $log->table_name }}</td>
                            <td class="text-center">{{ $log->records_count }} Record</td>
                            <td class="text-center">
                                @if($log->status == 'success')
                                    <span class="badge bg-success px-2 py-1">Sukses</span>
                                @else
                                    <span class="badge bg-danger px-2 py-1">Gagal</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $log->error_message ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                Belum ada riwayat sinkronisasi data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($logs) && method_exists($logs, 'hasPages') && $logs->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Library SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Toggle Lihat/Sembunyikan Token
function toggleTokenVisibility() {
    var tokenInput = document.getElementById("apiTokenInput");
    var toggleIcon = document.getElementById("toggleIcon");

    if (tokenInput.type === "password") {
        tokenInput.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        tokenInput.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}

// Salin Token
function copyToken() {
    var tokenInput = document.getElementById("apiTokenInput");
    if(tokenInput && tokenInput.value && !tokenInput.value.includes('Klik button')) {
        navigator.clipboard.writeText(tokenInput.value);
        Swal.fire({
            icon: 'success',
            title: 'Tersalin!',
            text: 'API Token berhasil disalin ke clipboard.',
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Belum ada token valid yang dapat disalin.'
        });
    }
}

// -------------------------------------------------------------
// SWEETALERT2: SINKRONISASI PULL DATA MASTER
// -------------------------------------------------------------
document.getElementById('btnPullMaster')?.addEventListener('click', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Tarik Data Master?',
        text: "Sistem akan menarik data siswa, guru, kelas, dan user terbaru dari Server Online.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Tarik Sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Menarik Data Master...',
                html: 'Mohon tunggu, proses sinkronisasi sedang berlangsung.<br><br><div class="progress" style="height: 18px;"><div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%"></div></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            document.getElementById('formPullMaster').submit();
        }
    });
});

// -------------------------------------------------------------
// SWEETALERT2: SINKRONISASI PULL DATA ABSENSI
// -------------------------------------------------------------
document.getElementById('btnPullAttendances')?.addEventListener('click', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Tarik Data Absensi?',
        text: "Sistem akan menarik pembaruan data presensi harian, pembelajaran, dan sholat dari Server Online.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Tarik Sekarang!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'text-white'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Menarik Data Absensi...',
                html: 'Mohon tunggu, proses penarikan data presensi sedang berjalan.<br><br><div class="progress" style="height: 18px;"><div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 100%"></div></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            document.getElementById('formPullAttendances').submit();
        }
    });
});

// -------------------------------------------------------------
// SWEETALERT2: SINKRONISASI PULL FOTO PRESENSI (CLEANUP HOSTING)
// -------------------------------------------------------------
document.getElementById('btnPullPhotos')?.addEventListener('click', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Tarik Foto Presensi & Cleanup Hosting?',
        text: "File foto presensi di server online akan diunduh ke server lokal dan otomatis dihapus dari hosting online untuk mengosongkan ruang penyimpanan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0dcaf0',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Pindahkan Foto!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'text-white'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Memindahkan Berkas Foto...',
                html: 'Mohon tunggu, file foto sedang dikompresi dan dipindahkan ke server lokal.<br><br><div class="progress" style="height: 18px;"><div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 100%"></div></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            document.getElementById('formPullPhotos').submit();
        }
    });
});

// -------------------------------------------------------------
// SWEETALERT2: SINKRONISASI PUSH DATA LOKAL
// -------------------------------------------------------------
document.getElementById('btnPushData')?.addEventListener('click', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Kirim Data ke Online?',
        text: "Data transaksi lokal yang belum disinkronkan akan dikirimkan ke Server Online.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Kirim Sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Mengirim Data...',
                html: 'Mohon tunggu, proses pengiriman data ke server online sedang berjalan.<br><br><div class="progress" style="height: 18px;"><div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            document.getElementById('formPushData').submit();
        }
    });
});
</script>

<!-- Notification Popup dari Controller -->
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        confirmButtonColor: '#0d6efd',
        timer: 4000,
        showConfirmButton: true
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal Sinkronisasi',
        text: "{{ session('error') }}",
        confirmButtonColor: '#dc3545',
        showConfirmButton: true
    });
</script>
@endif
@endsection