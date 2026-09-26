<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Terpadu</title>
    <link rel="stylesheet" href="{{ asset('backend/assets/css/custom/bootstrap@5.3.0.css')}}"/>
    <link rel="stylesheet" href="{{ asset('backend/assets/css/custom/font-awesome-6.4.0-css-all.min.css')}}"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(90deg, #0056b3 0%, #0088ff 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
            background-color: white;
            border-radius: 50%;
            padding: 2px;
        }

        /* Hero / Welcome Section */
        .hero-section {
            text-align: center;
            padding: 40px 20px;
        }

        /* Clock Widget */
        .clock-widget {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .date-widget {
            color: #666;
            margin-bottom: 30px;
        }

        /* Card Styling */
        .menu-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            height: 100%;
            overflow: hidden;
            background: white;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        /* Specific Colors */
        .absensi-header { background-color: #e3f2fd; color: #0d6efd; }
        .pkl-header { background-color: #e6fffa; color: #00b894; }
        .inventaris-header { background-color: #fff3e0; color: #ff7675; }

        /* Absensi Buttons */
        .btn-absen {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{url('/')}}">
                <!-- <img src="https://via.placeholder.com/50?text=LOGO" alt="Logo Instansi"> -->
                Sistem Terpadu
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> Admin User
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#">Logout</a></li>
                        </ul>
                    </li> -->
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; margin-bottom: 50px;">

        <div class="hero-section">
            <h4>Selamat Datang, <span class="text-primary">Pengguna</span></h4>
            <div id="clock" class="clock-widget">00:00:00</div>
            <div id="date" class="date-widget">Senin, 1 Januari 2024</div>
        </div>

        <div class="row g-4 justify-content-center">

            <div class="col-md-4">
                <div class="shadow-sm card menu-card">
                    <div class="p-4 text-center card-body">
                        <div class="card-icon text-primary">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        <h4 class="mb-3 card-title">Absensi Siswa</h4>
                        <p class="text-muted small">Silakan lakukan absensi sesuai jam kerja.</p>

                        <div class="gap-2 mt-4 d-grid">
                            <a href="{{url('/login')}}" class="btn btn-primary btn-absen">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk
                            </a>
                            {{-- <button class="btn btn-danger btn-absen" onclick="alert('Absen Pulang Berhasil!')">
                                <i class="fas fa-sign-out-alt me-2"></i> Absen Pulang
                            </button> --}}
                            <a href="#" class="mt-2 btn btn-outline-secondary btn-sm">
                                <i class="fas fa-history me-1"></i> Lihat Rekapitulasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="shadow-sm card menu-card h-100">
                    <div class="p-4 text-center card-body d-flex flex-column justify-content-center">
                        <div class="card-icon text-success">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h4 class="card-title">Siswa PKL</h4>
                        <p class="text-muted">Kelola data siswa magang, jurnal kegiatan, dan penilaian.</p>
                        <hr>
                        <div class="d-flex justify-content-around">
                            <a href="#" class="btn btn-light text-success fw-bold"><i class="fas fa-list"></i> Data Siswa</a>
                            <a href="#" class="btn btn-light text-success fw-bold"><i class="fas fa-book"></i> Jurnal</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="shadow-sm card menu-card h-100">
                    <div class="p-4 text-center card-body d-flex flex-column justify-content-center">
                        <div class="card-icon text-warning">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h4 class="card-title">Inventaris</h4>
                        <p class="text-muted">Manajemen aset, peminjaman barang, dan stok opname.</p>
                        <hr>
                        <div class="d-flex justify-content-around">
                            <a href="#" class="btn btn-light text-warning fw-bold"><i class="fas fa-box-open"></i> Aset</a>
                            <a href="#" class="btn btn-light text-warning fw-bold"><i class="fas fa-hand-holding"></i> Peminjaman</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="py-4 mt-5 text-center text-muted small">
        <p>&copy; 2025 Sistem Manajemen Terpadu. All Rights Reserved.</p>
    </footer>
    <script src="{{ asset('backend/assets/js/custom/bootstrap@5.3.0.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function updateClock() {
            const now = new Date();

            // Format Jam
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('clock').textContent = timeString;

            // Format Tanggal
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('id-ID', options);
            document.getElementById('date').textContent = dateString;
        }

        setInterval(updateClock, 1000);
        updateClock(); // Jalankan segera
    </script>
</body>
</html>
