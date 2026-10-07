@extends('layouts.app')

@section('title', 'Rekap Nilai Ujian - ' . $exam->title)

@section('content')
<div class="container-fluid py-4">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Detail Ujian
            </a>
            <h3 class="fw-bold text-dark mb-0">Rekap Nilai Ujian</h3>
            <p class="text-muted small mb-0">Ujian: <strong>{{ $exam->title }}</strong> | Mapel: <strong>{{ $exam->subject->name ?? '-' }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('guru.exams.unsubmitted', $exam->id) }}" class="btn btn-warning text-dark fw-bold">
                <i class="fas fa-user-clock me-1"></i> Belum Ujian
            </a>
            <a href="{{ route('exams.report.export', $exam->id) }}" class="btn btn-success fw-bold">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Ringkasan Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                <small class="text-muted d-block fw-semibold mb-1">Total Peserta</small>
                <h3 class="fw-bold text-primary mb-0">{{ $sessions->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                <small class="text-muted d-block fw-semibold mb-1">Rata-Rata Nilai</small>
                <h3 class="fw-bold text-info mb-0">{{ number_format($averageScore ?? 0, 1) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                <small class="text-muted d-block fw-semibold mb-1">Nilai Tertinggi</small>
                <h3 class="fw-bold text-success mb-0">{{ number_format($highestScore ?? 0, 1) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                <small class="text-muted d-block fw-semibold mb-1">Nilai Terendah</small>
                <h3 class="fw-bold text-danger mb-0">{{ number_format($lowestScore ?? 0, 1) }}</h3>
            </div>
        </div>
    </div>

    <!-- Tabel Hasil Ujian Siswa -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">Daftar Hasil Siswa</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Waktu Pengerjaan</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Nilai</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $index => $s)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $s->student->name ?? '-' }}</div>
                                    <small class="text-muted">NIS: {{ $s->student->nis ?? $s->student->nisn ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $s->student->classroom->name ?? '-' }}</span>
                                </td>
                                <td>
                                    <small class="d-block text-dark">
                                        {{ $s->start_time ? \Carbon\Carbon::parse($s->start_time)->format('d M Y, H:i') : '-' }}
                                    </small>
                                    <small class="text-muted">
                                        s/d {{ $s->submit_time ? \Carbon\Carbon::parse($s->submit_time)->format('H:i') : ($s->updated_at ? \Carbon\Carbon::parse($s->updated_at)->format('H:i') : '-') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if($s->status === 'completed')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">Selesai</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1">Sedang Mengerjakan</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold fs-6 {{ ($s->score ?? 0) >= 75 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($s->score ?? 0, 1) }}
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Tombol Selesai Ujian Force oleh Admin/Guru -->
                                        @if($s->status !== 'completed')
                                            <form action="{{ route('guru.exam-sessions.finish', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan ujian siswa ini secara paksa? Nilai akan dihitung dari jawaban yang telah diisi.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success fw-semibold" title="Paksa Selesai Ujian">
                                                    <i class="fas fa-check-circle me-1"></i> Selesai
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Form Reset Sesi Ujian Siswa -->
                                        <form action="{{ route('guru.exam-sessions.reset', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/mereset ujian siswa ini? Semua jawaban akan dihapus dan siswa bisa ujian kembali dari awal.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold" title="Reset Ujian Siswa">
                                                <i class="fas fa-redo me-1"></i> Reset
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    Belum ada siswa yang mengerjakan ujian ini.
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
