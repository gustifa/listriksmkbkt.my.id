@section('title', 'Pengaturan Jadwal Absensi')

<x-app-layout>
    <div class="page-content">
        <!--breadcrumb-->
        <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
            <div class="breadcrumb-title pe-3">Setting</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="p-0 mb-0 breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Jadwal Masuk & Pulang</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row justify-content-center">
            <!-- Form Pengaturan -->
            <div class="col-md-8">
                <div class="border-0 shadow card">
                    <div class="text-white card-header bg-primary">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Pengaturan Jam Absensi</h5>
                    </div>

                    <div class="p-4 card-body">
                        <form action="{{ route('settings.update.attendance') }}" method="POST">
                            @csrf

                            <!-- BAGIAN 1: JAM MASUK -->
                            <h6 class="pb-2 mb-3 text-primary fw-bold border-bottom">Aturan Kedatangan (Check-In)</h6>

                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Jam Mulai Scan Dibuka</label>
                                    <input type="time" name="start_check_in_time" class="form-control"
                                           value="{{ $setting->start_check_in_time ?? '06:00' }}" required>
                                    <div class="form-text text-muted small">Siswa tidak bisa absen sebelum jam ini.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-danger">Batas Keterlambatan</label>
                                    <input type="time" name="late_limit_time" class="form-control border-danger"
                                           value="{{ $setting->late_limit_time ?? '07:00' }}" required>
                                    <div class="form-text text-danger small">Lewat jam ini status otomatis <b>TERLAMBAT</b>.</div>
                                </div>
                            </div>

                            <!-- BAGIAN 2: JAM PULANG -->
                            <h6 class="pb-2 mt-4 mb-3 text-primary fw-bold border-bottom">Aturan Kepulangan (Check-Out)</h6>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Batas Awal Jam Pulang</label>
                                <input type="time" name="early_departure_time" class="form-control"
                                       value="{{ $setting->early_departure_time ?? '14:00' }}" required>
                                <div class="form-text text-muted small">Siswa tidak bisa scan pulang sebelum jam ini.</div>
                            </div>

                            <!-- TOMBOL SIMPAN -->
                            <div class="mt-4 d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bx bx-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel Informasi / Simulasi -->
            <div class="col-md-4">
                <div class="border-0 shadow-sm card bg-light">
                    <div class="card-body">
                        <h6 class="mb-3 fw-bold"><i class="fas fa-info-circle me-1"></i> Simulasi Logika</h6>
                        <ul class="bg-transparent list-group list-group-flush">
                            <li class="px-0 py-2 bg-transparent list-group-item">
                                <small class="text-muted d-block">00:00 - {{ substr($setting->start_check_in_time ?? '06:00', 0, 5) }}</small>
                                <span class="badge bg-secondary">Absen Ditolak (Belum Buka)</span>
                            </li>
                            <li class="px-0 py-2 bg-transparent list-group-item">
                                <small class="text-muted d-block">{{ substr($setting->start_check_in_time ?? '06:00', 0, 5) }} - {{ substr($setting->late_limit_time ?? '07:00', 0, 5) }}</small>
                                <span class="badge bg-success">Hadir Tepat Waktu</span>
                            </li>
                            <li class="px-0 py-2 bg-transparent list-group-item">
                                <small class="text-muted d-block">> {{ substr($setting->late_limit_time ?? '07:00', 0, 5) }}</small>
                                <span class="badge bg-warning text-dark">Terlambat</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Notifikasi Sukses
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        // Notifikasi Error Validasi (Misal jam pulang < jam masuk)
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: '<ul style="text-align: left;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
            });
        @endif
    </script>
</x-app-layout>
