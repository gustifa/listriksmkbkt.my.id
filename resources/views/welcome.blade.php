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
            overflow-x: hidden; /* Mencegah scroll samping */
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

        /* Stats Cards - Diperbaiki untuk Mobile */
        .stats-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 12px 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.03);
            height: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }

        .stats-info {
            min-width: 0; /* Agar teks ellipsis / word wrap bekerja */
            flex: 1;
        }

        .stats-title {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 600;
            line-height: 1.2;
            display: block;
            word-break: break-word; /* Mencegah teks terpotong */
            margin-bottom: 2px;
        }

        .stats-value {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1;
            color: #1a1d20;
        }

        /* Timeline Layout - Diperbaiki total untuk Desktop & Mobile */
        .timeline-container {
            position: relative;
            padding: 10px 0;
        }

        /* Garis Tengah Desktop */
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
            width: 36px;
            height: 36px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            color: var(--primary-color);
            font-size: 0.9rem;
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

            /* Pindahkan Garis Timeline ke Kiri pada Layar HP */
            .timeline-container::before {
                left: 18px;
                transform: none;
            }

            .timeline-dot {
                left: 0;
                transform: none;
                width: 36px;
                height: 36px;
            }

            .timeline-item {
                justify-content: flex-start !important;
                margin-bottom: 1.5rem;
            }

            .timeline-card {
                width: calc(100% - 50px) !important;
                margin-left: 50px !important;
                padding: 1rem;
            }

            /* Header Kartu Timeline di HP (Badge + Subtitle) */
            .timeline-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 4px;
            }

            .timeline-header small {
                font-size: 0.7rem !important;
            }

            .stats-card {
                padding: 10px;
            }
            .stats-icon-box {
                width: 34px;
                height: 34px;
                font-size: 0.95rem;
            }
            .stats-title {
                font-size: 0.7rem;
            }
            .stats-value {
                font-size: 1.1rem;
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
                <li class="nav-item"><a class="nav-link" href="#rekap">Rekapitulasi</a></li>
                <li class="nav-item"><a class="nav-link" href="#status">Status Sistem</a></li>
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
        <p class="text-muted small mx-auto mb-0" style="max-width: 550px; font-size: 0.85rem;">Pantau aktivitas sekolah, statistik kehadiran, timeline PKL, jurnal pengajaran, dan status WhatsApp Gateway secara real-time.</p>
    </div>

    <!-- Live Metric Cards (Grid 2 Kolom Rapi di HP) -->
    <div class="row g-2 g-md-3 mb-4" id="rekap">
        <!-- Hadir Hari Ini -->
        <div class="col-6 col-md-3">
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
        <div class="col-6 col-md-3">
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
        <div class="col-6 col-md-3">
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
        <div class="col-6 col-md-3">
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
                    <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">KEHADIRAN</span>
                    <small class="text-muted fw-semibold">Jadwal Hari Ini</small>
                </div>
                <h6 class="fw-bold fs-6 mb-2">Pengaturan Absensi Harian</h6>
                <p class="text-muted small mb-2 lh-sm" style="font-size: 0.8rem;">
                    Waktu Masuk: <strong>{{ $attendanceSetting->start_check_in_time ?? '07:00' }} WIB</strong><br>
                    Batas Terlambat: <strong class="text-danger">{{ $attendanceSetting->late_limit_time ?? '07:30' }} WIB</strong>
                </p>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-2 d-block font-monospace" style="font-size: 0.72rem;">Tingkat Kehadiran: {{ $percentage }}%</small>
            </div>
        </div>

        <!-- Item 2: Status WhatsApp Gateway -->
        <div class="timeline-item" id="status">
            <div class="timeline-dot"><i class="fab fa-whatsapp"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                    <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 0.7rem;">SISTEM NOTIFIKASI</span>
                    <small class="text-muted fw-semibold">Real-time</small>
                </div>
                <h6 class="fw-bold fs-6 mb-2">Status WhatsApp Gateway</h6>
                <p class="text-muted small mb-2 lh-sm" style="font-size: 0.8rem;">
                    Layanan notifikasi otomatis pesan kehadiran dan pengumuman wali murid saat ini:
                </p>
                @if($isWaActive)
                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-2" style="font-size: 0.75rem;"><i class="fas fa-check-circle me-1"></i> Terhubung</span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1 rounded-2" style="font-size: 0.75rem;"><i class="fas fa-exclamation-circle me-1"></i> Terputus</span>
                @endif
            </div>
        </div>

        <!-- Item 3: Timeline PKL -->
        @if($internshipTimeline)
        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-briefcase"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 0.7rem;">PRAKERIN / PKL</span>
                    <small class="text-muted fw-semibold">{{ \Carbon\Carbon::parse($internshipTimeline->start_date ?? now())->format('d M Y') }}</small>
                </div>
                <h6 class="fw-bold fs-6 mb-1">{{ $internshipTimeline->title ?? 'Pelaksanaan PKL / Magang' }}</h6>
                <p class="text-muted small mb-0 lh-sm" style="font-size: 0.8rem;">{{ $internshipTimeline->description ?? 'Jadwal pelaksanaan Praktik Kerja Lapangan.' }}</p>
            </div>
        </div>
        @endif

        <!-- Item 4: Jurnal Mengajar -->
        @if($latestJournal)
        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-book-open"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                    <span class="badge bg-info text-dark rounded-pill px-2 py-1" style="font-size: 0.7rem;">JURNAL MENGAJAR</span>
                    <small class="text-muted fw-semibold">{{ $latestJournal->created_at->diffForHumans() }}</small>
                </div>
                <h6 class="fw-bold fs-6 mb-1">{{ $latestJournal->subject->name ?? 'Kegiatan Mengajar' }}</h6>
                <p class="text-muted small mb-1 lh-sm" style="font-size: 0.8rem;">Pengajar: <strong>{{ $latestJournal->teacher->name ?? 'Guru Pengampu' }}</strong></p>
                <p class="text-muted small mb-0 lh-sm" style="font-size: 0.8rem;">Materi: {{ Str::limit($latestJournal->notes ?? 'Penyampaian materi dan praktik kelas.', 100) }}</p>
            </div>
        </div>
        @endif

        <!-- Item 5: Catatan Tahfiz -->
        @if($latestTahfiz)
        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-quran"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2 timeline-header">
                    <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">TAHFIZ AL-QUR'AN</span>
                    <small class="text-muted fw-semibold">{{ $latestTahfiz->created_at->format('H:i') }} WIB</small>
                </div>
                <h6 class="fw-bold fs-6 mb-1">Setoran Hafalan Terbaru</h6>
                <p class="text-muted small mb-1 lh-sm" style="font-size: 0.8rem;">Siswa: <strong>{{ $latestTahfiz->student->name ?? 'Siswa' }}</strong></p>
                <p class="text-muted small mb-0 lh-sm" style="font-size: 0.8rem;">Capaian: Surah/Juz <span class="badge bg-light text-dark border">{{ $latestTahfiz->surah_or_juz ?? '-' }}</span></p>
            </div>
        </div>
        @endif

    </div>

    <!-- Footer -->
    <div class="text-center my-4">
        <p class="text-muted small mb-0">&copy; {{ date('Y') }} {{ \App\Models\Setting::value('school_name', 'SMK Negeri 1 Bukittinggi') }}</p>
        <p class="text-muted small" style="font-size: 0.72rem;">Dikembangkan oleh {{ \App\Models\Setting::value('developer_name', 'Gustifa Fauzan') }}</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
