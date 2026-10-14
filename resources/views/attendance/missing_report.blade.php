@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- HEADER HALAMAN -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-primary mb-1">
                <i class="fas fa-user-clock me-2"></i> Rekap Monitoring Presensi Siswa
            </h4>
            <p class="text-muted mb-0">
                Laporan real-time siswa yang tidak/belum melakukan absensi gerbang dan mata pelajaran.
            </p>
        </div>
        <span class="badge bg-primary fs-6 px-3 py-2 rounded-3">
            <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
        </span>
    </div>

    <div class="row g-4">

        <!-- TABEL 1: SISWA BELUM ABSEN GERBANG (HARIAN) -->
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-danger text-white py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">
                        <i class="fas fa-door-closed me-2"></i> Belum Absen Gerbang (Harian)
                    </h6>
                    <span class="badge bg-white text-danger fw-bold rounded-pill">
                        {{ count($absentGateStudents) }} Siswa
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absentGateStudents as $index => $student)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-bold">{{ $student->nis ?? '-' }}</td>
                                        <td class="fw-bold text-dark">{{ $student->name }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                {{ $student->classroom->name ?? 'Tanpa Kelas' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger rounded-pill px-3 py-1">
                                                <i class="fas fa-times-circle me-1"></i> Belum Absen
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas fa-check-circle fa-3x text-success mb-3 d-block opacity-50"></i>
                                            Semua siswa sudah melakukan absensi gerbang hari ini[cite: 2, 4]!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL 2: SISWA BELUM PRESENSI MAPEL (BERDASARKAN JADWAL) -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-warning text-dark py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i> Belum Presensi Mata Pelajaran[cite: 2, 4]
                    </h6>
                    <span class="badge bg-dark text-white rounded-pill">
                        {{ count($missingSubjectStudents) }} Jadwal Bermasalah
                    </span>
                </div>
                <div class="card-body p-3" style="max-height: 500px; overflow-y: auto;">
                    @forelse($missingSubjectStudents as $item)
                        @php
                            $schedule = $item['schedule'];
                            $students = $item['students'];
                        @endphp
                        
                        <div class="border rounded-3 p-3 mb-3 bg-light shadow-sm">
                            <!-- Info Jadwal & Guru -->
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-2 mb-2 gap-2">
                                <div>
                                    <h6 class="fw-bold text-primary mb-1">
                                        <i class="fas fa-book me-1"></i> {{ $schedule->subject->name ?? '-' }} 
                                        <span class="text-dark">({{ $schedule->classroom->name ?? '-' }})</span>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-user-tie me-1"></i> {{ $schedule->teacher->name ?? '-' }}
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-primary px-3 py-2">
                                        <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }} WIB
                                    </span>
                                </div>
                            </div>

                            <!-- Daftar Siswa Belum Absen Mapel Ini -->
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small border-bottom">
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th class="text-end">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr>
                                                <td class="fw-semibold">{{ $student->nis ?? '-' }}</td>
                                                <td class="fw-bold text-dark">{{ $student->name }}</td>
                                                <td class="text-end">
                                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="fas fa-exclamation-circle me-1"></i> Belum Presensi Mapel
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-user-check fa-3x text-success mb-3 d-block opacity-50"></i>
                            Tidak ada siswa yang terlewat presensi mata pelajaran[cite: 2, 4].
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection