@section('title', 'Detail Rekap - ' . $student->name)

<x-app-layout>
    <div class="page-content">
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('recap.students') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Siswa</a>
            <span class="text-muted small">Data diperbarui: {{ now()->format('d M Y H:i') }}</span>
        </div>

        <!-- PROFILE & STATS -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <div class="avatar-circle bg-primary text-white mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 32px; border-radius: 50%;">
                                {{ substr($student->name, 0, 1) }}
                            </div>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $student->name }}</h4>
                        <p class="text-muted mb-1">NIS: {{ $student->nis }}</p>
                        <span class="badge bg-secondary">{{ $student->classroom->name ?? 'Tanpa Kelas' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row h-100 g-3">
                    <div class="col-md-3">
                        <div class="card bg-success text-white h-100 text-center p-3">
                            <h3>{{ $stats['hadir'] }}</h3>
                            <small>Hadir Tepat Waktu</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark h-100 text-center p-3">
                            <h3>{{ $stats['terlambat'] }}</h3>
                            <small>Total Terlambat</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white h-100 text-center p-3">
                            <h3>{{ $stats['alpa'] }}</h3>
                            <small>Total Alpha</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white h-100 text-center p-3">
                            <h3>{{ $stats['mapel_hadir'] }}</h3>
                            <small>Hadir di Mapel</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- TABEL 1: RIWAYAT GERBANG -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white fw-bold text-primary border-bottom">
                        <i class="fas fa-door-open me-2"></i> Riwayat Kehadiran Harian (Gerbang)
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 small align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Masuk</th>
                                        <th>Pulang</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dailyLogs as $log)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}</td>
                                            <td class="text-success fw-bold">{{ $log->arrival_time ? \Carbon\Carbon::parse($log->arrival_time)->format('H:i') : '-' }}</td>
                                            <td class="text-danger fw-bold">{{ $log->departure_time ? \Carbon\Carbon::parse($log->departure_time)->format('H:i') : '-' }}</td>
                                            <td>
                                                @if($log->status == 'hadir') <span class="badge bg-success">Hadir</span>
                                                @elseif($log->status == 'terlambat') <span class="badge bg-warning text-dark">Telat</span>
                                                @else <span class="badge bg-danger">Alpha</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada data kehadiran.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 border-top">
                            {{ $dailyLogs->appends(['learning_page' => request('learning_page')])->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABEL 2: RIWAYAT MAPEL -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white fw-bold text-success border-bottom">
                        <i class="fas fa-book-reader me-2"></i> Riwayat Kehadiran Mata Pelajaran
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 small align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($learningLogs as $log)
                                        <tr>
                                            <td>{{ $log->created_at->format('d/m H:i') }}</td>
                                            <td class="fw-bold">{{ $log->subject->name ?? '-' }}</td>
                                            <td>
                                                @if($log->status == 'present') <span class="badge bg-success">Hadir</span>
                                                @elseif($log->status == 'sick') <span class="badge bg-info">Sakit</span>
                                                @elseif($log->status == 'permission') <span class="badge bg-warning text-dark">Izin</span>
                                                @else <span class="badge bg-danger">Alpha</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center py-3 text-muted">Belum ada data pembelajaran.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 border-top">
                            {{ $learningLogs->appends(['daily_page' => request('daily_page')])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>