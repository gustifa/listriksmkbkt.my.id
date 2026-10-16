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

            
            <div class="col-12 col-lg-8">
                            <!-- Jadwal Pelajaran Hari Ini -->
            <div class="card-body p-3 pt-0">
                @if(isset($todaySchedules) && count($todaySchedules) > 0)
                    <div class="d-flex flex-column gap-2">
                        @foreach($todaySchedules as $schedule)
                            @php
                                $startTime = isset($schedule->start_time) ? substr($schedule->start_time, 0, 5) : '00:00';
                                $endTime   = isset($schedule->end_time) ? substr($schedule->end_time, 0, 5) : '00:00';
                            @endphp

                            <div class="p-3 border rounded-3 bg-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 shadow-sm">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-primary text-white fw-bold">
                                            <i class="far fa-clock me-1"></i> {{ $startTime }} - {{ $endTime }}
                                        </span>
                                        <span class="text-muted small"><i class="fas fa-user-tie me-1"></i>{{ $schedule->teacher->name ?? '-' }}</span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0">
                                        {{ $schedule->subject->name ?? 'Mata Pelajaran' }}
                                    </h6>
                                </div>

                                <div class="text-start text-sm-end">
                                    @if(isset($schedule->student_attendance) && $schedule->student_attendance)
                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i> Hadir
                                        </span>
                                    @elseif(isset($schedule->is_active) && $schedule->is_active)
                                        <form action="{{ route('student.attendance.store') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                                                <i class="fas fa-fingerprint me-1"></i> Absen Sekarang
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">Belum Aktif</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 bg-light rounded-3">
                        <p class="text-muted small mb-0">Tidak ada jadwal pelajaran hari ini.</p>
                    </div>
                @endif
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
                                        <th class="text-center">Aksi</th> <!-- Tambahkan ini -->
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
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-detail-sholat"
                                                        data-tanggal="{{ \Carbon\Carbon::parse($prayer->date)->translatedFormat('d F Y') }}"
                                                        data-jam="{{ \Carbon\Carbon::parse($prayer->check_in_time)->format('H:i') }} WIB"
                                                        data-sholat="Sholat {{ ucfirst($prayer->prayer_name) }}"
                                                        data-lokasi="{{ $prayer->masjid_name ?? ucfirst($prayer->location_type ?? 'Sekolah') }}"
                                                        data-status="{{ ucfirst($prayer->status) }}"
                                                        data-catatan="{{ $prayer->notes ?? '-' }}"
                                                        data-foto="{{ $prayer->photo_evidence ? asset('storage/' . $prayer->photo_evidence) : '' }}"
                                                    <i class="fas fa-eye me-1"></i> Detail
                                                </button>
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

                <!-- CARD HISTORI ABSENSI GERBANG -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-door-open me-2"></i>Histori Presensi Gerbang (Harian)
                        </h6>
                        <span class="badge bg-primary rounded-pill">5 Terakhir</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th class="text-center">Jam Masuk</th>
                                        <th class="text-center">Jam Pulang</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($gateHistory as $gate)
                                        <tr>
                                            <td class="fw-semibold text-nowrap">
                                                <i class="far fa-calendar-alt text-muted me-1"></i>
                                                {{ \Carbon\Carbon::parse($gate->date)->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="text-center fw-bold text-dark">
                                                @if($gate->arrival_time)
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="far fa-clock text-primary me-1"></i>
                                                        {{ \Carbon\Carbon::parse($gate->arrival_time)->format('H:i') }} WIB
                                                    </span>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold text-dark">
                                                @if($gate->departure_time)
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="far fa-clock text-danger me-1"></i>
                                                        {{ \Carbon\Carbon::parse($gate->departure_time)->format('H:i') }} WIB
                                                    </span>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $statusClass = match(strtolower($gate->status)) {
                                                        'hadir'     => 'bg-success',
                                                        'terlambat' => 'bg-warning text-dark',
                                                        'izin', 'sakit' => 'bg-info text-dark',
                                                        default     => 'bg-danger'
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }} rounded-pill px-3 py-1">
                                                    {{ ucfirst($gate->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <!-- TOMBOL DETAIL GERBANG -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-detail-gerbang"
                                                        data-tanggal="{{ \Carbon\Carbon::parse($gate->date)->translatedFormat('d F Y') }}"
                                                        data-masuk="{{ $gate->arrival_time ? \Carbon\Carbon::parse($gate->arrival_time)->format('H:i') . ' WIB' : '-' }}"
                                                        data-pulang="{{ $gate->departure_time ? \Carbon\Carbon::parse($gate->departure_time)->format('H:i') . ' WIB' : '-' }}"
                                                        data-status="{{ ucfirst($gate->status) }}"
                                                        data-catatan="{{ $gate->notes ?? '-' }}"
                                                        data-foto-masuk="{{ $gate->photo_in ? asset('storage/' . $gate->photo_in) : '' }}"
                                                        data-foto-pulang="{{ $gate->photo_out ? asset('storage/' . $gate->photo_out) : '' }}">
                                                    <i class="fas fa-eye me-1"></i> Detail
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-door-closed fa-2x mb-2 d-block opacity-50"></i>
                                                Belum ada riwayat presensi gerbang.
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

<!-- MODAL DETAIL SHOLAT -->
<div class="modal fade" id="modalDetailSholat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-mosque me-2"></i> Detail Presensi Sholat
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Foto Presensi -->
                <div id="containerFotoSholat" class="text-center mb-3 d-none">
                    <img id="detailFotoSholat" src="" alt="Foto Sholat" class="img-fluid rounded-3 border shadow-sm" style="max-height: 200px; object-fit: cover;">
                </div>

                <div class="list-group list-group-flush rounded-3 border">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-pray me-2 text-primary"></i>Nama Sholat</span>
                        <strong id="detailNamaSholat" class="text-dark"></strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="far fa-calendar-alt me-2 text-primary"></i>Tanggal & Waktu</span>
                        <span id="detailWaktuSholat" class="fw-bold"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Lokasi / Masjid</span>
                        <span id="detailLokasiSholat" class="fw-semibold"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-info-circle me-2 text-info"></i>Status</span>
                        <span id="detailStatusSholat" class="badge bg-success rounded-pill px-3 py-1"></span>
                    </div>
                    <div class="list-group-item">
                        <span class="text-muted d-block mb-1"><i class="fas fa-comment-alt me-2 text-secondary"></i>Catatan:</span>
                        <small id="detailCatatanSholat" class="text-dark fw-normal"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL ABSENSI GERBANG -->
<div class="modal fade" id="modalDetailGerbang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-door-open me-2"></i> Detail Presensi Gerbang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Foto Masuk & Pulang (Jika Ada) -->
                <!-- Foto Masuk & Pulang -->
                <div id="containerFotoGerbang" class="row g-2 mb-3 d-none">
                    <div class="col-6 text-center mx-auto" id="boxFotoMasuk">
                        <small class="text-muted d-block mb-1 fw-bold">Foto Masuk</small>
                        <img id="detailFotoMasuk" 
                            src="" 
                            alt="Foto Masuk" 
                            class="img-fluid rounded-3 border shadow-sm" 
                            style="max-height: 150px; object-fit: cover; width: 100%;"
                            onerror="this.closest('#boxFotoMasuk').classList.add('d-none'); checkEmptyPhotos();">
                    </div>
                    <div class="col-6 text-center mx-auto" id="boxFotoPulang">
                        <small class="text-muted d-block mb-1 fw-bold">Foto Pulang</small>
                        <img id="detailFotoPulang" 
                            src="" 
                            alt="Foto Pulang" 
                            class="img-fluid rounded-3 border shadow-sm" 
                            style="max-height: 150px; object-fit: cover; width: 100%;"
                            onerror="this.closest('#boxFotoPulang').classList.add('d-none'); checkEmptyPhotos();">
                    </div>
                </div>

                <div class="list-group list-group-flush rounded-3 border">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="far fa-calendar-alt me-2 text-primary"></i>Tanggal</span>
                        <strong id="detailTanggalGerbang" class="text-dark"></strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-sign-in-alt me-2 text-success"></i>Jam Masuk</span>
                        <span id="detailJamMasuk" class="fw-bold"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-sign-out-alt me-2 text-danger"></i>Jam Pulang</span>
                        <span id="detailJamPulang" class="fw-bold"></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-info-circle me-2 text-info"></i>Status</span>
                        <span id="detailStatusGerbang" class="badge rounded-pill px-3 py-1"></span>
                    </div>
                    <div class="list-group-item">
                        <span class="text-muted d-block mb-1"><i class="fas fa-comment-alt me-2 text-secondary"></i>Catatan:</span>
                        <small id="detailCatatanGerbang" class="text-dark fw-normal"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT DETAIL GERBANG -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnGerbangs = document.querySelectorAll('.btn-detail-gerbang');
        const modalGerbang = new bootstrap.Modal(document.getElementById('modalDetailGerbang'));

        btnGerbangs.forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('detailTanggalGerbang').textContent = this.dataset.tanggal;
                document.getElementById('detailJamMasuk').textContent = this.dataset.masuk;
                document.getElementById('detailJamPulang').textContent = this.dataset.pulang;
                document.getElementById('detailStatusGerbang').textContent = this.dataset.status;
                document.getElementById('detailCatatanGerbang').textContent = this.dataset.catatan;

                // Penanganan Foto Masuk & Pulang
                const fotoMasuk = this.dataset.fotoMasuk;
                const fotoPulang = this.dataset.fotoPulang;
                
                const containerFoto = document.getElementById('containerFotoGerbang');
                const boxMasuk = document.getElementById('boxFotoMasuk');
                const boxPulang = document.getElementById('boxFotoPulang');
                const imgMasuk = document.getElementById('detailFotoMasuk');
                const imgPulang = document.getElementById('detailFotoPulang');

                let hasPhoto = false;

                if (fotoMasuk && fotoMasuk.trim() !== '') {
                    imgMasuk.src = fotoMasuk;
                    boxMasuk.classList.remove('d-none');
                    hasPhoto = true;
                } else {
                    boxMasuk.classList.add('d-none');
                }

                if (fotoPulang && fotoPulang.trim() !== '') {
                    imgPulang.src = fotoPulang;
                    boxPulang.classList.remove('d-none');
                    hasPhoto = true;
                } else {
                    boxPulang.classList.add('d-none');
                }

                if (hasPhoto) {
                    containerFoto.classList.remove('d-none');
                } else {
                    containerFoto.classList.add('d-none');
                }

                modalGerbang.show();
            });
        });
    });
</script>

<!-- SCRIPT DETAIL SHOLAT -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnDetails = document.querySelectorAll('.btn-detail-sholat');
        const modalElement = new bootstrap.Modal(document.getElementById('modalDetailSholat'));

        btnDetails.forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('detailNamaSholat').textContent = this.dataset.sholat;
                document.getElementById('detailWaktuSholat').textContent = `${this.dataset.tanggal} (${this.dataset.jam})`;
                document.getElementById('detailLokasiSholat').textContent = this.dataset.lokasi;
                document.getElementById('detailStatusSholat').textContent = this.dataset.status;
                document.getElementById('detailCatatanSholat').textContent = this.dataset.catatan;

                const fotoUrl = this.dataset.foto;
                const containerFoto = document.getElementById('containerFotoSholat');
                const imgFoto = document.getElementById('detailFotoSholat');

                if (fotoUrl && fotoUrl.trim() !== '') {
                    imgFoto.src = fotoUrl;
                    containerFoto.classList.remove('d-none');
                } else {
                    containerFoto.classList.add('d-none');
                    imgFoto.src = '';
                }

                modalElement.show();
            });
        });
    });
</script>

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