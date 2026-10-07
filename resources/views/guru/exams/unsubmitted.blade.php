@extends('layouts.app')

@section('title', 'Rekap Siswa Belum Mengikuti Ujian - ' . $exam->title)

@section('content')
<div class="container-fluid py-3">
    <!-- Header Navigasi -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Detail Ujian
            </a>
            <h3 class="fw-bold text-dark mb-0">Siswa Belum Mengikuti Ujian</h3>
            <span class="text-muted fs-6">{{ $exam->title }} ({{ $exam->subject->name ?? '-' }})</span>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-secondary fw-semibold">
                <i class="fas fa-print me-1"></i> Cetak / Export PDF
            </button>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-warning-subtle text-warning-emphasis">
                <div class="card-body p-3 text-center">
                    <h2 class="fw-bold mb-0">{{ $unsubmittedStudents->count() }}</h2>
                    <small class="fw-semibold">Total Siswa Belum Ikut Ujian</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Siswa Belum Ujian per Kelas -->
    @forelse($groupedByClass as $className => $students)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fas fa-users me-2"></i> Kelas: {{ $className }}
                </h5>
                <span class="badge bg-danger fs-6">{{ $students->count() }} Siswa</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th>NIS / NISN</th>
                                <th>Nama Lengkap</th>

                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                <tr>
                                    <td class="text-center fw-bold text-secondary">{{ $index + 1 }}</td>
                                    <td>{{ $student->nis ?? $student->nisn ?? '-' }}</td>
                                    <td class="fw-semibold text-dark">{{ $student->name }}</td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            Belum Mengikuti Ujian
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm rounded-3 py-5 text-center">
            <div class="card-body">
                <i class="fas fa-check-circle text-success display-3 mb-3"></i>
                <h4 class="fw-bold text-dark">Semua Siswa Sudah Mengikuti Ujian!</h4>
                <p class="text-muted">Tidak ada siswa target yang belum membuka atau mengerjakan ujian ini.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection
