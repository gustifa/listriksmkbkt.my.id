@section('title', 'Dashboard Guru')

<x-app-layout>
    <div class="page-content container-fluid px-3 px-md-4">
        
        <!-- Header Sambutan Responsif -->
        <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h4 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-chalkboard-teacher me-2"></i> Ruang Guru
                </h4>
                <p class="text-muted mb-0">Selamat Datang, <strong>{{ $teacher->name }}</strong> (NIP: {{ $teacher->nip ?? '-' }})</p>
            </div>
            <div>
                <span class="badge bg-primary text-white p-2 fs-6">
                    <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
        </div>

        <!-- 1. STATISTIK RINGKAS (RESPONSIF GRID 5 KARTU) -->
        <div class="row g-3 mb-4">
            <!-- Total Kelas -->
            <div class="col-6 col-md-4 col-lg">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-primary">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-xs font-weight-bold text-primary text-uppercase d-block">Total Kelas</small>
                                <span class="h4 mb-0 font-weight-bold text-dark">{{ $totalClasses }}</span>
                            </div>
                            <div class="text-primary opacity-50"><i class="fas fa-door-open fa-2x"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mapel Diampu -->
            <div class="col-6 col-md-4 col-lg">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-success">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-xs font-weight-bold text-success text-uppercase d-block">Mapel Diampu</small>
                                <span class="h4 mb-0 font-weight-bold text-dark">{{ $totalSubjects }}</span>
                            </div>
                            <div class="text-success opacity-50"><i class="fas fa-book fa-2x"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Siswa -->
            <div class="col-6 col-md-4 col-lg">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-warning">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-xs font-weight-bold text-warning text-uppercase d-block">Total Siswa</small>
                                <span class="h4 mb-0 font-weight-bold text-dark">{{ $totalStudents }}</span>
                            </div>
                            <div class="text-warning opacity-50"><i class="fas fa-users fa-2x"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wali Kelas -->
            <div class="col-6 col-md-6 col-lg">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-info">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-xs font-weight-bold text-info text-uppercase d-block">Wali Kelas</small>
                                <span class="h6 mb-0 font-weight-bold text-dark">
                                    {{ $homeroomClass ? $homeroomClass->name : 'Bukan Wali' }}
                                </span>
                            </div>
                            <div class="text-info opacity-50"><i class="fas fa-user-shield fa-2x"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bimbingan PKL -->
            <div class="col-12 col-md-6 col-lg">
                <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-danger">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-xs font-weight-bold text-danger text-uppercase d-block">Bimbingan PKL</small>
                                <span class="h4 mb-0 font-weight-bold text-dark">{{ $internshipStudentsCount }} Siswa</span>
                            </div>
                            <div class="text-danger opacity-50"><i class="fas fa-briefcase fa-2x"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. JADWAL HARI INI & STATISTIK CHART -->
        <div class="row g-3 mb-4">
            <!-- Jadwal Mengajar Hari Ini -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-clock me-2"></i>Jadwal Hari Ini</h6>
                        <span class="badge bg-primary-subtle text-primary border">{{ count($todaySchedules) }} Sesi</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Jam</th>
                                        <th>Kelas</th>
                                        <th>Mapel</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($todaySchedules as $schedule)
                                        <tr>
                                            <td class="small fw-semibold text-nowrap">
                                                {{ $schedule->start_time }} - {{ $schedule->end_time }}
                                            </td>
                                            <td class="fw-bold text-dark">{{ $schedule->classroom->name ?? '-' }}</td>
                                            <td class="small">{{ $schedule->subject->name ?? '-' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('scan.index', $schedule->id) }}" class="btn btn-sm btn-primary py-0 px-2" title="Absensi Scan">
                                                    <i class="fas fa-qrcode"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-coffee fa-2x mb-2 d-block opacity-50"></i>
                                                Tidak ada jadwal mengajar hari ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Kehadiran Siswa -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-1"></i> Rekap Kehadiran Siswa</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" onclick="updateChart('harian', this)">Harian</button>
                            <button type="button" class="btn btn-outline-primary" onclick="updateChart('mingguan', this)">Mingguan</button>
                            <button type="button" class="btn btn-outline-primary" onclick="updateChart('bulanan', this)">Bulanan</button>
                            <button type="button" class="btn btn-outline-primary" onclick="updateChart('semester', this)">Semester</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px; position: relative;">
                            <canvas id="teacherAttendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. DAFTAR MAPPING KELAS & MAPEL YANG DIAMPU -->
        <h5 class="fw-bold text-dark mb-3 ps-2 border-start border-4 border-primary">Semua Kelas & Mapel Anda</h5>
        <div class="row g-3 mb-4">
            @forelse($assignments as $schedule)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-primary hover-scale">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">{{ $schedule->classroom->name ?? 'Kelas' }}</h5>
                                    <span class="badge bg-secondary-subtle text-secondary border mt-1">
                                        {{ $schedule->classroom->students->count() ?? 0 }} Siswa
                                    </span>
                                </div>
                                <div class="bg-primary-subtle p-2 rounded-circle text-primary">
                                    <i class="fas fa-book-open fa-lg"></i>
                                </div>
                            </div>
                            
                            <h6 class="text-primary fw-bold mt-2">{{ $schedule->subject->name ?? 'Mata Pelajaran' }}</h6>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-code me-1"></i> Kode Mapel: {{ $schedule->subject->code ?? '-' }}
                            </p>

                            <hr class="my-2">

                            <div class="d-grid gap-2 mt-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle w-100 fw-semibold" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-check me-1"></i> Opsi Absensi
                                    </button>
                                    <ul class="dropdown-menu w-100 shadow">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('recap.learning', ['subject_id' => $schedule->subject_id, 'classroom_id' => $schedule->classroom_id]) }}">
                                                <i class="fas fa-edit me-2 text-primary"></i> Input Absensi Manual
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('journal.index') }}">
                                                <i class="fas fa-journal-whills me-2 text-success"></i> Isi Jurnal Mengajar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center py-4 rounded-3 border-0 shadow-sm">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block opacity-50"></i>
                        <h6 class="fw-bold">Belum Ada Kelas Diampu</h6>
                        <p class="mb-0 small">Anda belum terhubung ke jadwal atau mapping mata pelajaran mana pun.</p>
                    </div>
                </div>
            @endforelse
        </div>

    </div>

    <style>
        .hover-scale { transition: transform 0.2s, box-shadow 0.2s; }
        .hover-scale:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important; }
    </style>

    <!-- CHART.JS SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                        data: chartData.harian,
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