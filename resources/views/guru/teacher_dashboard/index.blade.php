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

        <!-- 1. STATISTIK RINGKAS (5 KARTU SEJAJAR RAPI) -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    
    <!-- 1. Total Kelas -->
    <div class="col">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-primary">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase fw-bold text-primary small mb-1" style="font-size: 11px;">Total Kelas</div>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalClasses }}</h3>
                    </div>
                    <div class="p-2 rounded-3 text-primary d-flex align-items-center justify-content-center" style="background-color: rgba(13, 110, 253, 0.12);">
                        <i class='bx bx-door-open fs-3'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Mapel Diampu -->
    <div class="col">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-success">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase fw-bold text-success small mb-1" style="font-size: 11px;">Mapel Diampu</div>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalSubjects }}</h3>
                    </div>
                    <div class="p-2 rounded-3 text-success d-flex align-items-center justify-content-center" style="background-color: rgba(25, 135, 84, 0.12);">
                        <i class='bx bx-book-open fs-3'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Total Siswa -->
    <div class="col">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-warning">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase fw-bold text-warning small mb-1" style="font-size: 11px;">Total Siswa</div>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalStudents }}</h3>
                    </div>
                    <div class="p-2 rounded-3 text-warning d-flex align-items-center justify-content-center" style="background-color: rgba(255, 193, 7, 0.15);">
                        <i class='bx bx-group fs-3'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Tugas Tambahan / Wali Kelas -->
    <div class="col">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-info">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="overflow-hidden pe-1">
                        <div class="text-uppercase fw-bold text-info small mb-1 text-truncate" style="font-size: 11px;">
                            {{ $additionalRole['title'] ?? 'Wali Kelas' }}
                        </div>
                        <div class="fw-bold text-dark text-truncate" style="font-size: 13px;" title="{{ $additionalRole['value'] ?? 'Bukan Wali' }}">
                            {{ $additionalRole['value'] ?? 'Bukan Wali' }}
                        </div>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill mt-1" style="font-size: 10px;">
                            {{ $additionalRole['badge'] ?? 'Guru Pengampu' }}
                        </span>
                    </div>
                    <div class="p-2 rounded-3 text-info flex-shrink-0 d-flex align-items-center justify-content-center" style="background-color: rgba(13, 202, 240, 0.15);">
                        <i class='{{ $additionalRole["icon"] ?? "bx bx-user-pin" }} fs-3'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Bimbingan PKL -->
    <div class="col">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-4 border-danger">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase fw-bold text-danger small mb-1" style="font-size: 11px;">Bimbingan PKL</div>
                        <h3 class="mb-0 fw-bold text-dark">{{ $internshipStudentsCount }} <span class="fs-6 fw-normal text-muted">Siswa</span></h3>
                    </div>
                    <div class="p-2 rounded-3 text-danger d-flex align-items-center justify-content-center" style="background-color: rgba(220, 53, 69, 0.12);">
                        <i class='bx bx-briefcase-alt-2 fs-3'></i>
                    </div>
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