@section('title', 'Dashboard Siswa')

<x-app-layout>
    <div class="page-content container-fluid px-3 px-md-4">
        
        <!-- Header Sambutan -->
        <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h4 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-user-graduate me-2"></i> Dashboard Siswa
                </h4>
                <p class="text-muted mb-0">
                    Selamat Datang, <strong>{{ $student->name }}</strong> | Kelas: <strong>{{ $student->classroom->name ?? 'Belum ada kelas' }}</strong> (NIS: {{ $student->nis ?? '-' }}) 
                </p>
                
            </div>

            <!-- === DI SINI LETAK KODENYA (Ganti bagian div tombol sebelah kanan) === -->
            <div class="d-flex align-items-center gap-2">
                @if($todayAttendance)
                    <!-- Jika SUDAH absen/izin hari ini: Tombol mati (disabled) -->
                    <button type="button" class="btn btn-secondary text-white fw-bold rounded-pill shadow-sm px-3" disabled title="Kamu sudah mengajukan presensi/izin hari ini">
                        <i class="fas fa-check-circle me-1"></i> Sudah Mengajukan Hari Ini
                    </button>
                @else
                    <!-- Jika BELUM absen/izin hari ini: Tombol aktif & bisa diklik -->
                    <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalPermit">
                        <i class="fas fa-notes-medical me-1"></i> Ajukan Izin / Sakit
                    </button>
                @endif

                <span class="badge bg-primary text-white p-2 rounded-3">
                    <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
            
        </div>

        <!-- 1. STATISTIK KARTU (RESPONSIF GRID) -->
        <div class="row g-3 mb-4">
            <!-- Status Presensi Hari Ini -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Presensi Hari Ini</div>
                                <div class="h5 mb-0 fw-bold text-dark">
                                    @if($todayAttendance)
                                        <span class="badge bg-success">{{ ucfirst($todayAttendance->status) }}</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Absen</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-primary opacity-50">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Hadir Bulan Ini -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Hadir (Bulan Ini)</div>
                                <div class="h4 mb-0 fw-bold text-dark">{{ $attendanceStats['hadir'] }} Hari</div>
                            </div>
                            <div class="text-success opacity-50">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Izin / Sakit Bulan Ini -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Izin / Sakit</div>
                                <div class="h4 mb-0 fw-bold text-dark">{{ $attendanceStats['izin'] + $attendanceStats['sakit'] }} Hari</div>
                            </div>
                            <div class="text-warning opacity-50">
                                <i class="fas fa-file-medical fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alpa Bulan Ini -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Alpa (Tanpa Ket.)</div>
                                <div class="h4 mb-0 fw-bold text-dark">{{ $attendanceStats['alpa'] }} Hari</div>
                            </div>
                            <div class="text-danger opacity-50">
                                <i class="fas fa-user-times fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. INFORMASI JADWAL & AKTIVITAS -->
        <div class="row g-3">
            <!-- Jadwal Pelajaran Hari Ini -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-book-reader me-2"></i>Jadwal Pelajaran Hari Ini
                        </h6>
                        <a href="{{ route('student.history.subject') }}" class="btn btn-sm btn-outline-primary rounded-pill">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Jam</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Pengajar</th>
                                        <th class="text-center">Presensi Mapel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($todaySchedules as $schedule)
                                        <tr>
                                            <td class="fw-semibold text-nowrap">
                                                <i class="far fa-clock text-muted me-1"></i>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </td>
                                            <td class="fw-bold text-dark">{{ $schedule->subject->name ?? '-' }}</td>
                                            <td>{{ $schedule->teacher->name ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($schedule->student_attendance)
                                                    {{-- SUDAH PRESENSI --}}
                                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                                        <i class="fas fa-check-circle me-1"></i> Hadir ({{ \Carbon\Carbon::parse($schedule->student_attendance->check_in_time)->format('H:i') }})
                                                    </span>
                                                @elseif($schedule->is_active)
                                                    {{-- JADWAL SEDANG BERLANGSUNG --}}
                                                    <form action="{{ route('student.attendance.store') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="return confirm('Absen masuk untuk mata pelajaran ini?')">
                                                            <i class="fas fa-fingerprint me-1"></i> Absen Sekarang
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- BELUM AKTIF ATAU SUDAH LEWAT --}}
                                                    <span class="badge bg-secondary rounded-pill px-3 py-2">Belum Aktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-calendar-day fa-2x mb-2 d-block opacity-50"></i>
                                                Tidak ada jadwal pelajaran hari ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- CARD HISTORI PELAKSANAAN SHOLAT -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-mosque me-2"></i>Histori Pelaksanaan Sholat
                        </h6>
                        <span class="badge bg-primary rounded-pill">5 Terakhir</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal & Jam</th>
                                        <th>Nama Sholat</th>
                                        <th>Lokasi / Masjid</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($prayerHistory as $prayer)
                                        <tr>
                                            <td class="fw-semibold text-nowrap">
                                                <i class="far fa-calendar-alt text-muted me-1"></i>
                                                {{ \Carbon\Carbon::parse($prayer->date)->format('d/m/Y') }}
                                                <small class="text-muted ms-1">({{ \Carbon\Carbon::parse($prayer->check_in_time)->format('H:i') }} WIB)</small>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                Sholat {{ ucfirst($prayer->prayer_name) }}
                                            </td>
                                            <td>
                                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                {{ $prayer->masjid_name ?? ucfirst($prayer->location_type ?? 'Sekolah') }}
                                            </td>
                                            <td class="text-center">
                                                @if($prayer->status === 'hadir' || $prayer->status === 'terlaksana')
                                                    <span class="badge bg-success rounded-pill px-3 py-1">
                                                        <i class="fas fa-check-circle me-1"></i> {{ ucfirst($prayer->status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary rounded-pill px-3 py-1">
                                                        {{ ucfirst($prayer->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-pray fa-2x mb-2 d-block opacity-50"></i>
                                                Belum ada riwayat pelaksanaan sholat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            
            <!-- Kolom Kanan: Histori Pembelajaran & Rekap Absensi Gerbang -->
            <div class="col-12 col-lg-4">
                
                <!-- CARD TABS: HISTORI ABSENSI & PEMBELAJARAN -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-2">
                        <ul class="nav nav-tabs card-header-tabs id="dashboardTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold text-primary" id="gerbang-tab" data-bs-toggle="tab" data-bs-target="#gerbang" type="button" role="tab">
                                    <i class="fas fa-door-open me-1"></i> Absensi Gerbang
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold text-primary" id="mapel-tab" data-bs-toggle="tab" data-bs-target="#mapel" type="button" role="tab">
                                    <i class="fas fa-history me-1"></i> Histori Mapel
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <div class="tab-content" id="dashboardTabsContent">
                            
                            <!-- TAB 1: REKAP ABSENSI GERBANG -->
                            <div class="tab-pane fade show active" id="gerbang" role="tabpanel">
                                <!-- Sub Tab/Pill Waktu -->
                                <ul class="nav nav-pills nav-justified mb-3" id="pills-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active btn-sm py-1" id="pills-harian-tab" data-bs-toggle="pill" data-bs-target="#pills-harian" type="button">Harian</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link btn-sm py-1" id="pills-mingguan-tab" data-bs-toggle="pill" data-bs-target="#pills-mingguan" type="button">Mingguan</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link btn-sm py-1" id="pills-bulanan-tab" data-bs-toggle="pill" data-bs-target="#pills-bulanan" type="button">Bulanan</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="pills-tabContent">
                                    <!-- HARIAN -->
                                    <div class="tab-pane fade show active" id="pills-harian">
                                        <div class="p-3 bg-light rounded text-center">
                                            <span class="text-muted d-block mb-1">Status Kedatangan Hari Ini</span>
                                            @if($gateAttendanceStats['harian'])
                                                <span class="badge bg-success fs-6">{{ ucfirst($gateAttendanceStats['harian']->status) }}</span>
                                                <small class="d-block text-muted mt-2">
                                                    Jam Masuk: {{ $gateAttendanceStats['harian']->arrival_time ?? '-' }} | Pulang: {{ $gateAttendanceStats['harian']->departure_time ?? '-' }}
                                                </small>
                                            @else
                                                <span class="badge bg-secondary fs-6">Belum Ada Catatan</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- MINGGUAN -->
                                    <div class="tab-pane fade" id="pills-mingguan">
                                        <div class="row g-2 text-center">
                                            <div class="col-6"><div class="p-2 border rounded bg-light"><small class="text-success fw-bold">Hadir</small><h6 class="mb-0">{{ $gateAttendanceStats['mingguan']['hadir'] }} Hari</h6></div></div>
                                            <div class="col-6"><div class="p-2 border rounded bg-light"><small class="text-warning fw-bold">Izin / Sakit</small><h6 class="mb-0">{{ $gateAttendanceStats['mingguan']['izin'] + $gateAttendanceStats['mingguan']['sakit'] }} Hari</h6></div></div>
                                            <div class="col-12"><div class="p-2 border rounded bg-light"><small class="text-danger fw-bold">Alpa</small><h6 class="mb-0">{{ $gateAttendanceStats['mingguan']['alpa'] }} Hari</h6></div></div>
                                        </div>
                                    </div>

                                    <!-- BULANAN -->
                                    <div class="tab-pane fade" id="pills-bulanan">
                                        <div class="row g-2 text-center">
                                            <div class="col-6"><div class="p-2 border rounded bg-light"><small class="text-success fw-bold">Hadir</small><h6 class="mb-0">{{ $gateAttendanceStats['bulanan']['hadir'] }} Hari</h6></div></div>
                                            <div class="col-6"><div class="p-2 border rounded bg-light"><small class="text-warning fw-bold">Izin / Sakit</small><h6 class="mb-0">{{ $gateAttendanceStats['bulanan']['izin'] + $gateAttendanceStats['bulanan']['sakit'] }} Hari</h6></div></div>
                                            <div class="col-12"><div class="p-2 border rounded bg-light"><small class="text-danger fw-bold">Alpa</small><h6 class="mb-0">{{ $gateAttendanceStats['bulanan']['alpa'] }} Hari</h6></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: HISTORI PEMBELAJARAN (MAPEL) -->
                            <div class="tab-pane fade" id="mapel" role="tabpanel">
                                <ul class="list-group list-group-flush">
                                    @forelse($subjectAttendanceHistory as $history)
                                        <li class="list-group-item px-0 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="text-dark d-block mb-0" style="font-size: 0.9rem;">{{ $history->subject->name ?? 'Mata Pelajaran' }}</strong>
                                                    <small class="text-muted" style="font-size: 0.75rem;">
                                                        <i class="far fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($history->date)->format('d M Y') }} | {{ $history->check_in_time }}
                                                    </small>
                                                </div>
                                                <span class="badge bg-success" style="font-size: 0.7rem;">
                                                    <i class="fas fa-check-circle me-1"></i>Hadir
                                                </span>
                                            </div>
                                        </li>
                                    @empty
                                        <p class="text-muted text-center my-3"><i class="fas fa-info-circle me-1"></i> Belum ada riwayat presensi mapel.</p>
                                    @endforelse
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Card Ujian Aktif -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-edit me-2"></i>Ujian / Evaluasi Aktif
                        </h6>
                    </div>
                    <div class="card-body">
                        @forelse($activeExams as $exam)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark">{{ $exam->title }}</h6>
                                    <small class="text-muted"><i class="far fa-clock me-1"></i>{{ $exam->duration }} Menit</small>
                                </div>
                                <a href="{{ route('student.exam.start', $exam->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">Ikuti</a>
                            </div>
                        @empty
                            <p class="text-muted text-center my-3">
                                <i class="fas fa-info-circle me-1"></i> Belum ada ujian aktif.
                            </p>
                        @endforelse
                    </div>
                </div>

            </div>

    </div>

    <!-- MODAL FORM AJUKAN IZIN / SAKIT -->
<div class="modal fade" id="modalPermit" tabindex="-1" aria-labelledby="modalPermitLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalPermitLabel">
                    <i class="fas fa-file-medical me-2"></i> Form Pengajuan Izin / Sakit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('student.permit.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <!-- Pilih Status -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kategori Pengajuan <span class="text-danger">*</span></label>
                        <select name="status" id="permit_status" class="form-select rounded-3" required>
                            <option value="">-- Pilih Jenis Pengajuan --</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                        </select>
                    </div>

                    <!-- Alasan / Keterangan -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alasan / Keterangan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control rounded-3" rows="3" placeholder="Tuliskan alasan tidak dapat hadir..." required></textarea>
                    </div>

                    <!-- Upload Surat Keterangan Sakit -->
                    <div class="mb-3" id="fileUploadContainer" style="display: none;">
                        <label class="form-label fw-bold text-dark">
                            Surat Keterangan Dokter / Sakit <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="medical_certificate" id="medical_certificate" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle me-1"></i> Format: PDF, JPG, JPEG, PNG (Maksimal 2MB).
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT UNTUK TAMPILKAN INPUT FILE -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const permitStatusSelect = document.getElementById('permit_status');
        const fileContainer = document.getElementById('fileUploadContainer');
        const fileInput = document.getElementById('medical_certificate');

        if (permitStatusSelect) {
            permitStatusSelect.addEventListener('change', function () {
                if (this.value === 'sakit') {
                    fileContainer.style.display = 'block';
                    fileInput.setAttribute('required', 'required');
                } else {
                    fileContainer.style.display = 'none';
                    fileInput.removeAttribute('required');
                    fileInput.value = '';
                }
            });
        }
    });
</script>

    <!-- CDN SweetAlert2 Terbaru -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('swal_alert'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertData = @json(session('swal_alert'));
        // PENANGANAN TIPE: no_gate_attendance
        if (alertData.type === 'no_gate_attendance') {
            Swal.fire({
                title: `<span style="color: #dc3545;"><i class="fas fa-exclamation-triangle me-2"></i>${alertData.title}</span>`,
                html: `
                    <div class="text-center my-2">
                        <p class="text-dark mb-2">${alertData.message}</p>
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="fas fa-door-closed me-1"></i> Status: Belum Absen
                        </span>
                    </div>
                `,
                icon: 'warning',
                position: 'center',
                confirmButtonColor: '#dc3545',
                confirmButtonText: '<i class="fas fa-check me-1"></i> Saya Mengerti',
                allowOutsideClick: false,
                customClass: {
                    popup: 'rounded-4 shadow-lg'
                }
            });
        }

        if (alertData.type === 'active') {
            Swal.fire({
                title: `<span style="color: #0d6efd;"><i class="fas fa-clock me-2"></i>${alertData.title}</span>`,
                html: `
                    <div class="text-center my-2">
                        <h4 class="fw-bold text-dark mb-1">${alertData.subject}</h4>
                        <p class="text-muted mb-2"><i class="fas fa-user-tie me-1"></i> ${alertData.teacher}</p>
                        <span class="badge bg-primary fs-6 px-3 py-2">
                            <i class="far fa-clock me-1"></i> ${alertData.time} WIB
                        </span>
                    </div>
                `,
                icon: 'info',
                position: 'center',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-fingerprint me-1"></i> Absen Sekarang',
                cancelButtonText: 'Nanti Saja',
                allowOutsideClick: false,
                customClass: {
                    popup: 'rounded-4 shadow-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Otomatis Submit Form Absen Mapel Aktif
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('student.attendance.store') }}";

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const scheduleInput = document.createElement('input');
                    scheduleInput.type = 'hidden';
                    scheduleInput.name = 'schedule_id';
                    scheduleInput.value = alertData.schedule_id;

                    form.appendChild(csrfToken);
                    form.appendChild(scheduleInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        } else if (alertData.type === 'upcoming') {
            Swal.fire({
                title: `<span style="color: #ffc107;"><i class="fas fa-bell me-2"></i>${alertData.title}</span>`,
                html: `
                    <div class="text-center my-2">
                        <h4 class="fw-bold text-dark mb-1">${alertData.subject}</h4>
                        <p class="text-muted mb-2"><i class="fas fa-user-tie me-1"></i> ${alertData.teacher}</p>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <i class="far fa-clock me-1"></i> ${alertData.time} WIB
                        </span>
                        <small class="d-block text-muted mt-3">Persiapankan diri Anda sebelum jam pelajaran dimulai.</small>
                    </div>
                `,
                icon: 'warning',
                position: 'center',
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Siap, Mengerti!',
                customClass: {
                    popup: 'rounded-4 shadow-lg'
                }
            });
        }
    });
</script>
@endif
</x-app-layout>