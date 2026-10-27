@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- HEADER HALAMAN -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Pengaturan Otomatisasi Alpa Kelas</h3>
            <p class="text-muted small mb-0">Aktifkan atau nonaktifkan pencatatan alpa otomatis untuk tiap kelas secara fleksibel.</p>
        </div>
    </div>

    <!-- CARD UTAMA -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 60px;">No</th>
                            <th>Nama Kelas</th>
                            <th>Jurusan</th>
                            <th class="text-center" style="width: 220px;">Status Otomatisasi Alpa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classrooms as $index => $classroom)
                        <tr>
                            <td class="ps-4 text-muted font-monospace">{{ $index + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $classroom->name }}</td>
                            <td class="text-muted">
                                {{ $classroom->major->name ?? $classroom->major->title ?? $classroom->major->code ?? '-' }}
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input toggle-auto-absent" 
                                           type="checkbox" 
                                           role="switch"
                                           data-id="{{ $classroom->id }}"
                                           style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                           {{ $classroom->is_auto_absent_enabled ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bx bx-info-circle fs-3 d-block mb-2"></i>
                                Tidak ada data kelas yang dapat dikelola atau belum dikonfigurasi oleh Admin.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SWEETALERT2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JAVASCRIPT LOGIC -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Konfigurasi Toast SweetAlert2
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // 2. Event Handler Toggle Switch
    document.querySelectorAll('.toggle-auto-absent').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const classroomId = this.dataset.id;
            const status = this.checked;

            fetch(`/settings/auto-absent-classrooms/${classroomId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan pada server');
                }
                return data;
            })
            .then(data => {
                if (data.status === 'success') {
                    // Notifikasi Toast Berhasil
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Status otomatisasi berhasil diperbarui'
                    });
                } else {
                    throw new Error(data.message || 'Gagal mengubah status');
                }
            })
            .catch(err => {
                // Revert posisi toggle ke posisi semula jika gagal
                this.checked = !status;

                // Popup Modal Error
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memperbarui',
                    text: err.message || 'Terjadi kesalahan sistem atau Anda tidak memiliki akses.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Tutup'
                });
            });
        });
    });
});
</script>
@endsection