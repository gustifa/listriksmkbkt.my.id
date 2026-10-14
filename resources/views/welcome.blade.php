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

    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #3a7bd5 0%, #3a6073 100%);
            --accent-blue: #4361ee;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 15px 35px rgba(67, 97, 238, 0.15);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --border-glass: rgba(255, 255, 255, 0.6);
        }

        body {
            background: #f4f7fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(67, 97, 238, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(58, 123, 213, 0.08) 0px, transparent 50%);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #2b2d42;
            min-height: 100vh;
            padding-top: 100px;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        /* Floating Navbar */
        .navbar-floating {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 50px;
            margin: 15px auto;
            padding: 10px 24px;
            border: 1px solid var(--border-glass);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            width: 90%;
            max-width: 1140px;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav-link {
            font-weight: 600;
            color: #4a5568 !important;
            margin: 0 8px;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--accent-blue) !important;
        }

        .btn-login {
            background: linear-gradient(135deg, #4361ee, #3a7bd5);
            color: white !important;
            border-radius: 30px;
            padding: 10px 24px;
            font-weight: 700;
            border: none;
            box-shadow: 0 6px 18px rgba(67, 97, 238, 0.35);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.45);
        }

        /* Hero Header */
        .hero-title {
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Metric Cards Modern */
        .stats-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .stats-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            transition: all 0.3s ease;
        }

        .stats-card:hover .stats-icon-box {
            transform: scale(1.1) rotate(5deg);
        }

        .stats-title {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stats-value {
            font-size: 1.85rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }

        /* Timeline Layout Centered */
        .timeline-container {
            position: relative;
            padding: 20px 0;
            margin-top: 10px;
        }

        .timeline-container::before {
            content: '';
            position: absolute;
            top: 0; bottom: 0;
            left: 50%;
            width: 3px;
            background: linear-gradient(180deg, #cbd5e1 0%, #e2e8f0 100%);
            transform: translateX(-50%);
            border-radius: 10px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2.5rem;
            width: 100%;
            display: flex;
        }

        .timeline-item:nth-child(odd) { justify-content: flex-start; }
        .timeline-item:nth-child(even) { justify-content: flex-end; }

        .timeline-card {
            width: 46%;
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s ease;
        }

        .timeline-card:hover {
            box-shadow: var(--hover-shadow);
        }

        .timeline-dot {
            position: absolute;
            left: 50%;
            top: 20px;
            transform: translateX(-50%);
            width: 44px;
            height: 44px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: var(--accent-blue);
            font-size: 1.1rem;
            border: 2px solid #f1f5f9;
        }

        /* Schedule Grid Layout */
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 12px;
            max-height: 400px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .schedule-card-item {
            background: #f8fafc;
            border-radius: 14px;
            padding: 12px 14px;
            border-left: 4px solid var(--accent-blue);
            transition: all 0.2s ease;
        }

        .schedule-card-item:hover {
            background: #ffffff;
            box-shadow: 0 6px 16px rgba(67, 97, 238, 0.12);
            transform: translateY(-2px);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* Responsive Mobile Adjustments */
        @media (max-width: 991.98px) {
            .timeline-container::before {
                left: 20px;
                transform: none;
            }

            .timeline-dot {
                left: 0;
                transform: none;
                width: 40px;
                height: 40px;
            }

            .timeline-item {
                justify-content: flex-start !important;
            }

            .timeline-card {
                width: calc(100% - 55px) !important;
                margin-left: 55px !important;
                padding: 1.25rem;
            }

            .schedule-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Floating Navbar -->
<nav class="navbar navbar-expand-lg navbar-floating">
    <div class="container-fluid px-1">
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center fs-6" href="#">
            <div class="bg-primary text-white rounded-3 p-2 me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span class="fw-extrabold text-dark" style="letter-spacing: -0.5px;">
                {{ \App\Models\Setting::value('app_name', 'SISFO') }}<span class="text-primary">SMK</span>
            </span>
        </a>
        <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="width: 1.2em; height: 1.2em;"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto my-2 my-lg-0 text-center">
                <li class="nav-item"><a class="nav-link active" href="#"><i class="fas fa-home me-1 opacity-50"></i>Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#rekap"><i class="fas fa-chart-pie me-1 opacity-50"></i>Rekapitulasi</a></li>
                <li class="nav-item"><a class="nav-link" href="#jadwal"><i class="fas fa-calendar-alt me-1 opacity-50"></i>Jadwal Hari Ini</a></li>
                <li class="nav-item"><a class="nav-link" href="#timeline"><i class="fas fa-stream me-1 opacity-50"></i>Timeline Aktivitas</a></li>
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

<div class="container px-3 px-md-4">
    
    <!-- Hero Header -->
    <div class="text-center mb-5 mt-3" data-aos="fade-down" data-aos-duration="1000">
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-2">
            <i class="fas fa-bolt me-1"></i> DASHBOARD INFORMASI REAL-TIME
        </span>
        <h1 class="hero-title display-5 mb-2">Informasi <span class="text-primary">Terintegrasi</span></h1>
        <p class="text-muted mx-auto mb-0" style="max-width: 600px; font-size: 0.95rem;">
            Pantau aktivitas sekolah, statistik kehadiran, jadwal pelajaran, timeline PKL, dan jurnal pengajaran secara transparan & akurat.
        </p>
    </div>

    <!-- Live Metric Cards -->
    <div class="row g-3 mb-5" id="rekap">
        <!-- Hadir Hari Ini -->
        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
            <div class="stats-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="stats-title">Hadir Hari Ini</span>
                        <div class="stats-value text-success">{{ $stats['hadir'] ?? 0 }}</div>
                    </div>
                    <div class="stats-icon-box bg-success bg-opacity-10 text-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
            <div class="stats-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="stats-title">Terlambat</span>
                        <div class="stats-value text-warning">{{ $stats['terlambat'] ?? 0 }}</div>
                    </div>
                    <div class="stats-icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sakit / Izin -->
        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
            <div class="stats-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="stats-title">Sakit / Izin</span>
                        <div class="stats-value text-info">{{ $stats['sakit'] ?? 0 }}</div>
                    </div>
                    <div class="stats-icon-box bg-info bg-opacity-10 text-info">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tanpa Keterangan -->
        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
            <div class="stats-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="stats-title">Tanpa Keterangan</span>
                        <div class="stats-value text-danger">{{ $stats['alpa'] ?? 0 }}</div>
                    </div>
                    <div class="stats-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Dinamis -->
    <div class="timeline-container" id="timeline">

        <!-- Item 1: Status Presensi Hari Ini -->
        @php
            $percentage = (isset($stats['total']) && $stats['total'] > 0) ? round(($stats['hadir'] / $stats['total']) * 100) : 0;
        @endphp
        <div class="timeline-item" data-aos="fade-right">
            <div class="timeline-dot"><i class="fas fa-user-clock"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold">KEHADIRAN</span>
                    <small class="text-muted fw-semibold"><i class="far fa-clock me-1"></i>Jadwal Absensi</small>
                </div>
                <h6 class="fw-bold fs-6 mb-2">Pengaturan Absensi Harian</h6>
                <div class="bg-light rounded-3 p-2 mb-3 border">
                    <p class="text-muted small mb-1">
                        Waktu Masuk: <strong class="text-dark">{{ $attendanceSetting->start_check_in_time ?? '06:00:00' }} WIB</strong>
                    </p>
                    <p class="text-muted small mb-0">
                        Batas Terlambat: <strong class="text-danger">{{ $attendanceSetting->late_limit_time ?? '15:32:00' }} WIB</strong>
                    </p>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Tingkat Kehadiran</span>
                    <span class="small fw-bold text-success">{{ $percentage }}%</span>
                </div>
                <div class="progress" style="height: 8px; border-radius: 10px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $percentage }}%;"></div>
                </div>
            </div>
        </div>

        <!-- Item 2: Jadwal Pelajaran Hari Ini (Clickable Modal Grid) -->
        <div class="timeline-item" id="jadwal" data-aos="fade-left">
            <div class="timeline-dot"><i class="fas fa-calendar-day"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold">JADWAL HARI INI</span>
                    <small class="text-muted fw-semibold"><i class="far fa-calendar-alt me-1"></i>{{ $todayIndo ?? \Carbon\Carbon::now()->locale('id')->isoFormat('dddd') }}</small>
                </div>
                <h6 class="fw-bold fs-6 mb-3">Jadwal Pelajaran Aktif</h6>

                @if(isset($todaySchedules) && count($todaySchedules) > 0)
                    <div class="schedule-grid">
                        @foreach($todaySchedules as $schedule)
                            @php
                                $subjectName = $schedule->subject->name ?? ($schedule->subject->subject_name ?? 'Mata Pelajaran');
                                $teacherName = $schedule->teacher->name ?? ($schedule->teacher->user->name ?? ($schedule->user->name ?? 'Guru Pengampu'));
                                $classroomName = $schedule->classroom->name ?? ($schedule->room->name ?? 'Kelas');
                                $timeRange = \Carbon\Carbon::parse($schedule->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($schedule->end_time)->format('H:i') . ' WIB';
                                $dayName = $todayIndo ?? \Carbon\Carbon::now()->locale('id')->isoFormat('dddd');
                            @endphp

                            <!-- Card Item Jadwal (Clickable) -->
                            <div class="schedule-card-item cursor-pointer shadow-sm position-relative overflow-hidden" 
                                 style="cursor: pointer;"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#modalDetailJadwal"
                                 data-subject="{{ $subjectName }}"
                                 data-teacher="{{ $teacherName }}"
                                 data-classroom="{{ $classroomName }}"
                                 data-time="{{ $timeRange }}"
                                 data-day="{{ $dayName }}">
                                
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small text-truncate pe-2" style="max-width: 170px;">
                                        {{ $subjectName }}
                                    </strong>
                                    <span class="badge bg-primary text-white rounded-pill" style="font-size: 0.7rem;">
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                    </span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.78rem;">
                                    <span class="text-truncate me-1" style="max-width: 150px;">
                                        <i class="fas fa-chalkboard-teacher me-1 text-primary"></i>{{ $teacherName }}
                                    </span>
                                    <span class="badge bg-light text-dark border flex-shrink-0">
                                        <i class="fas fa-door-open me-1"></i>{{ $classroomName }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 bg-light rounded-3">
                        <i class="fas fa-coffee text-muted mb-2 fs-3 opacity-50"></i>
                        <p class="text-muted small mb-0 fw-medium">Tidak ada jadwal pelajaran aktif untuk hari ini.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Item 3: PKL AKTIF -->
        @php
            $activePklList = collect();
            if (isset($internshipTimelines) && is_iterable($internshipTimelines)) {
                $activePklList = $internshipTimelines;
            } elseif (isset($internshipTimeline) && is_iterable($internshipTimeline)) {
                $activePklList = $internshipTimeline;
            } elseif (isset($internshipTimeline) && !empty($internshipTimeline)) {
                $activePklList = collect([$internshipTimeline]);
            }
        @endphp

        @foreach($activePklList as $itemPkl)
            <div class="timeline-item" data-aos="fade-right">
                <div class="timeline-dot"><i class="fas fa-briefcase"></i></div>
                <div class="timeline-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-warning bg-opacity-10 text-dark rounded-pill px-3 py-1 fw-bold">
                            <i class="fas fa-circle text-success me-1" style="font-size: 0.5rem;"></i> PKL AKTIF
                        </span>
                        <small class="text-muted fw-semibold">
                            @if(!empty($itemPkl->start_date) && !empty($itemPkl->end_date))
                                {{ \Carbon\Carbon::parse($itemPkl->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($itemPkl->end_date)->format('d M Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($itemPkl->start_date ?? now())->format('d M Y') }}
                            @endif
                        </small>
                    </div>
                    <h6 class="fw-bold fs-6 mb-1 text-dark">{{ $itemPkl->title ?? 'Pelaksanaan PKL / Magang' }}</h6>
                    <p class="text-muted small mb-0 lh-sm">{{ $itemPkl->description ?? 'Jadwal pelaksanaan Praktik Kerja Lapangan yang sedang berlangsung.' }}</p>
                </div>
            </div>
        @endforeach

        <!-- Item 4: Jurnal Mengajar -->
        @if(isset($latestJournal) && $latestJournal)
        <div class="timeline-item" data-aos="fade-left">
            <div class="timeline-dot"><i class="fas fa-book-open"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-1 fw-bold">JURNAL MENGAJAR</span>
                    <small class="text-muted fw-semibold">
                        {{ $latestJournal->created_at ? $latestJournal->created_at->diffForHumans() : '' }}
                    </small>
                </div>

                <h6 class="fw-bold fs-6 mb-1 text-dark">
                    {{ $latestJournal->schedule->subject->name ?? $latestJournal->schedule->subject->subject_name ?? $latestJournal->subject->name ?? $latestJournal->topic ?? 'Kegiatan Mengajar' }}
                </h6>

                <p class="text-muted small mb-1 lh-sm">
                    Pengajar: <strong class="text-dark">{{ $latestJournal->schedule->teacher->name ?? $latestJournal->schedule->teacher->user->name ?? $latestJournal->schedule->user->name ?? $latestJournal->teacher->name ?? 'Guru Pengampu' }}</strong>
                </p>

                <div class="bg-light p-2 rounded-3 mt-2 border">
                    <p class="text-muted small mb-0 lh-sm">
                        <i class="fas fa-pen-nib me-1 text-info"></i> <strong>Materi:</strong> {{ $latestJournal->topic ?? $latestJournal->activity ?? $latestJournal->notes ?? 'Penyampaian materi dan praktik kelas.' }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Item 5: Catatan Tahfiz -->
        @if(isset($latestTahfiz) && $latestTahfiz)
        <div class="timeline-item" data-aos="fade-right">
            <div class="timeline-dot"><i class="fas fa-quran"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-bold">TAHFIZ AL-QUR'AN</span>
                    <small class="text-muted fw-semibold">{{ $latestTahfiz->created_at ? $latestTahfiz->created_at->format('H:i') . ' WIB' : '' }}</small>
                </div>
                <h6 class="fw-bold fs-6 mb-1 text-dark">Setoran Hafalan Terbaru</h6>
                <p class="text-muted small mb-1 lh-sm">Siswa: <strong class="text-dark">{{ $latestTahfiz->student->name ?? 'Siswa' }}</strong></p>
                <p class="text-muted small mb-0 lh-sm">Capaian: <span class="badge bg-light text-dark border px-2 py-1">Surah/Juz {{ $latestTahfiz->surah_or_juz ?? '-' }}</span></p>
            </div>
        </div>
        @endif

        <!-- Item 6: Status WhatsApp Gateway -->
        <div class="timeline-item" id="status" data-aos="fade-up">
            <div class="timeline-dot"><i class="fab fa-whatsapp"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold">SISTEM NOTIFIKASI</span>
                    <small class="text-muted fw-semibold">Real-time</small>
                </div>
                <h6 class="fw-bold fs-6 mb-2">Status WhatsApp Gateway</h6>
                <p class="text-muted small mb-2 lh-sm">
                    Layanan pengiriman notifikasi pesan kehadiran & pengumuman wali murid:
                </p>
                @if(isset($isWaActive) && $isWaActive)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-3 fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Layanan Terhubung
                    </span>
                @else
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-3 fw-bold">
                        <i class="fas fa-exclamation-circle me-1"></i> Layanan Terputus
                    </span>
                @endif
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="text-center my-5 pt-3 border-top">
        <p class="text-muted small mb-0">&copy; {{ date('Y') }} <strong>{{ \App\Models\Setting::value('school_name', 'SMK Negeri 1 Bukittinggi') }}</strong>. All rights reserved.</p>
        <p class="text-muted small mt-1" style="font-size: 0.78rem;">Dikembangkan oleh {{ \App\Models\Setting::value('developer_name', 'Gustifa Fauzan') }}</p>
    </div>
</div>

<!-- MODAL DETAIL JADWAL PELAJARAN -->
<div class="modal fade" id="modalDetailJadwal" tabindex="-1" aria-labelledby="modalDetailJadwalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white p-4 border-0 position-relative">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white text-primary rounded-3 p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                        <i class="fas fa-book-open fs-4"></i>
                    </div>
                    <div>
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-bold mb-1" id="detailDayBadge">Hari Ini</span>
                        <h5 class="modal-title fw-extrabold mb-0" id="detailSubjectName">Detail Mata Pelajaran</h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light">
                <div class="card border-0 shadow-sm rounded-3 p-3 mb-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-user-tie fs-5"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-semibold" style="font-size: 0.75rem;">GURU PENGAMPU</small>
                            <h6 class="fw-bold text-dark mb-0" id="detailTeacherName">-</h6>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="far fa-clock text-primary"></i>
                                <small class="text-muted fw-semibold" style="font-size: 0.75rem;">WAKTU PELAJARAN</small>
                            </div>
                            <strong class="text-dark small" id="detailTimeRange">-</strong>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-door-open text-warning"></i>
                                <small class="text-muted fw-semibold" style="font-size: 0.75rem;">RUANG KELAS</small>
                            </div>
                            <strong class="text-dark small" id="detailClassroom">-</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-white border-0 p-3 justify-content-end">
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Init AOS Animation
        AOS.init({
            once: true,
            duration: 800,
            easing: 'ease-out-cubic'
        });

        // Auto Populate Modal Detail Jadwal
        const modalDetailJadwal = document.getElementById('modalDetailJadwal');
        if (modalDetailJadwal) {
            modalDetailJadwal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                
                const subject = button.getAttribute('data-subject');
                const teacher = button.getAttribute('data-teacher');
                const classroom = button.getAttribute('data-classroom');
                const time = button.getAttribute('data-time');
                const day = button.getAttribute('data-day');

                document.getElementById('detailSubjectName').textContent = subject;
                document.getElementById('detailTeacherName').textContent = teacher;
                document.getElementById('detailClassroom').textContent = classroom;
                document.getElementById('detailTimeRange').textContent = time;
                document.getElementById('detailDayBadge').textContent = day;
            });
        }
    });
</script>
</body>
</html>