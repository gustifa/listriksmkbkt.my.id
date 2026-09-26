@section('title', 'Rekapitulasi Absensi')

<x-app-layout>
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary"><i class="fas fa-chart-line me-2"></i> Rekapitulasi Menyeluruh</h4>
            <span class="badge bg-light text-dark border">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
        </div>

        <!-- STATISTIK HARI INI -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1 small">Total Siswa</h6>
                                <h2 class="mb-0 fw-bold">{{ $totalStudents }}</h2>
                            </div>
                            <i class="fas fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1 small">Hadir Hari Ini</h6>
                                <h2 class="mb-0 fw-bold">{{ $dailyStats['hadir'] }}</h2>
                                <small class="text-white-50">{{ $attendanceRate }}% Kehadiran</small>
                            </div>
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1 small">Terlambat</h6>
                                <h2 class="mb-0 fw-bold">{{ $dailyStats['terlambat'] }}</h2>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1 small">Sudah Pulang</h6>
                                <h2 class="mb-0 fw-bold">{{ $dailyStats['pulang'] }}</h2>
                            </div>
                            <i class="fas fa-sign-out-alt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAFIK & MENU -->
        <div class="row mb-4">
            <!-- Kolom Grafik -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-1"></i> Tren Kehadiran (7 Hari Terakhir)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area" style="height: 320px;">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Menu Navigasi -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">Menu Laporan</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('recap.daily') }}" class="card text-decoration-none shadow-sm hover-scale mb-3">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-primary text-white p-3 rounded-circle me-3">
                                    <i class="fas fa-door-open fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Absensi Gerbang</h6>
                                    <p class="text-muted small mb-0">Log Masuk & Pulang</p>
                                </div>
                                <i class="fas fa-chevron-right ms-auto text-muted"></i>
                            </div>
                        </a>

                        <a href="{{ route('recap.learning') }}" class="card text-decoration-none shadow-sm hover-scale mb-3">
                            <div class="card-body d-flex align-items-center p-3">
                                <div class="bg-secondary text-white p-3 rounded-circle me-3">
                                    <i class="fas fa-book-reader fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Absensi Mapel</h6>
                                    <p class="text-muted small mb-0">Kehadiran di Kelas</p>
                                </div>
                                <i class="fas fa-chevron-right ms-auto text-muted"></i>
                            </div>
                        </a>
                        
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i> Grafik di samping menunjukkan perbandingan siswa yang <b>Hadir Tepat Waktu</b>, <b>Terlambat</b>, dan <b>Alpa</b> dalam satu minggu terakhir.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        .hover-scale { transition: transform 0.2s; border-left: 4px solid transparent; }
        .hover-scale:hover { transform: translateX(5px); border-left: 4px solid #4e73df; }
    </style>

    <!-- SCRIPT CHART.JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("attendanceChart").getContext('2d');
            
            // Data dari Controller
            var labels = {!! json_encode($chartLabels) !!};
            var dataHadir = {!! json_encode($dataHadir) !!};
            var dataTerlambat = {!! json_encode($dataTerlambat) !!};
            var dataAlpa = {!! json_encode($dataAlpa) !!};

            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Hadir',
                            data: dataHadir,
                            backgroundColor: '#1cc88a', // Hijau
                            borderColor: '#17a673',
                            borderWidth: 1
                        },
                        {
                            label: 'Terlambat',
                            data: dataTerlambat,
                            backgroundColor: '#f6c23e', // Kuning
                            borderColor: '#dda20a',
                            borderWidth: 1
                        },
                        {
                            label: 'Alpa',
                            data: dataAlpa,
                            backgroundColor: '#e74a3b', // Merah
                            borderColor: '#be2617',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1 // Agar sumbu Y bulat (jumlah orang)
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>