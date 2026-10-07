@extends('layouts.app')

@section('title', 'Rekap Laporan Izin Siswa')

@section('content')
<div class="container-fluid py-3 px-2 px-md-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0 fs-4 fs-md-3">Rekap Laporan Izin Keluar Sekolah</h3>
            <p class="text-muted small mb-0">Pantau dan unduh seluruh data siswa yang melakukan izin keluar sekolah.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.permit.recap.pdf', request()->query()) }}" class="btn btn-danger btn-sm fw-semibold">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.permit.recap.excel', request()->query()) }}" class="btn btn-success btn-sm fw-semibold">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50 fw-semibold d-block">Total Izin</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $totalIzin }}</h3>
                    </div>
                    <i class="fas fa-id-card-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-success text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50 fw-semibold d-block">Sudah Kembali</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $totalKembali }}</h3>
                    </div>
                    <i class="fas fa-user-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-warning text-dark p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-dark-50 fw-semibold d-block">Masih Di Luar</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $totalBelumKembali }}</h3>
                    </div>
                    <i class="fas fa-user-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Filter -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.permit.recap') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Kelas</label>
                    <select name="classroom_id" class="form-select form-select-sm">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>
                                {{ $cls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill fw-semibold">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.permit.recap') }}" class="btn btn-outline-secondary btn-sm fw-semibold">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Rekap -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 50px;">#</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Nama Siswa</th>
                            <th class="py-3">Kelas</th>
                            <th class="py-3">Jam Keluar</th>
                            <th class="py-3">Jam Kembali</th>
                            <th class="py-3">Alasan Izin</th>
                            <th class="py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $index => $perm)
                            <tr>
                                <td class="text-center fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ \Carbon\Carbon::parse($perm->date)->translatedFormat('d M Y') }}</td>
                                <td class="fw-bold text-dark">{{ $perm->student->name ?? '-' }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary border">{{ $perm->student->classroom->name ?? '-' }}</span></td>
                                <td class="text-danger fw-bold">{{ $perm->time_out }}</td>
                                <td class="text-success fw-bold">
                                    @if($perm->time_back)
                                        {{ $perm->time_back }}
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border">Belum Kembali</span>
                                    @endif
                                </td>
                                <td>{{ $perm->reason }}</td>
                                <td class="text-center">
                                    @if($perm->time_back)
                                        <span class="badge bg-success text-white">Selesai</span>
                                    @else
                                        <span class="badge bg-danger text-white">Di Luar</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    Tidak ada data izin siswa pada periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
