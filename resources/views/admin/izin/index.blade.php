<x-app-layout>
    <!-- CSRF Token Meta untuk AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CDN Bootstrap Icons & SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="container-fluid py-4">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i> Kelola Approval Izin & Sakit Siswa</h5>
            </div>
            <div class="card-body">

                <!-- Filter -->
                <form method="GET" action="{{ route('admin.izin.index') }}" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Jenis --</option>
                            <option value="izin" {{ request('type') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ request('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Siswa / Kelas</th>
                                <th>Jenis</th>
                                <th>Tanggal</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Lampiran</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($izinList as $item)
                            <tr id="row-izin-{{ $item->id }}">
                                <td>
                                    <strong>{{ optional($item->student)->name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ optional($item->student->classroom)->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $item->type === 'sakit' ? 'bg-warning text-dark' : 'bg-info' }}">
                                        {{ strtoupper($item->type) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                                <td>{{ $item->reason }}</td>
                                <td id="status-container-{{ $item->id }}">
                                    @if($item->status === 'approved')
                                        <span class="badge bg-success">Approved</span><br>
                                        <small class="text-muted">oleh: {{ $item->approved_by ?? '-' }}</small>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->attachment_path && Storage::disk('public')->exists($item->attachment_path))
                                        <a href="{{ asset('storage/' . $item->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-image"></i> Lihat Foto
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1" id="action-container-{{ $item->id }}">
                                        {{-- Tombol Setujui (Approve) --}}
                                        @if($item->status === 'pending')
                                            <button type="button" onclick="handleApprove('{{ $item->id }}')" class="btn btn-sm btn-success d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Setujui Pengajuan">
                                                <i class="bi bi-check-lg fs-6"></i>
                                            </button>
                                        @endif

                                        {{-- Tombol Tolak / Batalkan --}}
                                        @if($item->status === 'pending')
                                            <button type="button" onclick="confirmReject('{{ $item->id }}')" class="btn btn-sm btn-warning text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Tolak Pengajuan">
                                                <i class="bi bi-x-lg fs-6"></i>
                                            </button>
                                        @endif

                                        {{-- Tombol Hapus Permanen --}}
                                        <button type="button" onclick="confirmDelete('{{ $item->id }}')" class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Hapus Permanen">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>

                                        {{-- Tombol Cetak Surat --}}
                                        @if($item->type === 'izin')
                                            <a href="{{ route('admin.izin.cetak-surat', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Cetak Surat Izin">
                                                <i class="bi bi-printer fs-6"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.izin.cetak-sakit', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Cetak Keterangan Dokter">
                                                <i class="bi bi-printer fs-6"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr id="empty-row">
                                <td colspan="7" class="text-center text-muted py-4">Belum ada pengajuan izin/sakit.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $izinList->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT AJAX & SWEETALERT2 INTEGRATION -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Toast Notification Helper
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // 1. AJAX APPROVE IZIN
        function handleApprove(id) {
            Swal.fire({
                title: 'Setujui Pengajuan?',
                text: "Status izin akan diubah menjadi disetujui (Approved).",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    
                    fetch(`/admin/izin-siswa/${id}/approve`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Gagal memproses permintaan.');
                        return response.json().catch(() => ({ status: true }));
                    })
                    .then(data => {
                        Toast.fire({
                            icon: 'success',
                            title: 'Pengajuan izin berhasil disetujui.'
                        });

                        // Update tampilan badge status secara fleksibel tanpa reload
                        document.getElementById(`status-container-${id}`).innerHTML = `
                            <span class="badge bg-success">Approved</span><br>
                            <small class="text-muted">oleh: ${data.approved_by || 'Admin'}</small>
                        `;

                        // Sembunyikan tombol approve & reject
                        const actionContainer = document.getElementById(`action-container-${id}`);
                        const approveBtn = actionContainer.querySelector('.btn-success');
                        const rejectBtn = actionContainer.querySelector('.btn-warning');
                        if (approveBtn) approveBtn.remove();
                        if (rejectBtn) rejectBtn.remove();
                    })
                    .catch(error => {
                        Swal.fire('Gagal!', 'Terjadi kendala saat menyetujui izin.', 'error');
                    });
                }
            });
        }

        // 2. AJAX TOLAK / BATALKAN IZIN
        function confirmReject(id) {
            Swal.fire({
                title: 'Tolak Pengajuan?',
                text: "Pengajuan izin siswa ini akan ditolak dan dihapus dari sistem.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();

                    fetch(`/admin/izin-siswa/${id}/reject`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Gagal memproses permintaan.');
                        return response.json().catch(() => ({ status: true }));
                    })
                    .then(() => {
                        Toast.fire({
                            icon: 'warning',
                            title: 'Pengajuan berhasil ditolak & dihapus.'
                        });
                        removeRowWithAnimation(id);
                    })
                    .catch(error => {
                        Swal.fire('Gagal!', 'Terjadi kendala saat menolak pengajuan.', 'error');
                    });
                }
            });
        }

        // 3. AJAX HAPUS PERMANEN
        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Permanen?',
                text: "Data pengajuan izin siswa beserta lampirannya akan dihapus secara permanen!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();

                    fetch(`/admin/izin-siswa/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Gagal menghapus data.');
                        return response.json().catch(() => ({ status: true }));
                    })
                    .then(() => {
                        Toast.fire({
                            icon: 'success',
                            title: 'Data pengajuan izin berhasil dihapus.'
                        });
                        removeRowWithAnimation(id);
                    })
                    .catch(error => {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                    });
                }
            });
        }

        // Helper Animasi Hapus Baris Tabel
        function removeRowWithAnimation(id) {
            const row = document.getElementById(`row-izin-${id}`);
            if (row) {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => row.remove(), 400);
            }
        }
    </script>
</x-app-layout>