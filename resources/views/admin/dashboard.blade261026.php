@section('title', 'Dashboard Monitoring')

<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        
        .page-content { 
            background: #f4f7fa; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 20px;
        }

        /* Grouping Section */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .section-title i { margin-right: 8px; font-size: 18px; color: #3b82f6; }

        /* Card Customization */
        .bento-card {
            background: #ffffff;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }
        .bento-card:hover { transform: translateY(-3px); }

        /* Dashboard Filter Floating */
        .filter-container {
            background: #ffffff;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e2e8f0;
        }

        /* Icon Backgrounds */
        .icon-box {
            width: 45px; height: 45px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }

        .chart-height { height: 300px; position: relative; }

        @media (max-width: 768px) {
            .filter-container { flex-direction: column; gap: 10px; text-align: center; }
        }
    </style>

    <div class="page-content">
        
        <div class="filter-container">
            <div>
                <h5 class="fw-bold mb-0">E-Monitoring Dashboard</h5>
                <p class="text-muted small mb-0">Update terakhir: {{ now()->format('d M Y, H:i') }}</p>
            </div>
            <form method="GET" class="d-flex gap-2">
                <select name="filter" class="form-select border-1" onchange="this.form.submit()" style="border-radius: 8px;">
                    <option value="harian" {{ $filter == 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="mingguan" {{ $filter == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulanan" {{ $filter == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="semester" {{ $filter == 'semester' ? 'selected' : '' }}>Semester</option>
                    <option value="tahunan" {{ $filter == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="bento-card card h-100 p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-light-primary text-primary"><i class='bx bx-user'></i></div>
                        <div class="ms-3">
                            <p class="mb-0 text-muted small fw-bold">User</p>
                            <h5 class="mb-0 fw-bold">{{ $countUser }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bento-card card h-100 p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-light-danger text-danger"><i class='bx bx-chalkboard'></i></div>
                        <div class="ms-3">
                            <p class="mb-0 text-muted small fw-bold">Guru</p>
                            <h5 class="mb-0 fw-bold">{{ $countAllTeacher }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bento-card card h-100 p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-light-warning text-warning"><i class='bx bxs-group'></i></div>
                        <div class="ms-3">
                            <p class="mb-0 text-muted small fw-bold">Siswa</p>
                            <h5 class="mb-0 fw-bold">{{ $countStudent }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bento-card card h-100 p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-light-success text-success"><i class='bx bx-scan'></i></div>
                        <div class="ms-3">
                            <p class="mb-0 text-muted small fw-bold">Presensi</p>
                            <h5 class="mb-0 fw-bold">{{ $countPresensi }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title"><i class='bx bx-door-open'></i> Monitoring Kehadiran Gerbang</div>
        <div class="row g-4 mb-5">
            <div class="col-12 col-xl-8">
                <div class="bento-card card p-4">
                    <h6 class="fw-bold mb-4">Tren Kehadiran Gerbang ({{ ucfirst($filter) }})</h6>
                    <div class="chart-height">
                        <canvas id="chartTrendGerbangOnly"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="bento-card card p-4">
                    <h6 class="fw-bold mb-4">Status Gerbang</h6>
                    <div style="height: 200px;"><canvas id="chartPieGerbangOnly"></canvas></div>
                    <div class="mt-4">
                        @php $labels = ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha']; $colors = ['success', 'warning', 'info', 'primary', 'danger']; @endphp
                        <div class="row g-2">
                            @foreach($labels as $index => $label)
                            <div class="col-6">
                                <div class="p-2 border rounded text-center">
                                    <small class="text-muted d-block">{{ $label }}</small>
                                    <span class="fw-bold text-{{ $colors[$index] }}">{{ $pieDataGerbang[$index] ?? 0 }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title"><i class='bx bx-book-reader'></i> Monitoring Pembelajaran (PBM)</div>
        <div class="row g-4 mb-5">
            <div class="col-12 col-xl-4">
                <div class="bento-card card p-4">
                    <h6 class="fw-bold mb-4">Status Kehadiran PBM</h6>
                    <div style="height: 200px;"><canvas id="chartPiePBMOnly"></canvas></div>
                    <div class="list-group list-group-flush mt-4">
                        @foreach($labels as $index => $label)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 py-1">
                            <small class="text-muted">{{ $label }}</small>
                            <span class="badge bg-{{ $colors[$index] }} rounded-pill">{{ $pieData[$index] ?? 0 }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-8">
                <div class="bento-card card p-4 h-100">
                    <h6 class="fw-bold mb-4">Tren Kehadiran PBM ({{ ucfirst($filter) }})</h6>
                    <div class="chart-height">
                        <canvas id="chartTrendPBMOnly"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title"><i class='bx bx-data'></i> Informasi Akademik</div>
        <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
            <div class="col">
                <div class="bento-card bg-primary text-white p-3 text-center">
                    <h4 class="mb-0 fw-bold">{{ $countMapel }}</h4>
                    <small>Mata Pelajaran</small>
                </div>
            </div>
            <div class="col">
                <div class="bento-card bg-info text-white p-3 text-center">
                    <h4 class="mb-0 fw-bold">{{ $countKelasTotal }}</h4>
                    <small>Total Kelas</small>
                </div>
            </div>
            <div class="col">
                <div class="bento-card bg-dark text-white p-3 text-center">
                    <h4 class="mb-0 fw-bold">{{ $countJurusan }}</h4>
                    <small>Jurusan</small>
                </div>
            </div>
            <div class="col">
                <div class="bento-card bg-secondary text-white p-3 text-center">
                    <h4 class="mb-0 fw-bold">{{ $countJadwal }}</h4>
                    <small>Jadwal Aktif</small>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chartOptions = {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                    x: { grid: { display: false } }
                }
            };

            // 1. Trend Gerbang
            new Chart(document.getElementById('chartTrendGerbangOnly'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartDataGerbang['labels']) !!},
                    datasets: [{
                        label: 'Hadir',
                        data: {!! json_encode($chartDataGerbang['hadir']) !!},
                        borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.3
                    }]
                },
                options: chartOptions
            });

            // 2. Trend PBM
            new Chart(document.getElementById('chartTrendPBMOnly'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [{
                        label: 'Hadir PBM',
                        data: {!! json_encode($chartData['hadir']) !!},
                        backgroundColor: '#3b82f6', borderRadius: 5
                    }]
                },
                options: chartOptions
            });

            // 3. Pie Gerbang
            new Chart(document.getElementById('chartPieGerbangOnly'), {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha'],
                    datasets: [{
                        data: {!! json_encode($pieDataGerbang) !!},
                        backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9', '#6366f1', '#ef4444'],
                        cutout: '80%'
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            // 4. Pie PBM
            new Chart(document.getElementById('chartPiePBMOnly'), {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha'],
                    datasets: [{
                        data: {!! json_encode($pieData) !!},
                        backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9', '#6366f1', '#ef4444'],
                        cutout: '80%'
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        });
    </script>
    @endpush
</x-app-layout>