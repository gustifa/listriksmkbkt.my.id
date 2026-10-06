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
            <div>
                <span class="badge bg-primary text-white p-2">
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($todaySchedules as $schedule)
                                        <tr>
                                            <td class="fw-semibold text-nowrap">
                                                <i class="far fa-clock text-muted me-1"></i>
                                                {{ $schedule->start_time }} - {{ $schedule->end_time }}
                                            </td>
                                            <td class="fw-bold text-dark">{{ $schedule->subject->name ?? '-' }}</td>
                                            <td>{{ $schedule->teacher->name ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
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
            </div>

            <!-- Ujian & Timeline PKL -->
            <div class="col-12 col-lg-4">
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

                <!-- Card Timeline Kegiatan/PKL -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-stream me-2"></i>Timeline Agenda
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse($timelines as $timeline)
                                <li class="list-group-item px-0">
                                    <div class="fw-bold text-dark">{{ $timeline->title }}</div>
                                    <small class="text-muted">
                                        <i class="far fa-calendar me-1"></i>
                                        {{ \Carbon\Carbon::parse($timeline->start_date)->format('d M Y') }}
                                    </small>
                                </li>
                            @empty
                                <p class="text-muted text-center my-2">Belum ada agenda kegiatan.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>