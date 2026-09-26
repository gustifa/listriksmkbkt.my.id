@section('title', 'Dashboard Admin')

<x-app-layout>
    <div class="page-content">

        <!-- HEADER FILTER -->
        <!-- <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 text-uppercase">Ringkasan Data</h6>
            <form method="GET" class="d-flex gap-2">
                <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="harian" {{ $filter == 'harian' ? 'selected' : '' }}>Harian (7 Hari)</option>
                    <option value="mingguan" {{ $filter == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulanan" {{ $filter == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="semester" {{ $filter == 'semester' ? 'selected' : '' }}>Semester</option>
                    <option value="tahunan" {{ $filter == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </form>
        </div>
        <hr/> -->

        <!-- BARIS 1: DATA PENGGUNA UTAMA -->
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
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Guru</p>
                                <h4 class="my-1 text-danger">{{ $countAllTeacher ?? 0 }}</h4>
                                <!-- <p class="mb-0 font-13">Admin: {{ $countAdmin }} | Piket: {{ $countPiket }} | Guru: {{ $countGuru }}</p> -->
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                <i class='bx bx-chalkboard'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
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
               <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Absensi Gerbang</p>
                                <h4 class="my-1 text-success">{{ $countPresensi ?? 0 }}</h4>
                                <!-- <small class="text-muted">Masuk: {{ $countPresensiMasuk }} | Plg: {{ $countPresensiPulang }}</small> -->
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                <i class='bx bx-scan'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARIS 2: DATA AKADEMIK -->
        <!-- <h6 class="mb-0 text-uppercase">Statistik Akademik</h6>
        <hr/> -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-2 row-cols-xxl-4">
            <div class="col">
                <div class="card radius-10 bg-gradient-cosmic">
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
                <div class="card radius-10 bg-gradient-ibiza">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Total Kelas</p>
                                <h4 class="my-1 text-white">{{ $countKelasTotal ?? 0 }}</h4>
                                <!-- <small class="text-white">Isi: {{ $countKelasIsi }} | Kosong: {{ $countKelasKosong }}</small> -->
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-door-open'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-gradient-ohhappiness">
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
               <div class="card radius-10 bg-gradient-kyoto">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Jadwal Mapel</p>
                                <h4 class="my-1 text-white">{{ $countJadwal ?? 0 }}</h4>
                                <!-- <small class="text-white">Total Hadir: {{ $countMapelHadir }}</small> -->
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-calendar'></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HEADER FILTER -->
        <div class="d-flex justify-content-between align-items-center mb-4 ">
            <h6 class="mb-0 text-uppercase">STATISTIK DATA</h6>
            <form method="GET" class="d-flex gap-2">
                <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="harian" {{ $filter == 'harian' ? 'selected' : '' }}>Harian (7 Hari)</option>
                    <option value="mingguan" {{ $filter == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulanan" {{ $filter == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="semester" {{ $filter == 'semester' ? 'selected' : '' }}>Semester</option>
                    <option value="tahunan" {{ $filter == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </form>
        </div>
        <hr/>

        <!-- BARIS 3: GRAFIK -->
        

        <div class="row row-cols-1 row-cols-md-1 row-cols-xl-5 mb-4">
            <div class="col">
                <div class="card radius-10 bg-success bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Hadir Gerbang</p>
                                <h4 class="my-1 text-white">{{ $pieDataGerbang[0] ?? 0 }}</h4>
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-book'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-primary bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Terlambat</p>
                                <h4 class="my-1 text-white">{{ $pieDataGerbang[1] ?? 0 }}</h4>
                                <!-- <small class="text-white">Isi: {{ $countKelasIsi }} | Kosong: {{ $countKelasKosong }}</small> -->
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
                                <p class="mb-0 text-dark">Sakit</p>
                                <h4 class="my-1 text-dark">{{ $pieDataGerbang[2] ?? 0 }}</h4>
                            </div>
                            <div class="text-dark ms-auto font-35"><i class='bx bxs-graduation'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
               <div class="card radius-10 bg-secondary bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Izin</p>
                                <h4 class="my-1 text-white">{{ $pieDataGerbang[3] ?? 0 }}</h4>
                                <!-- <small class="text-white">Total Hadir: {{ $countMapelHadir }}</small> -->
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-calendar'></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
               <div class="card radius-10 bg-danger bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Alpa</p>
                                <h4 class="my-1 text-white">{{ $pieDataGerbang[4] ?? 0 }}</h4>
                                <!-- <small class="text-white">Total Hadir: {{ $countMapelHadir }}</small> -->
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-calendar'></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-1 row-cols-xl-5 mb-4">
            <div class="col">
                <div class="card radius-10 bg-success bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Hadir PBM</p>
                                <h4 class="my-1 text-white">{{ $pieData[0] ?? 0 }}</h4>
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-book'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-primary bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Terlambat</p>
                                <h4 class="my-1 text-white">{{ $pieData[1] ?? 0 }}</h4>
                                <!-- <small class="text-white">Isi: {{ $countKelasIsi }} | Kosong: {{ $countKelasKosong }}</small> -->
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
                                <p class="mb-0 text-dark">Sakit</p>
                                <h4 class="my-1 text-dark">{{ $pieData[2] ?? 0 }}</h4>
                            </div>
                            <div class="text-dark ms-auto font-35"><i class='bx bxs-graduation'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
               <div class="card radius-10 bg-secondary bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Izin</p>
                                <h4 class="my-1 text-white">{{ $pieData[3] ?? 0 }}</h4>
                                <!-- <small class="text-white">Total Hadir: {{ $countMapelHadir }}</small> -->
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-calendar'></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
               <div class="card radius-10 bg-danger bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Alpa</p>
                                <h4 class="my-1 text-white">{{ $pieData[4] ?? 0 }}</h4>
                                <!-- <small class="text-white">Total Hadir: {{ $countMapelHadir }}</small> -->
                            </div>
                            <div class="text-white ms-auto font-35"><i class='bx bx-calendar'></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-1 row-cols-xl-1 row-cols-xxl-2">

                <div class="row">
                   <div class="col-12 col-lg-12">
                      <!-- <div class="card radius-10 border-start border-0 border-4 border-info"> -->
                        <div class="card radius-10">
                            <div class="card-header border-bottom-0 bg-transparent">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0">Tren Kehadiran Gerbang ({{ ucfirst($filter) }})</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-1">
                                    <canvas id="chartTrendGerbang"></canvas>
                                </div>
                            </div>
                        </div>
				   </div>
				</div><!--end row-->	
                
                <div class="row">
                   <div class="col-12 col-lg-12">
                      <!-- <div class="card radius-10 border-start border-0 border-4 border-info"> -->
                        <div class="card radius-10">
                            <div class="card-header border-bottom-0 bg-transparent">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0">Tren Kehadiran ({{ ucfirst($filter) }})</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-1">
                                    <canvas id="chartTrend"></canvas>
                                </div>
                            </div>
                        </div>
				   </div>
				</div><!--end row-->	
                
                
            
        </div>

        <!-- BARIS 3: GRAFIK -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-2 mb-4">
           
            <!-- Grafik Lingkaran -->
            <div class="col-12 col-lg-4">
                <div class="card radius-10">
                    <div class="card-header border-bottom-0 bg-transparent">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0">Kehadiran Gerbang ({{ ucfirst($filter) }})</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-2">
                            <canvas id="chartPieGerbang"></canvas>
                        </div>
                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Hadir <span class="badge bg-success rounded-pill">{{ $pieDataGerbang[0] }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Terlambat <span class="badge bg-warning text-dark rounded-pill">{{ $pieDataGerbang[1] }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Sakit <span class="badge bg-info rounded-pill">{{ $pieDataGerbang[2] }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Izin <span class="badge bg-primary rounded-pill">{{ $pieDataGerbang[3] }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Alpha <span class="badge bg-danger rounded-pill">{{ $pieDataGerbang[4] }}</span>
                            </li>
                        </ul>
                    </div>
                </div>   
            </div>

            <!-- Grafik Lingkaran -->
            <div class="col-12 col-lg-4">
                <div class="card radius-10">
                    <div class="card-header border-bottom-0 bg-transparent">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0">Kehadiran PBM ({{ ucfirst($filter) }})</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-2">
                            <canvas id="chartPie"></canvas>
                        </div>
                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Hadir <span class="badge bg-success rounded-pill">{{ $pieData[0] }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Terlambat <span class="badge bg-warning text-dark rounded-pill">{{ $pieData[1] }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Sakit <span class="badge bg-info rounded-pill">{{ $pieData[2] }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Izin <span class="badge bg-primary rounded-pill">{{ $pieData[3] }}</span>
                            </li>
                            <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">
                                Alpha <span class="badge bg-danger rounded-pill">{{ $pieData[4] }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                

                
            </div>
            
        </div>

        
        

        

        

    </div>

    <!-- SCRIPT CHART.JS -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // --- 1. CHART BATANG (TREN) ---
            var ctxBar = document.getElementById('chartTrend').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [
                        {
                            label: 'Hadir',
                            data: {!! json_encode($chartData['hadir']) !!},
                            backgroundColor: '#15ca20',
                            borderColor: '#15ca20',
                            borderWidth: 1
                        },
                        {
                            label: 'Terlambat',
                            data: {!! json_encode($chartData['terlambat']) !!},
                            backgroundColor: '#ffc107',
                            borderColor: '#ffc107',
                            borderWidth: 1
                        },
                        {
                            label: 'Alpha',
                            data: {!! json_encode($chartData['alpa']) !!},
                            backgroundColor: '#fd3550',
                            borderColor: '#fd3550',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#e9ecef' } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // --- 1. CHART BATANG (TREN) GERBANG---
            var ctxBar = document.getElementById('chartTrendGerbang').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartDataGerbang['labels']) !!},
                    datasets: [
                        {
                            label: 'Hadir',
                            data: {!! json_encode($chartDataGerbang['hadir']) !!},
                            backgroundColor: '#15ca20',
                            borderColor: '#15ca20',
                            borderWidth: 1
                        },
                        {
                            label: 'Terlambat',
                            data: {!! json_encode($chartDataGerbang['terlambat']) !!},
                            backgroundColor: '#ffc107',
                            borderColor: '#ffc107',
                            borderWidth: 1
                        },
                        {
                            label: 'Alpha',
                            data: {!! json_encode($chartDataGerbang['alpa']) !!},
                            backgroundColor: '#fd3550',
                            borderColor: '#fd3550',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#e9ecef' } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // --- 2. CHART LINGKARAN (PIE) ---
            var ctxPie = document.getElementById('chartPie').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha'],
                    datasets: [{
                        data: {!! json_encode($pieData) !!},
                        backgroundColor: [
                            '#15ca20', // Hijau
                            '#ffc107', // Kuning
                            '#0dcaf0', // Cyan
                            '#0d6efd', // Biru
                            '#fd3550'  // Merah
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    cutout: '75%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // --- 2. CHART LINGKARAN (PIE) ---
            var ctxPie = document.getElementById('chartPieGerbang').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha'],
                    datasets: [{
                        data: {!! json_encode($pieDataGerbang) !!},
                        backgroundColor: [
                            '#15ca20', // Hijau
                            '#ffc107', // Kuning
                            '#0dcaf0', // Cyan
                            '#0d6efd', // Biru
                            '#fd3550'  // Merah
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    cutout: '75%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

        });
    </script>
    @endpush
</x-app-layout>