@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Monitoring Bot & Notifikasi Telegram</h4>
    </div>

    {{-- Alert Success / Error bawaan session Laravel --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Ringkasan Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 rounded-3">
                <div class="card-body">
                    <div class="text-muted small">Orang Tua Terdaftar</div>
                    <div class="h3 fw-bold mb-0 text-primary">{{ number_format($totalSubscribers) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 rounded-3">
                <div class="card-body">
                    <div class="text-muted small">Berhasil Terkirim</div>
                    <div class="h3 fw-bold text-success mb-0">{{ number_format($totalSentSuccess) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-danger border-4 rounded-3">
                <div class="card-body">
                    <div class="text-muted small">Gagal Terkirim</div>
                    <div class="h3 fw-bold text-danger mb-0">{{ number_format($totalSentFailed) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 rounded-3">
                <div class="card-body">
                    <div class="text-muted small">Notifikasi Hari Ini</div>
                    <div class="h3 fw-bold text-info mb-0">{{ number_format($todaySentCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigasi Tab --}}
    <ul class="nav nav-tabs mb-3" id="telegramTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ !request()->has('sub_page') ? 'active' : '' }} fw-bold" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button">
                Riwayat Pengiriman Pesan
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ request()->has('sub_page') ? 'active' : '' }} fw-bold" id="subscribers-tab" data-bs-toggle="tab" data-bs-target="#subscribers" type="button">
                Daftar Orang Tua Terdaftar ({{ $totalSubscribers }})
            </button>
        </li>
    </ul>

    <div class="tab-content" id="telegramTabContent">
        
        {{-- TAB 1: LOG RIWAYAT NOTIFIKASI --}}
        <div class="tab-pane fade {{ !request()->has('sub_page') ? 'show active' : '' }}" id="logs" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Riwayat Pengiriman Pesan</h6>
                        @if($logs->count() > 0)
                        <form action="{{ route('monitoring.telegram.clearLogs') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" id="btn-clear-all-logs" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash me-1"></i> Bersihkan Semua Log
                            </button>
                        </form>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('monitoring.telegram') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, judul, atau Chat ID..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="type" class="form-select">
                                <option value="">-- Semua Kategori --</option>
                                <option value="absensi" {{ request('type') == 'absensi' ? 'selected' : '' }}>Absensi</option>
                                <option value="ujian" {{ request('type') == 'ujian' ? 'selected' : '' }}>Ujian</option>
                                <option value="pengumuman" {{ request('type') == 'pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                                <option value="pelanggaran" {{ request('type') == 'pelanggaran' ? 'selected' : '' }}>Pelanggaran</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">-- Semua Status --</option>
                                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Penerima</th>
                                <th>Chat ID</th>
                                <th>Kategori</th>
                                <th>Judul & Pesan</th>
                                <th>Status</th>
                                <th>Detail Error</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="small text-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td><strong>{{ $log->recipient_name }}</strong></td>
                                <td><code>{{ $log->chat_id }}</code></td>
                                <td>
                                    @php
                                        $badgeColor = match($log->type) {
                                            'absensi' => 'bg-info',
                                            'ujian' => 'bg-primary',
                                            'pelanggaran' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }}">{{ strtoupper($log->type) }}</span>
                                </td>
                                <td>
                                    <strong>{{ $log->title }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($log->message, 60) }}</small>
                                </td>
                                <td>
                                    @if($log->status == 'success')
                                        <span class="badge bg-success">Success</span>
                                    @elseif($log->status == 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td class="small text-danger">{{ $log->error_log ?? '-' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('monitoring.telegram.destroyLog', $log->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-delete-log" title="Hapus Log">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat notifikasi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $logs->appends(request()->except('logs_page'))->links() }}
                </div>
            </div>
        </div>

        {{-- TAB 2: DAFTAR ORANG TUA TERDAFTAR --}}
        <div class="tab-pane fade {{ request()->has('sub_page') ? 'show active' : '' }}" id="subscribers" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <form method="GET" action="{{ route('monitoring.telegram') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="sub_search" class="form-control" placeholder="Cari nama orang tua, No HP, Chat ID..." value="{{ request('sub_search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Cari</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Orang Tua</th>
                                <th>Siswa Terkait</th>
                                <th>Chat ID / No. HP</th>
                                <th>Status Approval</th>
                                <th class="text-center">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscribers as $index => $sub)
                            <tr>
                                <td>{{ $subscribers->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $sub->parent_name }}</strong><br>
                                    <small class="text-muted">{{ $sub->username ? '@'.$sub->username : '-' }}</small>
                                </td>
                                <td>
                                    @if($sub->student)
                                        <span class="fw-bold">{{ $sub->student->name }}</span><br>
                                        <small class="text-muted">{{ $sub->student->classroom->name ?? '-' }}</small>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Dihubungkan</span>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $sub->chat_id }}</code><br>
                                    <small class="text-muted">{{ $sub->phone_number ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($sub->approval_status == 'approved')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif($sub->approval_status == 'pending')
                                        <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                                    @else
                                        <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($sub->approval_status == 'pending')
                                        <!-- Tombol Setujui (Buka Modal) -->
                                        <button type="button" class="btn btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $sub->id }}">
                                            <i class="bi bi-check-circle"></i> Setujui
                                        </button>
                                        
                                        <!-- Tombol Tolak -->
                                        <form action="{{ route('monitoring.telegram.rejectSubscriber', $sub->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tolak pendaftaran akun Telegram ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-x-circle"></i> Tolak
                                            </button>
                                        </form>

                                        <!-- MODAL PILIH SISWA UNTUK APPROVAL -->
                                        <div class="modal fade text-start" id="approveModal{{ $sub->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('monitoring.telegram.approveSubscriber', $sub->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Pilih Siswa untuk {{ $sub->parent_name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="small text-muted mb-3">
                                                                Nomor HP Telegram ini (<code>{{ $sub->phone_number }}</code>) belum terdaftar di database. Silakan pilih siswa terkait untuk menyetujui pendaftaran.
                                                            </p>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Pilih Siswa:</label>
                                                                <select name="student_id" class="form-select" required>
                                                                    <option value="">-- Cari & Pilih Nama Siswa --</option>
                                                                    @foreach(\App\Models\Student::with('classroom')->orderBy('name')->get() as $std)
                                                                        <option value="{{ $std->id }}">{{ $std->name }} ({{ $std->classroom->name ?? '-' }})</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success">Setujui & Hubungkan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada orang tua yang terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $subscribers->appends(request()->except('sub_page'))->links() }}
                </div>
            </div>
        </div>

    </div>
</div>

{{-- SweetAlert2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Hapus Single Log
        document.querySelectorAll('.btn-delete-log').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Hapus Log Pesan?',
                    text: "Data riwayat log ini akan dihapus dari sistem!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // 2. Bersihkan Semua Log
        const btnClearAll = document.getElementById('btn-clear-all-logs');
        if (btnClearAll) {
            btnClearAll.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Bersihkan Semua Log?',
                    text: "Seluruh riwayat pengiriman pesan akan dihapus dan tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Bersihkan Semua!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endsection