@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="fas fa-history me-2"></i>Histori & Log Aktivitas Pengguna
            </h4>
            <p class="text-muted small mb-0">Rekam jejak seluruh tindakan dan perubahan data dalam sistem SISFO.</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.activity_logs.index') }}" method="GET" class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="date" class="form-control rounded-3" value="{{ request('date') }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold">Kategori Aktivitas</label>
                    <select name="log_name" class="form-select rounded-3">
                        <option value="">-- Semua Kategori --</option>
                        <option value="Presensi Gerbang" {{ request('log_name') == 'Presensi Gerbang' ? 'selected' : '' }}>Presensi Gerbang</option>
                        <option value="Presensi Sholat" {{ request('log_name') == 'Presensi Sholat' ? 'selected' : '' }}>Presensi Sholat</option>
                        <option value="Presensi Mapel" {{ request('log_name') == 'Presensi Mapel' ? 'selected' : '' }}>Presensi Mapel</option>
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-search me-1"></i> Filter
    </button>
    <a href="{{ route('admin.activity_logs.index') }}" class="btn btn-secondary">Reset</a>
    
    <!-- Tempelkan tombol di sini -->
    <button type="button" class="btn btn-danger btn-clear-log">
        <i class="fas fa-trash-alt me-1"></i> Kosongkan Semua Log
    </button>
</div>
            </form>
        </div>
    </div>

    <!-- Tabel Log -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Waktu</th>
                            <th>Pengguna (Aktor)</th>
                            <th>Kategori</th>
                            <th>Keterangan Aktivitas</th>
                            <th>Detail Properti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <!-- 2. Tambahkan Kolom Nomor yang Memperhitungkan Pagination -->
                                <td class="text-center fw-bold">
                                    {{ $logs->firstItem() + $loop->index }}
                                </td>
                                <td class="text-nowrap">
                                    <small class="fw-bold d-block">{{ $log->created_at->format('d/m/Y H:i:s') }}</small>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $log->causer->name ?? 'Sistem/Guest' }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark rounded-pill px-3">{{ $log->log_name ?? 'General' }}</span>
                                </td>
                                <td>{{ $log->description }}</td>
                                <td>
                                    <small class="text-muted">{{ json_encode($log->properties) }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat aktivitas tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        // Tempelkan kode JS / AJAX di sini
        $(document).on('click', '.btn-clear-log', function (e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Kosongkan Semua Log?',
                text: 'Seluruh riwayat aktivitas sistem akan dihapus permanen dan tidak bisa dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Kosongkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menghapus seluruh log',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: "{{ route('admin.activity-logs.clear') }}",
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan pada sistem.';
                            Swal.fire('Error!', errorMsg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush