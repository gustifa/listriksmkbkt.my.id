@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Halaman -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-mobile-alt text-primary me-2"></i> Pengaturan Kunci Perangkat (HP) Siswa</h3>
            <p class="text-muted small mb-0">Kelola aturan penguncian perangkat HP siswa untuk mencegah kecurangan absensi.</p>
        </div>
    </div>

    <!-- Alert Notifikasi Berhasil -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- CARD 1: FORM PENGATURAN GLOBAL -->
    <div class="card shadow-sm rounded-4 border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-sliders-h me-2"></i> Konfigurasi Sistem</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.device-settings.update') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <!-- Switch / Select Status Kunci HP -->
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-dark">Status Kunci Perangkat</label>
                        <select name="enable_device_lock" class="form-select form-select-lg rounded-3 fs-6">
                            <option value="1" {{ ($settings['enable_device_lock'] ?? '0') == '1' ? 'selected' : '' }}>
                                🔒 AKTIF (Siswa Terkunci pada HP Terdaftar)
                            </option>
                            <option value="0" {{ ($settings['enable_device_lock'] ?? '0') == '0' ? 'selected' : '' }}>
                                🔓 NON-AKTIF (Siswa Bebas Login dari HP Manapun)
                            </option>
                        </select>
                    </div>

                    <!-- Select Batas Perangkat -->
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-dark">Batas Maksimal HP per Siswa</label>
                        <select name="max_allowed_devices" class="form-select form-select-lg rounded-3 fs-6">
                            <option value="1" {{ ($settings['max_allowed_devices'] ?? '1') == '1' ? 'selected' : '' }}>
                                📱 1 Perangkat (Hanya 1 HP Utama)
                            </option>
                            <option value="2" {{ ($settings['max_allowed_devices'] ?? '1') == '2' ? 'selected' : '' }}>
                                📱📱 2 Perangkat (Toleransi 2 HP/Gadget)
                            </option>
                        </select>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 fs-6">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- CARD 2: TABEL DAFTAR SISWA & PERANGKAT TERIKAT -->
    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-users-cog me-2"></i> Daftar Perangkat Terikat Siswa</h5>
            
            <!-- Form Pencarian -->
            <form action="{{ route('admin.device-settings.index') }}" method="GET" class="d-flex gap-2">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    @if(request('search'))
                        <a href="{{ route('admin.device-settings.index') }}" class="btn btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="25%">Siswa</th>
                            <th width="25%">Email</th>
                            <th width="15%" class="text-center">HP Pertama</th>
                            <th width="15%" class="text-center">HP Kedua</th>
                            <th width="15%" class="text-center">Aksi / Reset</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <!-- Nomor Pagination Kontinu -->
                                <td class="text-center fw-bold text-muted">
                                    {{ $students->firstItem() +$loop->index }}
                                </td>

                                <!-- Nama Siswa -->
                                <td class="fw-bold text-dark">
                                    {{ $student->name }}
                                </td>

                                <!-- Email Siswa -->
                                <td class="text-muted">
                                    {{ $student->email }}
                                </td>

                                <!-- Status Perangkat 1 -->
                                <td class="text-center">
                                    @if($student->device_id_1)
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                            <i class="fas fa-mobile-alt me-1"></i> Terikat
                                        </span>
                                        <div class="mt-1">
                                            <code class="text-muted small" title="{{ $student->device_id_1 }}">
                                                {{ Str::limit($student->device_id_1, 10, '...') }}
                                            </code>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                                            Belum Ada
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Perangkat 2 -->
                                <td class="text-center">
                                    @if($student->device_id_2)
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                            <i class="fas fa-mobile-alt me-1"></i> Terikat
                                        </span>
                                        <div class="mt-1">
                                            <code class="text-muted small" title="{{ $student->device_id_2 }}">
                                                {{ Str::limit($student->device_id_2, 10, '...') }}
                                            </code>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                                            Belum Ada
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi Reset HP -->
                                <td class="text-center">
                                    @if($student->device_id_1 || $student->device_id_2)
                                        <button type="button" 
                                                class="btn btn-danger btn-sm rounded-3 px-3 btn-reset-device"
                                                data-url="{{ route('admin.device-settings.reset', $student->id) }}"
                                                data-nama="{{ $student->name }}">
                                            <i class="fas fa-redo-alt me-1"></i> Reset HP
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-light border text-muted px-3" disabled>
                                            <i class="fas fa-check-circle me-1 text-muted"></i> Bersih
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                    Tidak ada data siswa ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Footer -->
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan {{ $students->firstItem() ?? 0 }} sampai {{ $students->lastItem() ?? 0 }} dari {{$students->total() }} siswa
                </div>
                <div>
                    {{ $students->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    // Handling AJAX Reset Perangkat Siswa
    $(document).on('click', '.btn-reset-device', function (e) {
        e.preventDefault();
        
        const resetUrl = $(this).data('url');
        const studentName = $(this).data('nama');

        Swal.fire({
            title: 'Reset Perangkat HP?',
            html: `Apakah Anda yakin ingin menghapus tautan HP milik <strong>${studentName}</strong>?<br><small class="text-muted">*Siswa akan diizinkan mendaftarkan HP baru pada saat login berikutnya.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-redo-alt me-1"></i> Ya, Reset HP!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan Swal Loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang me-reset data perangkat siswa',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                // Eksekusi AJAX Reset
                $.ajax({
                    url: resetUrl,
                    type: 'POST',
                    data: {
                        slot: 'all' // Reset seluruh slot perangkat siswa
                    },
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
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'rounded-4'
                                }
                            }).then(() => {
                                location.reload(); // Refresh halaman agar badge status diperbarui
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
@endsection