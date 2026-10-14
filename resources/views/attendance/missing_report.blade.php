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
                Laporan real-time siswa yang belum melakukan absensi gerbang (masuk/pulang) dan presensi mapel[cite: 5, 6, 7].
            </p>
        </div>
        <span class="badge bg-primary fs-6 px-3 py-2 rounded-3">
            <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
        </span>
    </div>

    <div class="row g-4">

        <!-- KARTU 1: BELUM ABSEN GERBANG (MASUK & PULANG) -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 rounded-top-4">
                    <!-- Nav Tabs Masuk / Pulang -->
                    <ul class="nav nav-pills card-header-pills" id="gateTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold btn-sm" id="masuk-tab" data-bs-toggle="tab" data-bs-target="#masuk" type="button">
                                <i class="fas fa-sign-in-alt me-1"></i> Belum Masuk 
                                <span class="badge bg-danger ms-1">{{ count($noArrivalStudents) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold btn-sm" id="pulang-tab" data-bs-toggle="tab" data-bs-target="#pulang" type="button">
                                <i class="fas fa-sign-out-alt me-1"></i> Belum Pulang 
                                <span class="badge bg-warning text-dark ms-1">{{ count($noDepartureStudents) }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="gateTabsContent">
                        
                        <!-- TAB BELUM ABSEN MASUK -->
                        <div class="tab-pane fade show active" id="masuk" role="tabpanel">
                            <div class="table-responsive" style="max-height: 450px;">
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
                                        @forelse($noArrivalStudents as $index => $student)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="fw-bold">{{ $student->nis ?? '-' }}</td>
                                                <td class="fw-bold text-dark">{{ $student->name }}</td>
                                                <td><span class="badge bg-info text-dark">{{ $student->classroom->name ?? '-' }}</span></td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger rounded-pill px-3 py-1">Belum Absen Masuk</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <i class="fas fa-check-circle fa-3x text-success mb-3 d-block opacity-50"></i>
                                                    Semua siswa sudah melakukan absensi masuk hari ini!
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB BELUM ABSEN PULANG -->
                        <div class="tab-pane fade" id="pulang" role="tabpanel">
                            <div class="table-responsive" style="max-height: 450px;">
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
                                        @forelse($noDepartureStudents as $index => $student)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="fw-bold">{{ $student->nis ?? '-' }}</td>
                                                <td class="fw-bold text-dark">{{ $student->name }}</td>
                                                <td><span class="badge bg-info text-dark">{{ $student->classroom->name ?? '-' }}</span></td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Belum Absen Pulang</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <i class="fas fa-check-circle fa-3x text-success mb-3 d-block opacity-50"></i>
                                                    Tidak ada siswa yang tertinggal/belum absen pulang!
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- KARTU 2: BELUM PRESENSI MAPEL -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-warning text-dark py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i> Belum Presensi Mata Pelajaran
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
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                <div>
                                    <h6 class="fw-bold text-primary mb-1">
                                        {{ $schedule->subject->name ?? '-' }} ({{ $schedule->classroom->name ?? '-' }})
                                    </h6>
                                    <small class="text-muted"><i class="fas fa-user-tie me-1"></i> {{ $schedule->teacher->name ?? '-' }}</small>
                                </div>
                                <span class="badge bg-primary">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }} WIB
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-borderless align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small border-bottom">
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th class="text-end">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr>
                                                <td class="fw-semibold">{{ $student->nis ?? '-' }}</td>
                                                <td class="fw-bold text-dark">{{ $student->name }}</td>
                                                <td class="text-end">
                                                    <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                                        Belum Absen Mapel
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
                            Tidak ada siswa yang terlewat presensi mata pelajaran.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection