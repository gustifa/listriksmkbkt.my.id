@section('title', 'Log Absensi Gerbang')

<x-app-layout>
    <div class="page-content">
        
        <div class="mb-3">
            <a href="{{ route('recap.index') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard Rekap</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold text-primary mb-0"><i class="fas fa-history me-2"></i> Log Absensi Gerbang (Semua Siswa)</h5>
            </div>
            <div class="card-body">
                
                <!-- FILTER -->
                <form method="GET" class="row g-2 mb-4 align-items-end">
                    <div class="col-md-3">
                        <label class="small fw-bold">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date', date('Y-m-d')) }}">
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
                    <div class="col-md-2">
                         <label class="small fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                            <option value="alpa" {{ request('status') == 'alpa' ? 'selected' : '' }}>Alpa</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i></button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle table-sm">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}</td>
                                    <td class="fw-bold">{{ $log->student->name }}</td>
                                    <td class="text-center">{{ $log->student->classroom->name ?? '-' }}</td>
                                    <td class="text-center text-success font-monospace">{{ $log->arrival_time ?? '-' }}</td>
                                    <td class="text-center text-danger font-monospace">{{ $log->departure_time ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($log->status == 'hadir') 
                                            <span class="badge bg-success">Tepat Waktu</span>
                                        @elseif($log->status == 'terlambat') 
                                            <span class="badge bg-warning text-dark">Terlambat</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($log->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data absensi.</td></tr>
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