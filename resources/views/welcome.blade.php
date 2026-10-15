<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $faviconPath = \App\Models\Setting::where('key', 'app_favicon')->value('value')
                    ?? \App\Models\Setting::where('key', 'logo_left')->value('value');
    @endphp

    @if($faviconPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($faviconPath))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $faviconPath) }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('storage/' . $faviconPath) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    <title>Beranda | {{ \App\Models\Setting::where('key', 'app_name')->value('value') ?? 'SISFO' }} {{ \App\Models\Setting::where('key', 'school_name')->value('value') ?? 'SMK Negeri 1 Bukittinggi' }}</title>

    <!-- Bootstrap 5 & FontAwesome Lightweight CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --bg-light: #f8f9fa;
            --border-color: #e9ecef;
        }

        body {
            background-color: var(--bg-light);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #212529;
            padding-top: 70px;
        }

        /* Navbar Ringan */
        .navbar-lightweight {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
        }

        /* Metric Cards Minimalis */
        .card-stat {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 14px 16px;
            transition: border-color 0.15s ease-in-out;
        }
        .card-stat:hover {
            border-color: #b6d4fe;
        }

        /* Timeline Streamline */
        .timeline-wrapper {
            border-left: 2px solid #dee2e6;
            margin-left: 10px;
            padding-left: 20px;
        }
        .timeline-block {
            position: relative;
            margin-bottom: 24px;
        }
        .timeline-icon-dot {
            position: absolute;
            left: -31px;
            top: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            color: var(--primary-color);
        }

        /* Schedule List Box Ringan */
        .schedule-box {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
        }
        .schedule-box:active {
            background: #e9ecef;
        }

        /* Mobile View Tweaks */
        @media (max-width: 575.98px) {
            body { padding-top: 65px; }
            .timeline-wrapper { margin-left: 5px; padding-left: 15px; }
            .timeline-icon-dot { left: -24px; width: 16px; height: 16px; font-size: 0.55rem; }
        }
    </style>
</head>
<body>

<!-- Navbar Ringan -->
<nav class="navbar navbar-expand-lg navbar-lightweight py-2">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary fs-6 d-flex align-items-center" href="#">
            <i class="fas fa-graduation-cap me-2"></i>
            <span>{{ \App\Models\Setting::value('app_name', 'SISFO') }}SMK</span>
        </a>
        <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <i class="fas fa-bars fs-5"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mx-auto my-2 my-lg-0 small fw-semibold">
                <li class="nav-item"><a class="nav-link text-dark" href="#">Beranda</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="#rekap">Rekapitulasi</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="#jadwal">Jadwal Hari Ini</a></li>
            </ul>
            <div class="d-flex justify-content-center">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold"><i class="fas fa-gauge me-1"></i> Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold"><i class="fas fa-sign-in-alt me-1"></i> Masuk Sistem</a>
                    @endauth
                @endif
            </div>
        </div>
    </div>
</nav>

<div class="container py-3">
    
    <!-- Hero Header Ringan -->
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">Informasi <span class="text-primary">Terintegrasi</span></h4>
        <p class="text-muted small mb-0">Pantau presensi, jadwal pelajaran, dan kegiatan sekolah secara real-time.</p>
    </div>

    <!-- Ringkasan Absensi (Metric Cards) -->
    <div class="row g-2 mb-3" id="rekap">
        <div class="col-6 col-md-3">
            <div class="card-stat">
                <small class="text-muted d-block fw-semibold" style="font-size: 0.75rem;">HADIR HARI INI</small>
                <div class="fs-4 fw-bold text-success">{{ $stats['hadir'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-stat">
                <small class="text-muted d-block fw-semibold" style="font-size: 0.75rem;">TERLAMBAT</small>
                <div class="fs-4 fw-bold text-warning">{{ $stats['terlambat'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-stat">
                <small class="text-muted d-block fw-semibold" style="font-size: 0.75rem;">SAKIT / IZIN</small>
                <div class="fs-4 fw-bold text-info">{{ $stats['sakit'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-stat">
                <small class="text-muted d-block fw-semibold" style="font-size: 0.75rem;">TANPA KETERANGAN</small>
                <div class="fs-4 fw-bold text-danger">{{ $stats['alpa'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- WIDGET MONITORING BELUM ABSEN (REAL-TIME) -->
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h6 class="fw-bold text-primary mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i> Monitoring Kendala Presensi Hari Ini
            </h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.7rem;">
                Real-Time
            </span>
        </div>
        <div class="row g-2">
            <!-- Card 1: Belum Absen Masuk -->
            <div class="col-12 col-md-4">
                <div class="p-2 rounded-3 bg-danger bg-opacity-10 border border-danger-subtle d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-danger fw-bold d-block" style="font-size: 0.72rem;">BELUM ABSEN MASUK</small>
                        <span class="fs-5 fw-bold text-danger">{{ $noArrivalCount ?? 0 }} <small class="fs-6 text-muted">Siswa</small></span>
                    </div>
                    <i class="fas fa-sign-in-alt fs-4 text-danger opacity-75 me-2"></i>
                </div>
            </div>

            <!-- Card 2: Belum Absen Pulang -->
            <div class="col-12 col-md-4">
                <div class="p-2 rounded-3 bg-warning bg-opacity-10 border border-warning-subtle d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-warning-emphasis fw-bold d-block" style="font-size: 0.72rem;">BELUM ABSEN PULANG</small>
                        <span class="fs-5 fw-bold text-warning-emphasis">{{ $noDepartureCount ?? 0 }} <small class="fs-6 text-muted">Siswa</small></span>
                    </div>
                    <i class="fas fa-sign-out-alt fs-4 text-warning opacity-75 me-2"></i>
                </div>
            </div>

            <!-- Card 3: Presensi Mapel Bermasalah -->
            <div class="col-12 col-md-4">
                <div class="p-2 rounded-3 bg-primary bg-opacity-10 border border-primary-subtle d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-primary fw-bold d-block" style="font-size: 0.72rem;">BELUM PRESENSI MAPEL</small>
                        <span class="fs-5 fw-bold text-primary">{{ $troubledSchedulesCount ?? 0 }} <small class="fs-6 text-muted">Jadwal</small></span>
                    </div>
                    <i class="fas fa-book-reader fs-4 text-primary opacity-75 me-2"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Aktivitas -->
    <div class="timeline-wrapper ms-2">

        <!-- Item 1: Absensi Harian -->
        @php
            $percentage = (isset($stats['total']) && $stats['total'] > 0) ? round(($stats['hadir'] / $stats['total']) * 100) : 0;
        @endphp
        <div class="timeline-block">
            <div class="timeline-icon-dot"><i class="fas fa-clock"></i></div>
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Presensi Harian</span>
                    <small class="text-muted" style="font-size: 0.75rem;">Aturan Absen</small>
                </div>
                <div class="small mb-2">
                    Masuk: <strong>{{ $attendanceSetting->start_check_in_time ?? '06:00:00' }} WIB</strong> | 
                    Batas: <strong class="text-danger">{{ $attendanceSetting->late_limit_time ?? '15:32:00' }} WIB</strong>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: {{ $percentage }}%;"></div>
                </div>
                <small class="text-muted mt-1 d-block" style="font-size: 0.7rem;">Kehadiran: {{ $percentage }}%</small>
            </div>
        </div>

        <!-- Item 2: Jadwal Pelajaran (Modal Trigger) -->
        <div class="timeline-block" id="jadwal">
            <div class="timeline-icon-dot"><i class="fas fa-calendar-day"></i></div>
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Jadwal Hari Ini</span>
                    <small class="text-muted fw-bold" style="font-size: 0.75rem;">{{ $todayIndo ?? \Carbon\Carbon::now()->locale('id')->isoFormat('dddd') }}</small>
                </div>

                @if(isset($todaySchedules) && count($todaySchedules) > 0)
                    <div class="row g-2">
                        @foreach($todaySchedules as $schedule)
                            @php
                                $subjectName = $schedule->subject->name ?? ($schedule->subject->subject_name ?? 'Mata Pelajaran');
                                $teacherName = $schedule->teacher->name ?? ($schedule->teacher->user->name ?? ($schedule->user->name ?? 'Guru Pengampu'));
                                $classroomName = $schedule->classroom->name ?? ($schedule->room->name ?? 'Kelas');
                                $timeRange = \Carbon\Carbon::parse($schedule->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($schedule->end_time)->format('H:i') . ' WIB';
                                $dayName = $todayIndo ?? \Carbon\Carbon::now()->locale('id')->isoFormat('dddd');
                            @endphp
                            <div class="col-12 col-md-6">
                                <div class="schedule-box d-flex justify-content-between align-items-center"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#modalDetailJadwal"
                                     data-subject="{{ $subjectName }}"
                                     data-teacher="{{ $teacherName }}"
                                     data-classroom="{{ $classroomName }}"
                                     data-time="{{ $timeRange }}"
                                     data-day="{{ $dayName }}">
                                    <div class="text-truncate me-2">
                                        <strong class="d-block text-dark small text-truncate">{{ $subjectName }}</strong>
                                        <small class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-user-tie me-1"></i>{{ $teacherName }}</small>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="badge bg-primary text-white" style="font-size: 0.65rem;">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</span>
                                        <small class="d-block text-muted" style="font-size: 0.65rem;">{{ $classroomName }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small text-center my-2"><i class="fas fa-info-circle me-1"></i>Tidak ada jadwal pelajaran aktif hari ini.</p>
                @endif
            </div>
        </div>

        <!-- Item 3: PKL AKTIF -->
        @php
            $activePklList = collect();
            if (isset($internshipTimeline) && is_iterable($internshipTimeline)) {
                $activePklList = $internshipTimeline;
            }
        @endphp

        @foreach($activePklList as $itemPkl)
            <div class="timeline-block">
                <div class="timeline-icon-dot"><i class="fas fa-briefcase"></i></div>
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill">PKL Aktif</span>
                        <small class="text-muted" style="font-size: 0.72rem;">
                            {{ !empty($itemPkl->start_date) ? \Carbon\Carbon::parse($itemPkl->start_date)->format('d/m/Y') : '-' }}
                        </small>
                    </div>
                    <strong class="text-dark small d-block mb-1">{{ $itemPkl->title ?? 'Pelaksanaan PKL' }}</strong>
                    <p class="text-muted small mb-0">{{ $itemPkl->description ?? 'Jadwal Praktik Kerja Lapangan berlangsung.' }}</p>
                </div>
            </div>
        @endforeach

    </div>

    <!-- Footer Simple -->
    <div class="text-center my-4 pt-3 border-top">
        <small class="text-muted d-block">&copy; {{ date('Y') }} {{ \App\Models\Setting::value('school_name', 'SMK Negeri 1 Bukittinggi') }}</small>
    </div>
</div>

<!-- MODAL DETAIL JADWAL RINGAN -->
<div class="modal fade" id="modalDetailJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-3 border-0 shadow">
            <div class="modal-header bg-primary text-white p-3">
                <h6 class="modal-title fw-bold" id="detailSubjectName">Detail Jadwal</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2">
                    <small class="text-muted d-block" style="font-size: 0.75rem;">GURU PENGAMPU</small>
                    <strong class="text-dark small" id="detailTeacherName">-</strong>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <small class="text-muted d-block" style="font-size: 0.75rem;">WAKTU</small>
                        <span class="badge bg-light text-dark border small" id="detailTimeRange">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block" style="font-size: 0.75rem;">KELAS</small>
                        <span class="badge bg-light text-dark border small" id="detailClassroom">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-2 bg-light border-0">
                <button type="button" class="btn btn-secondary btn-sm w-100 fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- JS Bootstrap Fast Load -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalDetailJadwal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function (e) {
                const btn = e.relatedTarget;
                document.getElementById('detailSubjectName').textContent = btn.getAttribute('data-subject');
                document.getElementById('detailTeacherName').textContent = btn.getAttribute('data-teacher');
                document.getElementById('detailClassroom').textContent = btn.getAttribute('data-classroom');
                document.getElementById('detailTimeRange').textContent = btn.getAttribute('data-time');
            });
        }
    });
</script>
</body>
</html>