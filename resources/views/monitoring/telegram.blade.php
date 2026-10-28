@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Monitoring Bot & Notifikasi Telegram</h4>
    </div>

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
                        <form action="{{ route('monitoring.telegram.clearLogs') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SELURUH riwayat pesan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
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
                                    <form action="{{ route('monitoring.telegram.destroyLog', $log->id) }}" method="POST" onsubmit="return confirm('Hapus log pesan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm border-0" title="Hapus Log">
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
                                <th>Username Telegram</th>
                                <th>Chat ID</th>
                                <th>No. HP</th>
                                <th>Status Bot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscribers as $index => $sub)
                            <tr>
                                <td>{{ $subscribers->firstItem() + $index }}</td>
                                <td><strong>{{ $sub->parent_name }}</strong></td>
                                <td>{{ $sub->student->name ?? '-' }}</td>
                                <td>{{ $sub->username ? '@'.$sub->username : '-' }}</td>
                                <td><code>{{ $sub->chat_id }}</code></td>
                                <td>{{ $sub->phone_number ?? '-' }}</td>
                                <td>
                                    @if($sub->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada orang tua yang terdaftar.</td>
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
@endsection