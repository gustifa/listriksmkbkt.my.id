@section('title', 'Dashboard Admin')

<x-app-layout>
    <style>
        /* Modern Typography & Colors */
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        
        .page-content { 
            background: #f0f2f5; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 1.5rem;
        }

        /* Glass Card Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 1.25rem;
            transition: all 0.3s ease;
        }
        
        .glass-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }

        /* Unified Header */
        .header-wrapper {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 1.5rem;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }

        /* Custom Badge */
        .badge-soft { padding: 0.5em 1em; border-radius: 50rem; font-weight: 600; font-size: 0.75rem; }
        
        /* Icon Box */
        .icon-shape {
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
        }

        /* Responsiveness adjustment */
        @media (max-width: 768px) {
            .header-wrapper { padding: 1.5rem; text-align: center; }
            .header-wrapper .d-flex { flex-direction: column; gap: 1rem; }
            .page-content { padding: 1rem; }
        }
    </style>

    <div class="page-content">
        
        <div class="header-wrapper shadow">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-1">E-Monitoring SMK N 1 Bukittinggi</h3>
                    <p class="mb-0 opacity-75">Data statistik akademik dan presensi terpusat</p>
                </div>
                <div class="bg-white p-2 rounded-3 shadow-sm">
                    <form method="GET" id="dashboardFilter" class="d-flex align-items-center gap-2">
                        <span class="text-dark small fw-bold px-2">Periode:</span>
                        <select name="filter" class="form-select border-0 bg-light" onchange="this.form.submit()" style="min-width: 150px;">
                            <option value="harian" {{ $filter == 'harian' ? 'selected' : '' }}>Harian</option>
                            <option value="mingguan" {{ $filter == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                            <option value="bulanan" {{ $filter == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                            <option value="semester" {{ $filter == 'semester' ? 'selected' : '' }}>Semester</option>
                            <option value="tahunan" {{ $filter == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @php
                $kpis = [
                    ['label' => 'Total Pengguna', 'value' => $countUser, 'icon' => 'bx-user', 'color' => 'primary'],
                    ['label' => 'Total Guru', 'value' => $countAllTeacher, 'icon' => 'bx-chalkboard', 'color' => 'danger'],
                    ['label' => 'Total Siswa', 'value' => $countStudent, 'icon' => 'bx-group', 'color' => 'warning'],
                    ['label' => 'Absensi Gerbang', 'value' => $countPresensi, 'icon' => 'bx-scan', 'color' => 'success']
                ];
            @endphp
            @foreach($kpis as $kpi)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card card h-100 shadow-sm">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="icon-shape bg-light-{{ $kpi['color'] }} text-{{ $kpi['color'] }} shadow-sm">
                            <i class='bx {{ $kpi['icon'] }}'></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted d-block fw-bold text-uppercase" style="letter-spacing: 1px;">{{ $kpi['label'] }}</small>
                            <h3 class="mb-0 fw-bold">{{ $kpi['value'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-3">
                <div class="glass-card card bg-primary text-white border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-0 opacity-75">Mapel</p>
                                <h4 class="mb-0 fw-bold">{{ $countMapel ?? 0 }}</h4>
                            </div>
                            <i class='bx bx-book-content fs-1 opacity-50'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="glass-card card bg-success text-white border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-0 opacity-75">Total Kelas</p>
                                <h4 class="mb-0 fw-bold">{{ $countKelasTotal ?? 0 }}</h4>
                            </div>
                            <i class='bx bx-door-open fs-1 opacity-50'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="glass-card card bg-info text-white border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-0 opacity-75">Jurusan</p>
                                <h4 class="mb-0 fw-bold">{{ $countJurusan ?? 0 }}</h4>
                            </div>
                            <i class='bx bxs-graduation fs-1 opacity-50'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="glass-card card bg-secondary text-white border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-0 opacity-75">Jadwal Mapel</p>
                                <h4 class="mb-0 fw-bold">{{ $countJadwal ?? 0 }}</h4>
                            </div>
                            <i class='bx bx-calendar-event fs-1 opacity-50'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="glass-card card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold"><i class='bx bx-bar-chart-alt-2 me-2 text-primary'></i>Statistik Presensi PBM & Gerbang</h6>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 350px;">
                            <canvas id="chartTrendExecutive"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="glass-card card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 text-center">
                        <h6 class="fw-bold">Distribusi Kehadiran</h6>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 250px;">
                            <canvas id="chartPieExecutive"></canvas>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="small"><i class='bx bxs-circle text-success me-1'></i> Hadir</span>
                                <span class="fw-bold">{{ $pieDataGerbang[0] }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="small"><i class='bx bxs-circle text-warning me-1'></i> Terlambat</span>
                                <span class="fw-bold">{{ $pieDataGerbang[1] }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="small"><i class='bx bxs-circle text-danger me-1'></i> Alpha</span>
                                <span class="fw-bold">{{ $pieDataGerbang[4] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

            // 1. COMBINED BAR & LINE CHART (Executive Trend)
            var ctxTrend = document.getElementById('chartTrendExecutive').getContext('2d');
            new Chart(ctxTrend, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [
                        {
                            label: 'Hadir PBM',
                            data: {!! json_encode($chartData['hadir']) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderRadius: 6
                        },
                        {
                            label: 'Hadir Gerbang',
                            data: {!! json_encode($chartDataGerbang['hadir']) !!},
                            type: 'line',
                            borderColor: '#10b981',
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. DOUGHNUT CHART (Executive Distribution)
            var ctxPie = document.getElementById('chartPieExecutive').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha'],
                    datasets: [{
                        data: {!! json_encode($pieDataGerbang) !!},
                        backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9', '#6366f1', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 15
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