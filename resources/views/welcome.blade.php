<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi SMK - Beranda | {{ \App\Models\Setting::value('app_name', 'GATECH') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
    
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
            padding-top: 100px; /* Jarak untuk navbar melayang */
        }

        /* Floating Navbar Glassmorphism */
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
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav-link {
            font-weight: 600;
            color: #4a4e69 !important;
            margin: 0 10px;
            font-size: 0.95rem;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        /* Tombol Login Elegan */
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

        /* Timeline Styles (Sama seperti sebelumnya) */
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

<nav class="navbar navbar-expand-lg navbar-floating">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <i class="fas fa-graduation-cap me-2"></i>SISFO<span class="text-dark">SMK</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link active" href="#">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Timeline</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Pengumuman</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Bantuan</a></li>
            </ul>
            <div class="d-flex">
                <a href="{{url('/login')}}" class="btn btn-login"><i class="fas fa-sign-in-alt me-2"></i>Masuk Sistem</a>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <div class="text-center mb-5 mt-4">
        <h1 class="fw-bold display-5 mb-3">Informasi <span class="text-primary">Terintegrasi</span></h1>
        <p class="text-muted mx-auto" style="max-width: 600px;">Pantau aktivitas sekolah, kehadiran siswa, dan pengumuman terbaru secara real-time dalam satu lini masa.</p>
    </div>

    <div class="timeline-container">

        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-bullhorn"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-primary rounded-pill px-3">AKADEMIK</span>
                    <small class="text-muted">Baru Saja</small>
                </div>
                <h5 class="fw-bold">Pelaksanaan Uji Kompetensi (UKK)</h5>
                <p class="text-muted small">Jadwal resmi UKK untuk kompetensi keahlian Teknik Instalasi Tenaga Listrik telah dirilis. Mohon siswa segera melakukan validasi alat dan bahan.</p>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-clock"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-success rounded-pill px-3">KEHADIRAN</span>
                    <small class="text-muted">07:15 WIB</small>
                </div>
                <h5 class="fw-bold">Status Absensi Wajah</h5>
                <p class="text-muted small">Sistem mendeteksi 95% kehadiran siswa tepat waktu hari ini. Data telah disinkronkan dengan aplikasi orang tua.</p>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-dot"><i class="fas fa-images"></i></div>
            <div class="timeline-card">
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-info text-dark rounded-pill px-3">KEGIATAN</span>
                    <small class="text-muted">Kemarin</small>
                </div>
                <h5 class="fw-bold">Kunjungan Industri TITL</h5>
                <p class="text-muted small">Dokumentasi kegiatan kunjungan industri siswa kelas XI ke unit pembangkit listrik tenaga air minggu lalu.</p>
                <img src="https://images.unsplash.com/photo-1541888941259-79273ceb0c16?auto=format&fit=crop&w=800&q=80" class="img-fluid rounded-4 mt-2" alt="Foto Sekolah">
            </div>
        </div>

    </div>

    <div class="text-center my-5">
        <p class="text-muted small">&copy; 2026 SMK Negeri 1 Bukittinggi - Dikembangkan oleh Gustifa Fauzan</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>