@section('title', 'Dashboard Admin')

<x-app-layout>
    <style>
        /* Custom Styling untuk mempercantik tampilan */
        .card { border: none; transition: all 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .stat-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .chart-wrapper { position: relative; height: 300px; }
        .welcome-box { background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%); border-radius: 15px; }
    </style>

    <div class="page-content">
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-box p-4 text-white shadow-sm d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Selamat Datang, Admin! 👋</h4>
                        <p class="mb-0 opacity-75">Berikut adalah ringkasan aktivitas akademik hari ini.</p>
                    </div>
                    <div class="d-none d-md-block">
                        <form method="GET" id="filterForm">
                            <select name="filter" class="form-select border-0 shadow-sm" onchange="document.getElementById('filterForm').submit()">
                                <option value="harian" {{ $filter == 'harian' ? 'selected' : '' }}>📅 Harian (7 Hari)</option>
                                <option value="mingguan" {{ $filter == 'mingguan' ? 'selected' : '' }}>📅 Mingguan</option>
                                <option value="bulanan" {{ $filter == 'bulanan' ? 'selected' : '' }}>📅 Bulanan</option>
                                <option value="semester" {{ $filter == 'semester' ? 'selected' : '' }}>🏫 Semester</option>
                                <option value="tahunan" {{ $filter == 'tahunan' ? 'selected' : '' }}>🗓️ Tahunan</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-light-primary text-primary rounded-3">
                                <i class='bx bx-user'></i>
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 text-secondary small text-uppercase fw-bold">Pengguna</p>
                                <h3 class="my-0">{{ $countUser ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-light-danger text-danger rounded-3">
                                <i class='bx bx-chalkboard'></i>
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 text-secondary small text-uppercase fw-bold">Guru</p>
                                <h3 class="my-0">{{ $countAllTeacher ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-light-warning text-warning rounded-3">
                                <i class='bx bxs-group'></i>
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 text-secondary small text-uppercase fw-bold">Siswa</p>
                                <h3 class="my-0">{{ $countStudent ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-light-success text-success rounded-3">
                                <i class='bx bx-scan'></i>
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 text-secondary small text-uppercase fw-bold">Presensi</p>
                                <h3 class="my-0">{{ $countPresensi ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12 col-xl-8">
                <div class="card radius-10 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold">📈 Tren Kehadiran Siswa ({{ ucfirst($filter) }})</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <canvas id="chartTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card radius-10 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold">🍩 Distribusi Absensi Gerbang</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <canvas id="chartPieGerbang"></canvas>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="bx bxs-circle text-success me-1"></i> Hadir</span>
                                <span class="fw-bold">{{ $pieDataGerbang[0] }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="bx bxs-circle text-warning me-1"></i> Terlambat</span>
                                <span class="fw-bold">{{ $pieDataGerbang[1] }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span><i class="bx bxs-circle text-danger me-1"></i> Alpha</span>
                                <span class="fw-bold">{{ $pieDataGerbang[4] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-lg-4 g-3">
            <div class="col">
                <div class="card bg-primary text-white radius-10">
                    <div class="card-body p-4 text-center">
                        <h2 class="mb-0">{{ $countMapel ?? 0 }}</h2>
                        <p class="mb-0">Mata Pelajaran</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-info text-white radius-10">
                    <div class="card-body p-4 text-center">
                        <h2 class="mb-0">{{ $countKelasTotal ?? 0 }}</h2>
                        <p class="mb-0">Total Kelas</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-dark text-white radius-10">
                    <div class="card-body p-4 text-center">
                        <h2 class="mb-0">{{ $countJurusan ?? 0 }}</h2>
                        <p class="mb-0">Jurusan</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-secondary text-white radius-10">
                    <div class="card-body p-4 text-center">
                        <h2 class="mb-0">{{ $countJadwal ?? 0 }}</h2>
                        <p class="mb-0">Jadwal Aktif</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Pengaturan Global Chart.js
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#8b95a5';

            // --- 1. BAR CHART (TREN) ---
            var ctxBar = document.getElementById('chartTrend').getContext('2d');
            new Chart(ctxBar, {
                type: 'line', // Diubah ke line chart agar lebih modern (tren)
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [
                        {
                            label: 'Hadir',
                            data: {!! json_encode($chartData['hadir']) !!},
                            borderColor: '#15ca20',
                            backgroundColor: 'rgba(21, 202, 32, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Alpha',
                            data: {!! json_encode($chartData['alpa']) !!},
                            borderColor: '#fd3550',
                            backgroundColor: 'rgba(253, 53, 80, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { borderDash: [5, 5] } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // --- 2. PIE CHART (DOUGHNUT) ---
            var ctxPie = document.getElementById('chartPieGerbang').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha'],
                    datasets: [{
                        data: {!! json_encode($pieDataGerbang) !!},
                        backgroundColor: ['#15ca20', '#ffc107', '#0dcaf0', '#0d6efd', '#fd3550'],
                        hoverOffset: 10
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '80%',
                    plugins: { legend: { display: false } }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>