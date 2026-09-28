<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- FAVICON DINAMIS DARI SETTING -->
    @php
        // Ambil favicon dari database (app_favicon -> logo_left -> fallback default)
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #f72585;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --bg-gradient: linear-gradient(135deg, #f0f2f5 0%, #c9d6ff 100%);
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Inter', sans-serif;
            color: #2b2d42;
            min-height: 100vh;
            padding-top: 100px;
        }

        .navbar-floating {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border-radius: 50px;
            margin: 20px auto;
            padding: 10px 25px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 90%;
            max-width: 1100px;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav-link {
            font-weight: 600;
            color: #4a4e69 !important;
            margin: 0 10px;
            font-size: 0.95rem;
        }

        .nav-link:hover { color: var(--primary-color) !important; }

        .btn-login {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white !important;
            border-radius: 30px;
            padding: 8px 25px;
            font-weight: 700;
            border: none;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
            color: #fff;
        }

        .stats-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: none;
            transition: transform 0.3s ease;
        }
        .stats-card:hover { transform: translateY(-5px); }

        .timeline-container { position: relative; padding: 2rem 0; }
        .timeline-container::before {
            content: ''; position: absolute; top: 0; left: 50%; width: 4px; height: 100%;
            background: linear-gradient(to bottom, #4361ee, #4cc9f0, #f72585);
            transform: translateX(-50%); border-radius: 10px; opacity: 0.2;
        }

        .timeline-item { position: relative; margin-bottom: 3rem; width: 100%; display: flex; align-items: center; }
        .timeline-item:nth-child(odd) { justify-content: flex-start; }
        .timeline-item:nth-child(even) { justify-content: flex-end; }

        .timeline-card {
            width: 45%; background: #ffffff; padding: 1.8rem; border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05); transition: all 0.4s ease;
        }

        .timeline-card:hover { transform: translateY(-10px); }

        .timeline-dot {
            position: absolute; left: 50%; transform: translateX(-50%);
            width: 45px; height: 45px; background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            z-index: 5; box-shadow: 0 0 15px rgba(0,0,0,0.1); color: var(--primary-color);
        }

        @media (max-width: 992px) {
            .navbar-floating { border-radius: 20px; width: 95%; }
            .timeline-container::before { left: 30px; }
            .timeline-dot { left: 30px; }
            .timeline-card { width: calc(100% - 75px); margin-left: 75px !important; }
            .timeline-item { justify-content: flex-start !important; }
        }
    </style>
</head>
<body>

<!-- Floating Navbar -->
<nav class="navbar navbar-expand-lg navbar-floating">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="#">
            <img src="{{ asset('storage/' . \App\Models\Setting::value('logo', 'logo.png')) }}" alt="Logo" width="30" height="30" class="me-2" onerror="this.style.display='none'">
            <i class="fas fa-graduation-cap me-2"></i>{{ \App\Models\Setting::value('app_name', 'SISFO') }}<span class="text-dark">SMK</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link active" href="#">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#timeline">Timeline</a></li>
                <li class="nav-item"><a class="nav-link" href="#rekap">Rekapitulasi</a></li>
                <li class="nav-item"><a class="nav-link" href="#status">Status Sistem</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-login"><i class="fas fa-gauge me-2"></i>Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-login"><i class="fas fa-sign-in-alt me-2"></i>Masuk Sistem</a>
                    @endauth
                @endif
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <div class="text-center mb-5 mt-4">
        <h1 class="fw-bold display-5 mb-3">Informasi <span class="text-primary">Terintegrasi</span></h1>
        <p class="text-muted mx-auto" style="max-width: 600px;">Pantau aktivitas sekolah, statistik kehadiran, timeline PKL, jurnal pengajaran, dan status WhatsApp Gateway secara real-time.</p>
    </div>

    <!-- Live Metric Cards -->
    <div class="row g-3 mb-5" id="rekap">
        <div class="col-6 col-lg-3">
            <div class="stats-card d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                    <i class="fas fa-user-check fa-xl"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Hadir Hari Ini</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['hadir'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stats-card d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3">
                    <i class="fas fa-clock fa-xl"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Terlambat</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['terlambat'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stats-card d-flex align-items-center">
                <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 me-3">
                    <i class="fas fa-notes-medical fa-xl"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Sakit / Izin</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['sakit'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stats-card d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 me-3">
                    <i class="fas fa-user-times fa-xl"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Tanpa Keterangan</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['alpa'] }}</h3>
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
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-success rounded-pill px-3">KEHADIRAN</span>
                    <small class="text-muted">Jadwal Hari Ini</small>
                </div>
                <h5 class="fw-bold">Pengaturan Absensi Harian</h5>
                <p class="text-muted small mb-2">
                    Waktu Masuk: <strong>{{ $attendanceSetting->start_check_in_time ?? '07:00' }} WIB</strong><br>
                    Batas Terlambat: <strong class="text-danger">{{ $attendanceSetting->late_limit_time ?? '07:30' }} WIB</strong>
                </p>
                <div class="progress" style="height: 6px;">
                    <!-- Diganti ke tag PHP native di atribut agar VS Code tidak membaca error CSS -->
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-1 d-block font-monospace">Tingkat Kehadiran: {{ $percentage }}%</small>
            </div>
        </div>

        <!-- Item 2: Status WhatsApp Gateway -->
        <div class="timeline-item" id="status">
            <div class="timeline-dot"><i class="fab fa-whatsapp"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-primary rounded-pill px-3">SISTEM NOTIFIKASI</span>
                    <small class="text-muted">Real-time</small>
                </div>
                <h5 class="fw-bold">Status WhatsApp Gateway</h5>
                <p class="text-muted small mb-2">
                    Layanan notifikasi otomatis pesan kehadiran dan pengumuman wali murid saat ini berstatus:
                </p>
                @if($isWaActive)
                    <span class="badge bg-success px-3 py-2 rounded-3"><i class="fas fa-circle-check me-1"></i> Connected (Terhubung)</span>
                @else
                    <span class="badge bg-danger px-3 py-2 rounded-3"><i class="fas fa-circle-exclamation me-1"></i> Disconnected (Terputus)</span>
                @endif
            </div>
        </div>

        <!-- Item 3: Timeline PKL -->
        @if($internshipTimeline)
        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-briefcase"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-warning text-dark rounded-pill px-3">PRAKERIN / PKL</span>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($internshipTimeline->start_date ?? now())->format('d M Y') }}</small>
                </div>
                <h5 class="fw-bold">{{ $internshipTimeline->title ?? 'Pelaksanaan PKL / Magang' }}</h5>
                <p class="text-muted small mb-0">{{ $internshipTimeline->description ?? 'Jadwal pelaksanaan Praktik Kerja Lapangan bagi siswa-siswi SMK Negeri 1 Bukittinggi.' }}</p>
            </div>
        </div>
        @endif

        <!-- Item 4: Jurnal Mengajar -->
        @if($latestJournal)
        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-book-open"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-info text-dark rounded-pill px-3">JURNAL MENGAJAR</span>
                    <small class="text-muted">{{ $latestJournal->created_at->diffForHumans() }}</small>
                </div>
                <h5 class="fw-bold">{{ $latestJournal->subject->name ?? 'Kegiatan Mengajar' }}</h5>
                <p class="text-muted small mb-1">Pengajar: <strong>{{ $latestJournal->teacher->name ?? 'Guru Pengampu' }}</strong></p>
                <p class="text-muted small mb-0">Materi: {{ Str::limit($latestJournal->notes ?? 'Penyampaian materi dan praktik kelas.', 120) }}</p>
            </div>
        </div>
        @endif

        <!-- Item 5: Catatan Tahfiz -->
        @if($latestTahfiz)
        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-quran"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-danger rounded-pill px-3">TAHFIZ AL-QUR'AN</span>
                    <small class="text-muted">{{ $latestTahfiz->created_at->format('H:i') }} WIB</small>
                </div>
                <h5 class="fw-bold">Setoran Hafalan Terbaru</h5>
                <p class="text-muted small mb-1">Siswa: <strong>{{ $latestTahfiz->student->name ?? 'Siswa' }}</strong></p>
                <p class="text-muted small mb-0">Capaian: Surah/Juz <span class="badge bg-light text-dark border">{{ $latestTahfiz->surah_or_juz ?? '-' }}</span></p>
            </div>
        </div>
        @endif

    </div>

    <!-- Footer -->
    <div class="text-center my-5">
        <p class="text-muted small">&copy; {{ date('Y') }} {{ \App\Models\Setting::value('school_name', 'SMK Negeri 1 Bukittinggi') }} - Dikembangkan oleh {{ \App\Models\Setting::value('developer_name', 'Gustifa Fauzan') }}</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
