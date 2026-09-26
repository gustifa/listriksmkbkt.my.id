@section('title', 'Dashboard Admin')

<x-app-layout>
    <div class="page-content">
        
        <!-- BARIS 1: DATA MASTER -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Pengguna</p>
                                <h4 class="my-1 text-info">{{ $countUser }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                <i class='bx bxs-user-account'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Guru</p>
                                <h4 class="my-1 text-danger">{{ $countTeacher }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                <i class='bx bxs-id-card'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Siswa</p>
                                <h4 class="my-1 text-warning">{{ $countStudent }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto">
                                <i class='bx bxs-group'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Presensi (Hari Ini)</p>
                                <h4 class="my-1 text-success">{{ $countPresensi }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                <i class='bx bx-scan'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

        <!-- BARIS 2: DETAIL ABSENSI HARI INI -->
        <h6 class="mb-0 text-uppercase">Statistik Kehadiran Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('d F Y') }})</h6>
        <hr/>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5">
            <!-- HADIR -->
            <div class="col">
                <div class="card radius-10 bg-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Hadir (Tepat Waktu)</p>
                                <h4 class="my-1 text-white">{{ $countHadir }}</h4>
                            </div>
                            <div class="widgets-icons bg-white text-success ms-auto">
                                <i class="bx bx-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- TERLAMBAT -->
            <div class="col">
                <div class="card radius-10 bg-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-dark">Terlambat</p>
                                <h4 class="my-1 text-dark">{{ $countTerlambat }}</h4>
                            </div>
                            <div class="widgets-icons bg-white text-warning ms-auto">
                                <i class="bx bx-time-five"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SAKIT -->
            <div class="col">
                <div class="card radius-10 bg-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Sakit</p>
                                <h4 class="my-1 text-white">{{ $countSakit }}</h4>
                            </div>
                            <div class="widgets-icons bg-white text-info ms-auto">
                                <i class="bx bx-plus-medical"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- IZIN -->
            <div class="col">
                <div class="card radius-10 bg-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Izin</p>
                                <h4 class="my-1 text-white">{{ $countIzin }}</h4>
                            </div>
                            <div class="widgets-icons bg-white text-primary ms-auto">
                                <i class="bx bx-envelope"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ALPA -->
            <div class="col">
                <div class="card radius-10 bg-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Alpa</p>
                                <h4 class="my-1 text-white">{{ $countAlpa }}</h4>
                            </div>
                            <div class="widgets-icons bg-white text-danger ms-auto">
                                <i class="bx bx-x-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

        <!-- BARIS 3: GRAFIK -->
        <div class="row">
            <!-- Grafik Batang (Tren Mingguan) -->
            <div class="col-12 col-lg-8">
                <div class="card radius-10">
                    <div class="card-header border-bottom-0 bg-transparent">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0">Tren Kehadiran (7 Hari Terakhir)</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-1">
                            <canvas id="chartWeeklyAttendance"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Grafik Lingkaran (Komposisi Hari Ini) -->
            <div class="col-12 col-lg-4">
                <div class="card radius-10">
                    <div class="card-header border-bottom-0 bg-transparent">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0">Komposisi Hari Ini</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-2">
                            <canvas id="chartDailyComposition"></canvas>
                        </div>
                        <div class="text-center mt-3 small text-muted">
                            * Persentase kehadiran berdasarkan data hari ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts untuk Chart.js -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // --- 1. CHART BATANG (MINGGUAN) ---
            var ctxBar = document.getElementById("chartWeeklyAttendance").getContext('2d');
            var myBarChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($barLabels) !!},
                    datasets: [
                        {
                            label: 'Hadir',
                            data: {!! json_encode($barDataHadir) !!},
                            backgroundColor: '#198754' // Success
                        },
                        {
                            label: 'Terlambat',
                            data: {!! json_encode($barDataTerlambat) !!},
                            backgroundColor: '#ffc107' // Warning
                        },
                        {
                            label: 'Alpa',
                            data: {!! json_encode($barDataAlpa) !!},
                            backgroundColor: '#dc3545' // Danger
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // --- 2. CHART LINGKARAN (HARI INI) ---
            var ctxPie = document.getElementById("chartDailyComposition").getContext('2d');
            var myPieChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ["Hadir", "Terlambat", "Sakit", "Izin", "Alpa"],
                    datasets: [{
                        backgroundColor: [
                            '#198754', // Hadir - Green
                            '#ffc107', // Terlambat - Yellow
                            '#0dcaf0', // Sakit - Cyan
                            '#0d6efd', // Izin - Blue
                            '#dc3545'  // Alpa - Red
                        ],
                        data: {!! json_encode($pieData) !!}
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

        });
    </script>
    @endpush

</x-app-layout>