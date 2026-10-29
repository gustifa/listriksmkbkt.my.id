@section('title', 'Manajemen Pengumuman')

<x-app-layout>
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    <div class="page-content container-fluid px-3 px-md-4 py-3">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-bullhorn me-2"></i> Pengumuman Sekolah & Broadcast Telegram
                </h4>
                <p class="text-muted small mb-0">Buat dan kirim pengumuman langsung ke Bot Telegram Orang Tua / Siswa.</p>
            </div>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateAnnouncement">
                <i class="fas fa-plus me-1"></i> Buat Pengumuman Baru
            </button>
        </div>

        <!-- TABEL DAFTAR PENGUMUMAN -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Judul & Isi</th>
                                <th>Target Penerima</th>
                                <th>Kelas Khusus</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-center pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $item)
                                <tr>
                                    <td class="ps-3 py-3">
                                        <h6 class="fw-bold text-dark mb-1">{{ $item->title }}</h6>
                                        <p class="text-muted small mb-0 text-truncate" style="max-width: 400px;">{{ $item->content }}</p>
                                        
                                        <!-- Menampilkan Tombol Lampiran Berkas -->
                                        @if($item->attachment)
                                            <div class="mt-2">
                                                <a href="{{ asset('storage/' . $item->attachment) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none py-1 px-2">
                                                    <i class="fas fa-paperclip me-1"></i> Lihat Berkas Lampiran
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->target_role === 'parent')
                                            <span class="badge bg-info text-dark">Orang Tua / Wali</span>
                                        @elseif($item->target_role === 'student')
                                            <span class="badge bg-primary">Siswa Saja</span>
                                        @else
                                            <span class="badge bg-success">Semua (Ortu & Siswa)</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ optional($item->classroom)->name ?? 'Semua Kelas' }}
                                    </td>
                                    <td class="text-nowrap text-muted small">
                                        {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td class="text-center pe-3">
                                        <form action="{{ route('admin.announcements.destroy', $item->id) }}" method="POST" class="d-inline form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-delete-trigger">
                                                <i class="fas fa-trash-alt me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-bullhorn fa-2x mb-2 d-block opacity-50"></i>
                                        Belum ada pengumuman yang dibuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($announcements->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- MODAL BUAT PENGUMUMAN -->
    <div class="modal fade" id="modalCreateAnnouncement" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-bullhorn me-2"></i> Buat Pengumuman & Broadcast Telegram
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- WAJIB ADA enctype="multipart/form-data" AGAR FILE BERHASIL DI-UPLOAD -->
                <form action="{{ route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Pengumuman <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="Contoh: Pemberitahuan Libur Semester..." required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Target Penerima <span class="text-danger">*</span></label>
                                <select name="target_role" id="target_role" class="form-select rounded-3" required>
                                    <option value="parent">Orang Tua / Wali Saja</option>
                                    <option value="student">Siswa Saja</option>
                                    <option value="all">Semua (Orang Tua & Siswa)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Filter Kelas (Opsional)</label>
                                <select name="classroom_id" id="select-classroom" class="form-select rounded-3">
                                    <option value="">-- Semua Kelas (Broadcast Massal) --</option>
                                    @foreach($classrooms as $cls)
                                        <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- PRATINJAU TARGET PENERIMA -->
                        <div class="mb-3 p-3 bg-light border rounded-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-primary mb-0">
                                    <i class="fas fa-users me-1"></i> Pratinjau Target Penerima Telegram
                                </h6>
                                <span class="badge bg-primary rounded-pill px-3" id="recipient-count-badge">Memuat...</span>
                            </div>
                            <p class="text-muted small mb-2">Berikut adalah daftar akun Telegram yang akan menerima pesan ini:</p>
                            
                            <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                                <table class="table table-sm table-bordered bg-white mb-0 small">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Nama Penerima</th>
                                            <th>Peran</th>
                                            <th>Siswa Terkait</th>
                                            <th>Kelas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="preview-recipients-tbody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-2">Memuat data penerima...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Isi Pengumuman / Pesan <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control rounded-3" rows="4" placeholder="Tuliskan isi pengumuman lengkap yang akan dikirimkan ke Telegram..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Lampiran Berkas (Opsional)</label>
                            <input type="file" name="attachment" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Format: PDF, JPG, PNG (Maks. 2MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="fas fa-paper-plane me-1"></i> Kirim & Broadcast
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPT JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#select-classroom').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCreateAnnouncement'),
                placeholder: '-- Semua Kelas (Broadcast Massal) --',
                allowClear: true,
                width: '100%'
            });

            function fetchRecipientPreview() {
                const targetRole = $('#target_role').val();
                const classroomId = $('#select-classroom').val();

                $('#preview-recipients-tbody').html('<tr><td colspan="4" class="text-center text-muted py-2">Memuat data...</td></tr>');
                $('#recipient-count-badge').text('Memuat...');

                $.ajax({
                    url: "{{ route('admin.announcements.preview') }}",
                    type: "GET",
                    data: {
                        target_role: targetRole,
                        classroom_id: classroomId
                    },
                    success: function(response) {
                        $('#recipient-count-badge').text(response.total + ' Penerima Aktif');
                        
                        let rows = '';
                        if (response.recipients.length > 0) {
                            response.recipients.forEach(function(item) {
                                rows += `
                                    <tr>
                                        <td class="fw-bold">${item.name}</td>
                                        <td><span class="badge bg-secondary">${item.role}</span></td>
                                        <td>${item.student}</td>
                                        <td>${item.class}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            rows = '<tr><td colspan="4" class="text-center text-muted py-2">Tidak ada penerima Telegram aktif.</td></tr>';
                        }
                        $('#preview-recipients-tbody').html(rows);
                    }
                });
            }

            $('#modalCreateAnnouncement').on('shown.bs.modal', function () {
                fetchRecipientPreview();
            });

            $('#target_role, #select-classroom').on('change', function () {
                fetchRecipientPreview();
            });

            const deleteButtons = document.querySelectorAll('.btn-delete-trigger');
            deleteButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Hapus Pengumuman?',
                        text: 'Pengumuman ini akan dihapus permanen.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        customClass: { popup: 'rounded-4 shadow' }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'OK',
                customClass: { popup: 'rounded-4 shadow' }
            });
        });
    </script>
    @endif
</x-app-layout>