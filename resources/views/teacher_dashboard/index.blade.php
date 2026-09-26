@section('title', 'Dashboard Guru')

<x-app-layout>
    <div class="page-content">
        
        <!-- Header Sambutan -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-chalkboard-teacher me-2"></i> Ruang Guru
                </h4>
                <p class="text-muted mb-0">Selamat Datang, <strong>{{ $teacher->name }}</strong> ({{ $teacher->nip ?? 'NIP. -' }})</p>
            </div>
            <div>
                <span class="badge bg-info text-dark">
                    <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
        </div>

        <!-- 1. STATISTIK RINGKAS (4 KARTU) -->
        <div class="row mb-4">
            <!-- Jumlah Kelas -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Kelas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalClasses }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-door-open fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jumlah Mapel -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Mapel Diampu</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSubjects }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-book fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jumlah Siswa -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Siswa</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalStudents }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hadir Hari Ini (NEW) -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Hadir Hari Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayPresence }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. GRAFIK KEHADIRAN -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-1"></i> Grafik Kehadiran (Kelas Anda)</h6>
                
                <!-- Filter Periode -->
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="updateChart('harian', this)">Harian</button>
                    <button type="button" class="btn btn-outline-primary" onclick="updateChart('mingguan', this)">Mingguan</button>
                    <button type="button" class="btn btn-outline-primary" onclick="updateChart('bulanan', this)">Bulanan</button>
                    <button type="button" class="btn btn-outline-primary" onclick="updateChart('semester', this)">Semester</button>
                    <button type="button" class="btn btn-outline-primary" onclick="updateChart('tahunan', this)">Tahunan</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 350px;">
                    <canvas id="teacherAttendanceChart"></canvas>
                </div>
                <div class="mt-3 text-center small text-muted">
                    Grafik ini menunjukkan akumulasi status kehadiran siswa pada jam pelajaran yang Anda ampu.
                </div>
            </div>
        </div>

        <!-- 3. DAFTAR JADWAL MENGAJAR -->
        <h5 class="fw-bold text-dark mb-3 ps-1 border-start border-4 border-primary">&nbsp;Jadwal & Kelas Anda</h5>
        <div class="row">
            @forelse($assignments as $schedule)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 border-start border-4 border-primary hover-scale">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $schedule->classroom->name }}</h5>
                                    <span class="badge bg-secondary">{{ $schedule->classroom->major->code ?? 'Umum' }}</span>
                                </div>
                                <div class="bg-light p-2 rounded-circle text-primary">
                                    <i class="fas fa-book-open fa-lg"></i>
                                </div>
                            </div>
                            
                            <h6 class="text-dark fw-bold">{{ $schedule->subject->name }}</h6>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-code-branch me-1"></i> Kode: {{ $schedule->subject->code ?? '-' }}
                            </p>

                            <hr class="my-3">

                            <p class="small text-muted mb-2 fw-bold">Menu Pembelajaran:</p>

                            <div class="d-grid gap-2">
                                <a href="{{ route('teaching-assignments.create') }}" class="btn btn-outline-primary btn-sm text-start">
                                    <i class="fas fa-calendar-plus me-2"></i> Buat/Atur Jadwal
                                </a>

                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-check me-1"></i> Mulai Absensi
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li><h6 class="dropdown-header">Pilih Metode:</h6></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('recap.learning', ['subject_id' => $schedule->subject_id, 'classroom_id' => $schedule->classroom_id]) }}">
                                                <i class="fas fa-clipboard-list me-2 text-secondary"></i> Input Manual
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-qrcode me-2 text-dark"></i> Scan QR Code</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-camera me-2 text-primary"></i> Face Recognition</a></li>
                                    </ul>
                                </div>
                                
                                <a href="#" class="btn btn-outline-secondary btn-sm text-start">
                                    <i class="fas fa-history me-2"></i> Riwayat & Jurnal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center py-5">
                        <img src="https://img.icons8.com/ios/100/cccccc/empty-box.png" class="mb-3 opacity-50" width="80">
                        <h5 class="fw-bold text-muted">Belum Ada Jadwal</h5>
                        <p class="mb-0">Anda belum memiliki jadwal mengajar yang dipetakan (mapping).<br>Silakan hubungi Kurikulum/Admin.</p>
                    </div>
                </div>
            @endforelse
        </div>

    </div>

    <style>
        .hover-scale { transition: transform 0.2s; }
        .hover-scale:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
        .border-left-success { border-left: 0.25rem solid #1cc88a !important; }
        .border-left-info { border-left: 0.25rem solid #36b9cc !important; }
        .border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
    </style>

    <!-- CHART.JS SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Data dari Controller
            const chartData = @json($chartData);
            
            const ctx = document.getElementById("teacherAttendanceChart").getContext('2d');
            const labels = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha'];
            const colors = ['#1cc88a', '#f6c23e', '#36b9cc', '#4e73df', '#e74a3b']; 

            let myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: chartData.harian, // Default view
                        backgroundColor: colors,
                        borderColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // Fungsi Update Chart
            window.updateChart = function(period, btn) {
                document.querySelectorAll('.btn-group button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                if (chartData[period]) {
                    myChart.data.datasets[0].data = chartData[period];
                    myChart.update();
                }
            }
        });
    </script>
</x-app-layout>