@section('title', 'Dashboard Admin')

<x-app-layout>
    <div class="page-content">

        <!-- BARIS 1: DATA PENGGUNA UTAMA -->
        <h6 class="mb-0 text-uppercase">Data Pengguna</h6>
        <hr/>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Pengguna</p>
                                <h4 class="my-1 text-info">{{ $countUser ?? 0 }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                <i class='bx bx-user'></i>
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
                                <h4 class="my-1 text-danger">{{ $countTeacher ?? 0 }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                <i class='bx bx-chalkboard'></i>
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
                                <h4 class="my-1 text-warning">{{ $countStudent ?? 0 }}</h4>
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
                                <p class="mb-0 text-secondary">Guru Piket</p>
                                <h4 class="my-1 text-success">{{ $countPiket ?? 0 }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                <i class='bx bx-id-card'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARIS 2: DATA AKADEMIK -->
        <h6 class="mb-0 text-uppercase mt-4">Data Akademik</h6>
        <hr/>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
            <div class="col">
                <div class="card radius-10 bg-primary bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Mata Pelajaran</p>
                                <h4 class="my-1 text-white">{{ $countMapel ?? 0 }}</h4>
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-book'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-danger bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Jumlah Kelas</p>
                                <h4 class="my-1 text-white">{{ $countKelas ?? 0 }}</h4>
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-door-open'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-warning bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-dark">Jurusan</p>
                                <h4 class="my-1 text-dark">{{ $countJurusan ?? 0 }}</h4>
                            </div>
                            <div class="text-dark ms-auto font-35"><i class='bx bxs-graduation'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-success bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Jadwal Pelajaran</p>
                                <h4 class="my-1 text-white">{{ $countJadwal ?? 0 }}</h4>
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-calendar'></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARIS 3: RINGKASAN ABSENSI HARI INI -->
        <h6 class="mb-0 text-uppercase mt-4">Absensi Hari Ini ({{ date('d M Y') }})</h6>
        <hr/>
        <div class="row">
            <!-- Scan Masuk (Gerbang) -->
            <div class="col-md-6">
                <div class="card radius-10 border-top border-0 border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-0">{{ $countPresensi ?? 0 }}</h5>
                                <p class="mb-0 text-secondary">Total Scan Masuk (Gerbang)</p>
                            </div>
                            <div class="ms-auto text-primary fs-3">
                                <i class="bx bx-scan"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Absensi Mapel (Pembelajaran) -->
            <div class="col-md-6">
                <div class="card radius-10 border-top border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-0">{{ $countMapelHadir ?? 0 }}</h5>
                                <p class="mb-0 text-secondary">Total Hadir Pembelajaran (Mapel)</p>
                            </div>
                            <div class="ms-auto text-info fs-3">
                                <i class="bx bx-chalkboard"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARIS 4: GRAFIK & CHART -->
        <div class="row mt-3">
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
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card radius-10">
                    <div class="card-header border-bottom-0 bg-transparent">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0">Status Kehadiran Hari Ini</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-2">
                            <canvas id="pieChart"></canvas>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Hadir <span class="badge bg-success rounded-pill">{{ $countHadir }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Terlambat <span class="badge bg-warning text-dark rounded-pill">{{ $countTerlambat }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Sakit/Izin <span class="badge bg-info rounded-pill">{{ $countSakit + $countIzin }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Alpha <span class="badge bg-danger rounded-pill">{{ $countAlpa }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Script Chart.js -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- GRAFIK BATANG (BAR CHART) ---
            var ctxBar = document.getElementById('barChart').getContext('2d');
            var myBarChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($barLabels) !!},
                    datasets: [
                        {
                            label: 'Hadir',
                            data: {!! json_encode($barDataHadir) !!},
                            backgroundColor: '#15ca20',
                            borderColor: '#15ca20',
                            borderWidth: 1
                        },
                        {
                            label: 'Terlambat',
                            data: {!! json_encode($barDataTerlambat) !!},
                            backgroundColor: '#ffc107',
                            borderColor: '#ffc107',
                            borderWidth: 1
                        },
                        {
                            label: 'Alpha',
                            data: {!! json_encode($barDataAlpa) !!},
                            backgroundColor: '#fd3550',
                            borderColor: '#fd3550',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // --- GRAFIK LINGKARAN (PIE CHART) ---
            var ctxPie = document.getElementById('pieChart').getContext('2d');
            var myPieChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha'],
                    datasets: [{
                        data: {!! json_encode($pieData) !!},
                        backgroundColor: [
                            '#15ca20', // Hadir - Hijau
                            '#ffc107', // Terlambat - Kuning
                            '#0dcaf0', // Sakit - Cyan
                            '#0d6efd', // Izin - Biru
                            '#fd3550'  // Alpha - Merah
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    cutout: '70%',
                }
            });
        });
    </script>
    @endpush

</x-app-layout>