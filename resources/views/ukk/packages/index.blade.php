@section('title', 'Master Paket UKK')

<x-app-layout>
    <div class="page-content">
        <div class="container-fluid">
            <!-- Breadcrumb -->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">UKK</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Kelola Paket & Unit Kompetensi</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Form Tambah Paket Baru -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Paket UKK Baru
                </div>
                <div class="card-body">
                    <form action="{{ route('ukk.packages.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Kode Jurusan</label>
                                <input type="text" name="major_code" class="form-control" placeholder="Contoh: TITL / TPTUP" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Judul Paket UKK</label>
                                <input type="text" name="package_name" class="form-control" placeholder="Contoh: Paket 1 - Perencanaan Instalasi Tenaga" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Tahun Ajaran</label>
                                <input type="text" name="academic_year" class="form-control" value="2026/2027" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100 fw-bold"><i class="fas fa-save me-1"></i> Simpan Paket</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Daftar Paket UKK & Detail Unit -->
            <div class="row">
                @forelse($packages as $package)
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                            <div>
                                <span class="badge bg-info text-dark me-2">{{ $package->major_code }}</span>
                                <strong class="fs-6">{{ $package->package_name }}</strong>
                                <small class="text-muted ms-2">({{ $package->academic_year }})</small>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUnit{{ $package->id }}">
                                    <i class="fas fa-plus me-1"></i> Tambah Unit
                                </button>
                                <form action="{{ route('ukk.packages.destroy', $package->id) }}" method="POST" class="d-inline form-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th width="20%">Kode Unit</th>
                                            <th width="35%">Judul Unit Kompetensi</th>
                                            <th width="35%">Elemen Kompetensi</th>
                                            <th width="5%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($package->units as $idx => $unit)
                                        <tr>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td class="fw-bold">{{ $unit->unit_code }}</td>
                                            <td>{{ $unit->unit_title }}</td>
                                            <td>{{ $unit->element_name }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('ukk.units.destroy', $unit->id) }}" method="POST" class="d-inline form-delete">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-muted">Belum ada unit kompetensi pada paket ini.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Tambah Unit -->
                <div class="modal fade" id="modalAddUnit{{ $package->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="{{ route('ukk.packages.units.store', $package->id) }}" method="POST">
                                @csrf
                                <div class="modal-header bg-primary text-white">
                                    <h6 class="modal-title fw-bold">Tambah Unit: {{ $package->package_name }}</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Kode Unit</label>
                                        <input type="text" name="unit_code" class="form-control" placeholder="Contoh: KEL.TL01.001.01" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Judul Unit Kompetensi</label>
                                        <input type="text" name="unit_title" class="form-control" placeholder="Contoh: Memasang Komponen Instalasi Tenaga" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Elemen Kompetensi</label>
                                        <textarea name="element_name" class="form-control" rows="3" placeholder="Contoh: Menyiapkan komponen dan peralatan instalasi..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Unit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-4">
                        <i class="fas fa-info-circle me-1"></i> Belum ada Paket UKK yang terdaftar.
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- SweetAlert Handlers -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const form = this.closest('.form-delete');
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
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
        });

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 2500, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}", timer: 3000, showConfirmButton: true });
        @endif
    </script>
</x-app-layout>