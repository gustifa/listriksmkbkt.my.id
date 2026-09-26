@section('title', 'Transkrip Nilai PKL')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('student.internships.index') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                <h4 class="mt-2 fw-bold text-primary"><i class="fas fa-certificate me-2"></i>Transkrip Nilai PKL</h4>
            </div>
            <a href="{{ route('student.internships.print_transcript') }}" target="_blank" class="btn btn-danger fw-bold shadow-sm">
                <i class="fas fa-file-pdf me-2"></i> Download PDF Resmi
            </a>
        </div>

        <div class="row">
            <!-- Kolom Kiri: Info -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-graduate text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="fw-bold text-dark">{{ $student->name }}</h5>
                        <p class="text-muted mb-0">{{ $student->nis }}</p>
                        <hr>
                        <div class="text-start">
                            <small class="text-uppercase text-muted fw-bold">Tempat PKL</small>
                            <p class="fw-bold text-dark mb-3">{{ $internship->industry->name }}</p>

                            <small class="text-uppercase text-muted fw-bold">Periode</small>
                            <p class="fw-bold text-dark mb-3">
                                {{ $internship->start_date->format('d M Y') }} s.d {{ $internship->end_date->format('d M Y') }}
                            </p>

                            <small class="text-uppercase text-muted fw-bold">Pembimbing Sekolah</small>
                            <p class="fw-bold text-dark mb-0">{{ $internship->advisor->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center py-3">
                        <small class="text-muted d-block mb-1">NILAI AKHIR</small>
                        <h1 class="fw-bold text-{{ $internship->grade->final_score >= 80 ? 'success' : 'primary' }} mb-0">
                            {{ $internship->grade->final_score }}
                        </h1>
                        <span class="badge bg-{{ $internship->grade->final_score >= 80 ? 'success' : 'primary' }}">
                            {{ strtoupper($predikat) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Rincian Nilai -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">Rincian Penilaian</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Komponen Penilaian</th>
                                        <th class="text-center" width="20%">Angka (0-100)</th>
                                        <th class="text-center" width="20%">Kategori</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Non Teknis -->
                                    <tr class="table-light"><td colspan="3" class="ps-4 fw-bold text-secondary">A. ASPEK NON-TEKNIS (SOFT SKILLS)</td></tr>
                                    <tr>
                                        <td class="ps-4">Disiplin</td>
                                        <td class="text-center fw-bold">{{ $internship->grade->discipline }}</td>
                                        <td class="text-center text-muted">Non-Teknis</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Kerjasama Tim</td>
                                        <td class="text-center fw-bold">{{ $internship->grade->teamwork }}</td>
                                        <td class="text-center text-muted">Non-Teknis</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Inisiatif</td>
                                        <td class="text-center fw-bold">{{ $internship->grade->initiative }}</td>
                                        <td class="text-center text-muted">Non-Teknis</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Tanggung Jawab</td>
                                        <td class="text-center fw-bold">{{ $internship->grade->responsibility }}</td>
                                        <td class="text-center text-muted">Non-Teknis</td>
                                    </tr>

                                    <!-- Teknis -->
                                    <tr class="table-light"><td colspan="3" class="ps-4 fw-bold text-secondary">B. ASPEK TEKNIS (HARD SKILLS)</td></tr>
                                    <tr>
                                        <td class="ps-4">Penguasaan Materi / Kompetensi</td>
                                        <td class="text-center fw-bold">{{ $internship->grade->technical_mastery }}</td>
                                        <td class="text-center text-muted">Teknis</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Kualitas Hasil Kerja</td>
                                        <td class="text-center fw-bold">{{ $internship->grade->work_quality }}</td>
                                        <td class="text-center text-muted">Teknis</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if($internship->grade->notes)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Catatan Evaluasi</h6>
                        <p class="text-muted fst-italic mb-0">"{{ $internship->grade->notes }}"</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>