@extends('layouts.app')

@section('title', 'Dashboard E-Monitoring')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
    
    .dashboard-container { 
        background: #f8fafc; 
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 24px;
    }

    /* Topbar Filter */
    .filter-card {
        background: #ffffff;
        padding: 16px 20px;
        border-radius: 14px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #e2e8f0;
    }

    /* CSS Grid untuk 5 Card Metric */
    .metric-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1200px) {
        .metric-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .metric-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .metric-grid { grid-template-columns: repeat(1, 1fr); }
    }

    .bento-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    .bento-card:hover { transform: translateY(-2px); }

    .icon-box {
        width: 42px; 
        height: 42px;
        border-radius: 10px;
        display: flex; 
        align-items: center; 
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .bg-light-primary { background: #eff6ff; color: #3b82f6; }
    .bg-light-danger { background: #fef2f2; color: #ef4444; }
    .bg-light-warning { background: #fffbeb; color: #f59e0b; }
    .bg-light-success { background: #ecfdf5; color: #10b981; }
    .bg-light-purple { background: #f3e8ff; color: #a855f7; }

    .section-title {
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
    }
    .section-title i { margin-right: 8px; font-size: 16px; color: #3b82f6; }

    .chart-height { height: 260px; position: relative; }
</style>

<div class="dashboard-container">
    
    <!-- Filter Topbar -->
    <div class="filter-card">
        <div>
            <h5 class="fw-bold mb-1 text-dark">E-Monitoring Dashboard</h5>
            <small class="text-muted"><i class='bx bx-time-five me-1'></i> Update: {{ now()->translatedFormat('d M Y, H:i') }} WIB</small>
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <span class="small fw-semibold text-muted d-none d-sm-inline">Periode:</span>
            <select name="filter" class="form-select border-1" onchange="this.form.submit()" style="border-radius: 8px; font-weight:600; width: auto;">
                <option value="harian" {{ $filter == 'harian' ? 'selected' : '' }}>Harian</option>
                <option value="mingguan" {{ $filter == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                <option value="bulanan" {{ $filter == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
            </select>
        </form>
    </div>

    <!-- Metric Cards (5 Kolom Sejajar Simetris) -->
    <div class="metric-grid">
        <div class="bento-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-light-primary"><i class='bx bx-user'></i></div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Total User</span>
                    <h4 class="mb-0 fw-bold text-dark">{{ number_format($countUser) }}</h4>
                </div>
            </div>
        </div>

        <div class="bento-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-light-danger"><i class='bx bx-chalkboard'></i></div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Pendidik</span>
                    <h4 class="mb-0 fw-bold text-dark">{{ number_format($countAllTeacher) }}</h4>
                    <span class="text-muted" style="font-size: 10px;">Guru: {{ $countGuru }}</span>
                </div>
            </div>
        </div>

        <div class="bento-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-light-warning"><i class='bx bxs-group'></i></div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Total Siswa</span>
                    <h4 class="mb-0 fw-bold text-dark">{{ number_format($countStudent) }}</h4>
                </div>
            </div>
        </div>

        <div class="bento-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-light-success"><i class='bx bx-scan'></i></div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Gerbang Hari Ini</span>
                    <h4 class="mb-0 fw-bold text-dark">{{ number_format($countPresensi) }}</h4>
                    <span class="text-success fw-semibold" style="font-size: 10px;">Masuk: {{ $countPresensiMasuk }} | Pulang: {{ $countPresensiPulang }}</span>
                </div>
            </div>
        </div>

        <div class="bento-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-light-purple"><i class='bx bx-book-open'></i></div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Hadir PBM</span>
                    <h4 class="mb-0 fw-bold text-purple">{{ number_format($countMapelHadir) }}</h4>
                    <span class="text-muted" style="font-size: 10px;">Siswa di Kelas</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitoring Rombel & Jadwal -->
    <div class="section-title"><i class='bx bx-calendar-event'></i> Monitoring Rombel & Jadwal KBM</div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-5">
            <div class="bento-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Aktivitas Jadwal Hari Ini</h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ date('l') }}</span>
                </div>
                <div class="d-flex flex-column gap-2">
                    <div class="p-3 rounded border bg-light d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light-success"><i class='bx bx-play-circle'></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Jadwal Berlangsung</h6>
                                <small class="text-muted">Sedang KBM di kelas</small>
                            </div>
                        </div>
                        <span class="fs-5 fw-bold text-success">{{ $countJadwalBerlangsung }}</span>
                    </div>
                    <div class="p-3 rounded border bg-light d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light-primary"><i class='bx bx-check-circle'></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Jadwal Selesai</h6>
                                <small class="text-muted">KBM telah berakhir</small>
                            </div>
                        </div>
                        <span class="fs-5 fw-bold text-primary">{{ $countJadwalSelesai }}</span>
                    </div>
                    <div class="p-3 rounded border bg-light d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light-warning"><i class='bx bx-time'></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Jadwal Belum Mulai</h6>
                                <small class="text-muted">Jam pelajaran berikutnya</small>
                            </div>
                        </div>
                        <span class="fs-5 fw-bold text-warning">{{ $countJadwalBelumMulai }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="bento-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Ringkasan Rombongan Belajar (Rombel)</h6>
                    <span class="badge bg-light text-dark border">Total: {{ $countKelasTotal }} Kelas</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="font-size: 11px;">ROMBEL</th>
                                <th style="font-size: 11px;">JURUSAN</th>
                                <th style="font-size: 11px;">WALI KELAS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listRombelSummary as $rombel)
                            <tr>
                                <td class="fw-bold text-dark">{{ $rombel->name ?? $rombel->nama_kelas }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $rombel->major->name ?? '-' }}</span></td>
                                <td><small class="text-muted">{{ $rombel->homeroomTeacher->name ?? 'Belum Ditentukan' }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Belum ada data rombel.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitoring Kehadiran Gerbang -->
    <div class="section-title"><i class='bx bx-door-open'></i> Monitoring Kehadiran Gerbang</div>
    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="bento-card h-100">
                <h6 class="fw-bold mb-3">Tren Kehadiran Gerbang</h6>
                <div class="chart-height"><canvas id="chartTrendGerbangOnly"></canvas></div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="bento-card h-100">
                <h6 class="fw-bold mb-3">Status Gerbang Hari Ini</h6>
                <div style="height: 180px;"><canvas id="chartPieGerbangOnly"></canvas></div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const commonOptions = {
            maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'top' } },
            scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
        };

        new Chart(document.getElementById('chartTrendGerbangOnly'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartDataGerbang['labels']) !!},
                datasets: [
                    { label: 'Hadir', data: {!! json_encode($chartDataGerbang['hadir']) !!}, borderColor: '#10b981', fill: false },
                    { label: 'Terlambat', data: {!! json_encode($chartDataGerbang['terlambat']) !!}, borderColor: '#f59e0b', fill: false }
                ]
            },
            options: commonOptions
        });

        new Chart(document.getElementById('chartPieGerbangOnly'), {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha'],
                datasets: [{
                    data: {!! json_encode($pieDataGerbang) !!},
                    backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9', '#6366f1', '#ef4444']
                }]
            },
            options: { maintainAspectRatio: false }
        });
    });
</script>
@endsection