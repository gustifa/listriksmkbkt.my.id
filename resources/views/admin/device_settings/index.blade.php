@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">📱 Pengaturan Kunci Perangkat (HP) Siswa</h4>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Konfigurasi Sistem --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bx bx-cog me-1"></i> Konfigurasi Sistem</h6>
        </div>
        @hasanyrole('super_admin|admin')
        <div class="card-body">
            <form action="{{ route('admin.device-settings.update') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Status Kunci Perangkat</label>
                        <select name="enable_device_lock" class="form-select">
                            <option value="1" {{ ($settings['enable_device_lock'] ?? '0') == '1' ? 'selected' : '' }}>
                                🔒 AKTIF (Siswa Terkunci pada HP Terdaftar)
                            </option>
                            <option value="0" {{ ($settings['enable_device_lock'] ?? '0') == '0' ? 'selected' : '' }}>
                                🔓 NON-AKTIF (Siswa Bebas Login dari HP Manapun)
                            </option>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-bold">Batas Maksimal HP per Siswa</label>
                        <select name="max_allowed_devices" class="form-select">
                            <option value="1" {{ ($settings['max_allowed_devices'] ?? '1') == '1' ? 'selected' : '' }}>
                                📱 1 Perangkat (Hanya 1 HP Utama)
                            </option>
                            <option value="2" {{ ($settings['max_allowed_devices'] ?? '1') == '2' ? 'selected' : '' }}>
                                📱📱 2 Perangkat (Maksimal 2 HP)
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-save me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endhasanyrole
    </div>

    {{-- Tabel Data Siswa & Perangkat --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bx bx-user-check me-1"></i> Daftar Perangkat Terikat Siswa</h6>
        </div>

        {{-- Form Filter Kelas & Pencarian Nama --}}
        <div class="card-body border-bottom">
            <form action="{{ route('admin.device-settings.index') }}" method="GET" class="row g-2 align-items-center">
                {{-- Select2 Filter Kelas --}}
                <div class="col-md-4">
                    <select name="classroom_id" id="select-classroom-filter" class="form-select">
                        <option value="">-- Semua Kelas --</option>
                        @if(isset($selectedClassroom) &&$selectedClassroom)
                            <option value="{{ $selectedClassroom->id }}" selected>{{ $selectedClassroom->name }}</option>
                        @endif
                    </select>
                </div>

                {{-- Cari Nama / Email --}}
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search"></i> Cari
                        </button>
                    </div>
                </div>

                {{-- Reset Filter --}}
                <div class="col-md-3 text-end">
                    <a href="{{ route('admin.device-settings.index') }}" class="btn btn-secondary w-100">
                        <i class="bx bx-refresh"></i> Reset Filter
                    </a>
                </div>
            </form>
        </div>

        {{-- Table Data --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">NO</th>
                        <th>SISWA</th>
                        <th>EMAIL</th>
                        <th>KELAS</th>
                        <th class="text-center">HP PERTAMA</th>
                        <th class="text-center">HP KEDUA</th>
                        <th class="text-center" width="15%">AKSI / RESET</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index =>$student)
                        <tr>
                            <td class="text-center">{{ $students->firstItem() +$index }}</td>
                            <td class="fw-bold">{{ $student->name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $student->student->classroom->name ?? '-' }}
                                </span>
                            </td>
                            
                            {{-- HP PERTAMA --}}
                            <td class="text-center">
                                @if($student->device_id_1)
                                    <span class="badge bg-success"><i class="bx bx-mobile-alt me-1"></i>Terikat</span>
                                    <div class="small text-muted text-truncate" style="max-width: 110px;" title="{{ $student->device_id_1 }}">{{ $student->device_id_1 }}</div>
                                @else
                                    <span class="badge bg-secondary">Belum Ada</span>
                                @endif
                            </td>

                            {{-- HP KEDUA --}}
                            <td class="text-center">
                                @if($student->device_id_2)
                                    <span class="badge bg-success"><i class="bx bx-mobile-alt me-1"></i>Terikat</span>
                                    <div class="small text-muted text-truncate" style="max-width: 110px;" title="{{ $student->device_id_2 }}">{{ $student->device_id_2 }}</div>
                                @else
                                    <span class="badge bg-secondary">Belum Ada</span>
                                @endif
                            </td>

                            {{-- AKSI RESET --}}
                            <td class="text-center">
                                @if($student->device_id_1 || $student->device_id_2)
                                    <button type="button" class="btn btn-danger btn-sm btn-reset-device" data-id="{{ $student->id }}" data-name="{{ $student->name }}">
                                        <i class="bx bx-refresh me-1"></i> Reset HP
                                    </button>
                                @else
                                    <button class="btn btn-light btn-sm text-muted" disabled>
                                        <i class="bx bx-check-circle me-1"></i> Bersih
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Data siswa tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="small text-muted">
                Menampilkan {{ $students->firstItem() ?? 0 }} sampai {{ $students->lastItem() ?? 0 }} dari {{$students->total() }} siswa
            </span>
            <div>
                {{ $students->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<!-- CDN CSS Select2 & Theme Bootstrap 5 -->
<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" /> -->
<!-- <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" /> -->
<style>
    /* Styling agar tampilan Select2 presisi dengan Bootstrap 5 */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px !important;
        border: 1px solid #dee2e6 !important;
    }
    .select2-container--bootstrap-5 .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
    }
</style>
@endpush

@push('scripts')
<!-- Library JS jQuery, Select2, & SweetAlert2 -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // 1. Inisialisasi Select2 AJAX Filter Kelas
    const $selectClass =$('#select-classroom-filter');

    if ($selectClass.length) {$selectClass.select2({
            theme: 'bootstrap-5',
            allowClear: true,
            placeholder: '-- Semua Kelas --',
            width: '100%',
            ajax: {
                url: "{{ route('admin.device-settings.classes') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || '' // Parameter pencarian nama kelas
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

        // Trigger Submit Form saat Pilihan Kelas Berubah/Dihapus
        $selectClass.on('select2:select select2:unselect', function (e) {$(this).closest('form').submit();
        });
    }

    // 2. AJAX Reset Device via SweetAlert2
    $(document).on('click', '.btn-reset-device', function() {
        const studentId = $(this).data('id');
        const studentName = $(this).data('name');

        Swal.fire({
            title: 'Reset Perangkat?',
            text: `Semua tautan HP milik "${studentName}" akan dihapus. Siswa dapat login kembali dari HP baru.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/device-settings/reset') }}/" + studentId,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
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
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat mereset perangkat.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush