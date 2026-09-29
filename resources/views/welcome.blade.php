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

    <title>Sistem Informasi SMK - Beranda | {{ \App\Models\Setting::where('key', 'app_name')->value('value') ?? 'GATECH' }} {{ \App\Models\Setting::where('key', 'school_name')->value('value') ?? 'SMK Negeri 1 Bukittinggi' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --bg-gradient: linear-gradient(135deg, #f0f2f5 0%, #c9d6ff 100%);
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Inter', sans-serif;
            color: #2b2d42;
            min-height: 100vh;
            padding-top: 85px;
            overflow-x: hidden;
        }

        /* Floating Navbar */
        .navbar-floating {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border-radius: 50px;
            margin: 10px auto;
            padding: 8px 18px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 92%;
            max-width: 1100px;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
        }

        .nav-link {
            font-weight: 600;
            color: #4a4e69 !important;
            margin: 0 5px;
            font-size: 0.9rem;
        }

        .btn-login {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white !important;
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        /* Stats Cards Layout */
        .stats-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.03);
            height: 100%;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stats-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.25rem;
        }

        .stats-info {
            flex: 1;
        }

        .stats-title {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 600;
            line-height: 1.2;
            display: block;
            margin-bottom: 4px;
        }

        .stats-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            color: #1a1d20;
        }

        /* Timeline Layout - Desktop */
        .timeline-container {
            position: relative;
            padding: 10px 0;
        }

        .timeline-container::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 3px;
            background: #cbd5e1;
            transform: translateX(-50%);
            border-radius: 10px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            width: 100%;
            display: flex;
        }

        .timeline-item:nth-child(odd) { justify-content: flex-start; }
        .timeline-item:nth-child(even) { justify-content: flex-end; }

        .timeline-card {
            width: 46%;
            background: #ffffff;
            padding: 1.25rem;
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0,0,0,0.03);
        }

        .timeline-dot {
            position: absolute;
            left: 50%;
            top: 15px;
            transform: translateX(-50%);
            width: 38px;
            height: 38px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            color: var(--primary-color);
            font-size: 1rem;
        }

        /* Schedule List Item */
        .schedule-list-item {
            background: #f8fafc;
            border-left: 4px solid var(--primary-color);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 8px;
        }

        /* PERBAIKAN KHUSUS LAYAR HP (MOBILE) */
        @media (max-width: 767.98px) {
            body {
                padding-top: 75px;
            }

            .navbar-floating {
                border-radius: 16px;
                width: 94%;
                padding: 8px 12px;
            }

            .timeline-container::before {
                left: 18px;
                transform: none;
            }

            .timeline-dot {
                left: 0;
                transform: none;
            }

            .timeline-item {
                justify-content: flex-start !important;
                margin-bottom: 1.5rem;
            }

            .timeline-card {
                width: calc(100% - 50px) !important;
                margin-left: 50px !important;
                padding: 1.1rem;
            }

            .timeline-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 4px;
            }
        }
    </style>
</head>
<body>

<!-- Floating Navbar -->
<nav class="navbar navbar-expand-lg navbar-floating">
    <div class="container-fluid px-1">
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center fs-6" href="#">
            <i class="fas fa-graduation-cap me-2 fs-5"></i>
            <span>{{ \App\Models\Setting::value('app_name', 'SISFO') }}<span class="text-dark">SMK</span></span>
        </a>
        <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="width: 1.2em; height: 1.2em;"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto my-2 my-lg-0 text-center">
                <li class="nav-item"><a class="nav-link active" href="#">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#timeline">Timeline</a></li>
                <li class="nav-item"><a class="nav-link" href="#jadwal">Jadwal Hari Ini</a></li>
                <li class="nav-item"><a class="nav-link" href="#rekap">Rekapitulasi</a></li>
            </ul>
            <div class="d-flex align-items-center justify-content-center gap-2 mt-2 mt-lg-0">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-login w-100 text-center"><i class="fas fa-gauge me-2"></i>Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-login w-100 text-center"><i class="fas fa-sign-in-alt me-2"></i>Masuk Sistem</a>
                    @endauth
                @endif
            </div>
        </div>
    </div>
</nav>

<div class="container px-3">
    <!-- Header -->
    <div class="text-center mb-4 mt-2">
        <h1 class="fw-bold fs-3 fs-md-1 mb-2">Informasi <span class="text-primary">Terintegrasi</span></h1>
        <p class="text-muted small mx-auto mb-0" style="max-width: 550px;">Pantau aktivitas sekolah, statistik kehadiran, jadwal pelajaran, timeline PKL, dan jurnal pengajaran secara real-time.</p>
    </div>

    <!-- Live Metric Cards -->
    <div class="row g-3 mb-4" id="rekap">
        <!-- Hadir Hari Ini -->
        <div class="col-12 col-md-3">
            <div class="stats-card">
                <div class="stats-icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stats-info">
                    <span class="stats-title">Hadir Hari Ini</span>
                    <div class="stats-value">{{ $stats['hadir'] }}</div>
                </div>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="col-12 col-md-3">
            <div class="stats-card">
                <div class="stats-icon-box bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stats-info">
                    <span class="stats-title">Terlambat</span>
                    <div class="stats-value">{{ $stats['terlambat'] }}</div>
                </div>
            </div>
        </div>

        <!-- Sakit / Izin -->
        <div class="col-12 col-md-3">
            <div class="stats-card">
                <div class="stats-icon-box bg-info bg-opacity-10 text-info">
                    <i class="fas fa-notes-medical"></i>
                </div>
                <div class="stats-info">
                    <span class="stats-title">Sakit / Izin</span>
                    <div class="stats-value">{{ $stats['sakit'] }}</div>
                </div>
            </div>
        </div>

        <!-- Tanpa Keterangan -->
        <div class="col-12 col-md-3">
            <div class="stats-card">
                <div class="stats-icon-box bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stats-info">
                    <span class="stats-title">Tanpa Keterangan</span>
                    <div class="stats-value">{{ $stats['alpa'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Dinamis -->
    <div class="timeline-container" id="timeline">

        <!-- Item 1: Status Presensi Hari Ini -->
        @php
            $percentage = $stats['total'] > 0 ? round(($stats['hadir'] / $stats['total']) * 100) : 0;
        @endphp
        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-clock"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                    <span class="badge bg-success rounded-pill px-2 py-1">KEHADIRAN</span>
                    <small class="text-muted fw-semibold">Jadwal Absensi</small>
                </div>
                <h6 class="fw-bold fs-6 mb-2">Pengaturan Absensi Harian</h6>
                <p class="text-muted small mb-2 lh-sm">
                    Waktu Masuk: <strong>{{ $attendanceSetting->start_check_in_time ?? '07:00' }} WIB</strong><br>
                    Batas Terlambat: <strong class="text-danger">{{ $attendanceSetting->late_limit_time ?? '07:30' }} WIB</strong>
                </p>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-2 d-block font-monospace">Tingkat Kehadiran: {{ $percentage }}%</small>
            </div>
        </div>

        <!-- Item 2: Jadwal Pelajaran Hari Ini -->
        <div class="timeline-item" id="jadwal">
            <div class="timeline-dot"><i class="fas fa-calendar-day"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                    <span class="badge bg-primary text-white rounded-pill px-2 py-1">JADWAL HARI INI</span>
                    <small class="text-muted fw-semibold"><i class="far fa-calendar-alt me-1"></i>{{ $todayIndo ?? \Carbon\Carbon::now()->locale('id')->isoFormat('dddd') }}</small>
                </div>
                <h6 class="fw-bold fs-6 mb-2">Jadwal Pelajaran Aktif</h6>

                @if(isset($todaySchedules) && $todaySchedules->count() > 0)
                    <div class="mt-2">
                        @foreach($todaySchedules as $schedule)
                            <div class="schedule-list-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-dark small">
                                        {{ $schedule->subject->name ?? ($schedule->subject->subject_name ?? 'Mata Pelajaran') }}
                                    </strong>
                                    <span class="badge bg-light text-primary border" style="font-size: 0.7rem;">
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 0.78rem;">
                                    <span><i class="fas fa-chalkboard-teacher me-1"></i>{{ $schedule->teacher->name ?? ($schedule->teacher->user->name ?? 'Guru Pengampu') }}</span>
                                    <span><i class="fas fa-door-open me-1"></i>{{ $schedule->classroom->name ?? ($schedule->room->name ?? 'Kelas') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3 bg-light rounded-3">
                        <i class="fas fa-coffee text-muted mb-2 fs-4"></i>
                        <p class="text-muted small mb-0">Tidak ada jadwal pelajaran aktif untuk hari ini.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Item 3: PRAKERIN / PKL AKTIF (Mendukung Multi-Data PKL & Single-Data) -->
        @if(isset($internshipTimelines) && $internshipTimelines->count() > 0)
            @foreach($internshipTimelines as $itemPkl)
            <div class="timeline-item">
                <div class="timeline-dot"><i class="fas fa-briefcase"></i></div>
                <div class="timeline-card">
                    <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1">
                            <i class="fas fa-circle text-success me-1" style="font-size: 0.5rem;"></i> PKL AKTIF
                        </span>
                        <small class="text-muted fw-semibold">
                            @if(isset($itemPkl->start_date) && isset($itemPkl->end_date))
                                {{ \Carbon\Carbon::parse($itemPkl->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($itemPkl->end_date)->format('d M Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($itemPkl->start_date ?? now())->format('d M Y') }}
                            @endif
                        </small>
                    </div>
                    <h6 class="fw-bold fs-6 mb-1">{{ $itemPkl->title ?? 'Pelaksanaan PKL / Magang' }}</h6>
                    <p class="text-muted small mb-0 lh-sm">{{ $itemPkl->description ?? 'Jadwal pelaksanaan Praktik Kerja Lapangan yang sedang berlangsung.' }}</p>
                </div>
            </div>
            @endforeach
        @elseif(isset($internshipTimeline) && $internshipTimeline)
            <div class="timeline-item">
                <div class="timeline-dot"><i class="fas fa-briefcase"></i></div>
                <div class="timeline-card">
                    <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1">
                            <i class="fas fa-circle text-success me-1" style="font-size: 0.5rem;"></i> PKL AKTIF
                        </span>
                        <small class="text-muted fw-semibold">
                            @if(isset($internshipTimeline->start_date) && isset($internshipTimeline->end_date))
                                {{ \Carbon\Carbon::parse($internshipTimeline->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($internshipTimeline->end_date)->format('d M Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($internshipTimeline->start_date ?? now())->format('d M Y') }}
                            @endif
                        </small>
                    </div>
                    <h6 class="fw-bold fs-6 mb-1">{{ $internshipTimeline->title ?? 'Pelaksanaan PKL / Magang' }}</h6>
                    <p class="text-muted small mb-0 lh-sm">{{ $internshipTimeline->description ?? 'Jadwal pelaksanaan Praktik Kerja Lapangan yang sedang berlangsung.' }}</p>
                </div>
            </div>
        @endif

        <!-- Item 4: Jurnal Mengajar -->
        @if($latestJournal)
        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-book-open"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                    <span class="badge bg-info text-dark rounded-pill px-2 py-1">JURNAL MENGAJAR</span>
                    <small class="text-muted fw-semibold">{{ $latestJournal->created_at->diffForHumans() }}</small>
                </div>
                <h6 class="fw-bold fs-6 mb-1">{{ $latestJournal->subject->name ?? 'Kegiatan Mengajar' }}</h6>
                <p class="text-muted small mb-1 lh-sm">Pengajar: <strong>{{ $latestJournal->teacher->name ?? 'Guru Pengampu' }}</strong></p>
                <p class="text-muted small mb-0 lh-sm">Materi: {{ Str::limit($latestJournal->notes ?? 'Penyampaian materi dan praktik kelas.', 100) }}</p>
            </div>
        </div>
        @endif

        <!-- Item 5: Catatan Tahfiz -->
        @if($latestTahfiz)
        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-quran"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                    <span class="badge bg-danger rounded-pill px-2 py-1">TAHFIZ AL-QUR'AN</span>
                    <small class="text-muted fw-semibold">{{ $latestTahfiz->created_at->format('H:i') }} WIB</small>
                </div>
                <h6 class="fw-bold fs-6 mb-1">Setoran Hafalan Terbaru</h6>
                <p class="text-muted small mb-1 lh-sm">Siswa: <strong>{{ $latestTahfiz->student->name ?? 'Siswa' }}</strong></p>
                <p class="text-muted small mb-0 lh-sm">Capaian: Surah/Juz <span class="badge bg-light text-dark border">{{ $latestTahfiz->surah_or_juz ?? '-' }}</span></p>
            </div>
        </div>
        @endif

        <!-- Item 6: Status WhatsApp Gateway (POSISI PALING BAWAH) -->
        <div class="timeline-item" id="status">
            <div class="timeline-dot"><i class="fab fa-whatsapp"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                    <span class="badge bg-primary rounded-pill px-2 py-1">SISTEM NOTIFIKASI</span>
                    <small class="text-muted fw-semibold">Real-time</small>
                </div>
                <h6 class="fw-bold fs-6 mb-2">Status WhatsApp Gateway</h6>
                <p class="text-muted small mb-2 lh-sm">
                    Layanan notifikasi otomatis pesan kehadiran dan pengumuman wali murid saat ini:
                </p>
                @if($isWaActive)
                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-2"><i class="fas fa-check-circle me-1"></i> Terhubung</span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1 rounded-2"><i class="fas fa-exclamation-circle me-1"></i> Terputus</span>
                @endif
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="text-center my-4">
        <p class="text-muted small mb-0">&copy; {{ date('Y') }} {{ \App\Models\Setting::value('school_name', 'SMK Negeri 1 Bukittinggi') }}</p>
        <p class="text-muted small" style="font-size: 0.75rem;">Dikembangkan oleh {{ \App\Models\Setting::value('developer_name', 'Gustifa Fauzan') }}</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.bundle.min.js"></script>
</body>
</html>
