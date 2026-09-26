@section('title', 'Log Pembelajaran')

<x-app-layout>
    <div class="page-content">
        
        <div class="mb-3">
            <a href="{{ route('recap.index') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard Rekap</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold text-success mb-0"><i class="fas fa-chalkboard-teacher me-2"></i> Log Absensi Pembelajaran (Per Mapel)</h5>
            </div>
            <div class="card-body">
                
                <!-- FILTER -->
                <form method="GET" class="row g-2 mb-4 align-items-end">
                    <div class="col-md-3">
                        <label class="small fw-bold">Tanggal</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Kelas</label>
                        <select name="classroom_id" class="form-select form-select-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Mata Pelajaran</label>
                        <select name="subject_id" class="form-select form-select-sm">
                            <option value="">Semua Mapel</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-success w-100"><i class="fas fa-filter"></i> Filter Data</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle table-sm">
                        <thead class="bg-success text-white text-center">
                            <tr>
                                <th>Waktu Input</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Status</th>
                                <th>Metode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="text-center">{{ $log->created_at->format('H:i:s') }}</td>
                                    <td class="fw-bold">{{ $log->student->name }}</td>
                                    <td class="text-center">{{ $log->student->classroom->name ?? '-' }}</td>
                                    <td>{{ $log->subject->name ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($log->status == 'present') 
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($log->status == 'sick') 
                                            <span class="badge bg-info">Sakit</span>
                                        @elseif($log->status == 'permission') 
                                            <span class="badge bg-warning text-dark">Izin</span>
                                        @else
                                            <span class="badge bg-danger">Alpha</span>
                                        @endif
                                    </td>
                                    <td class="text-center small text-muted">
                                        {{ $log->method ?? 'Manual' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data pembelajaran hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>