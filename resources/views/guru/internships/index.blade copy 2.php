@section('title', 'Penilaian PKL Siswa')

<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-chalkboard-teacher me-2"></i>Siswa Bimbingan PKL</h4>
            
            <!-- Export Excel Rekap Penilaian Ujian Jurnal -->
            <a href="{{ route('internships.exam.export_excel') }}" class="btn btn-success btn-sm fw-bold shadow-sm">
                <i class="fas fa-file-excel me-1"></i> Export Excel Rekap Ujian
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="border-0 shadow-sm card">
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%" class="text-center ps-3">No</th>
                                <th width="40%">Informasi Siswa & Tempat PKL</th>
                                <th width="20%" class="text-center">Nilai & Status Penilaian</th>
                                <th width="35%" class="text-center">Aksi Penilaian & Cetak Rapor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($internships as $index => $item)
                            <tr>
                                <td class="text-center fw-bold text-muted ps-3">
                                    {{ $internships->firstItem() + $index }}
                                </td>
                                <td>
                                    <!-- Nama Siswa & Kelas -->
                                    <div class="fw-bold text-dark fs-6">{{ $item->student->name }}</div>
                                    <div class="text-muted small mb-1">
                                        <i class="fas fa-id-card me-1 text-secondary"></i> {{ $item->student->nis }} • {{ $item->student->classroom->name ?? '-' }}
                                    </div>
                                    
                                    <!-- Tempat PKL & Periode -->
                                    <div class="p-2 rounded bg-light border-start border-3 border-primary">
                                        <div class="fw-bold text-primary small">
                                            <i class="fas fa-building me-1"></i> {{ $item->industry->name }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ \Illuminate\Support\Str::limit($item->industry->address, 35) }}
                                        </div>
                                        <div class="text-secondary small mt-1">
                                            <i class="far fa-calendar-alt me-1 text-info"></i> {{ $item->start_date->format('d/m/Y') }} s.d {{ $item->end_date->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <!-- Status Nilai TP Observasi -->
                                    <div class="mb-2">
                                        <small class="d-block text-muted mb-1 fw-bold">Nilai TP Observasi:</small>
                                        @if($item->tpScore)
                                            <span class="badge bg-success fs-6" title="Rerata Observasi TP">
                                                <i class="fas fa-check-circle me-1"></i> {{ number_format($item->tpScore->final_tp_score, 1) }}
                                            </span>
                                        @elseif($item->grade)
                                            <span class="badge bg-success fs-6">{{ $item->grade->final_score }}</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Dinilai</span>
                                        @endif
                                    </div>

                                    <!-- Status Ujian Jurnal -->
                                    <div>
                                        <small class="d-block text-muted mb-1 fw-bold">Ujian Jurnal:</small>
                                        @if($item->exam)
                                            <span class="badge bg-info text-white">
                                                <i class="fas fa-award me-1"></i> {{ number_format($item->exam->total_score ?? 0, 1) }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark border">Belum Ujian</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        // Pengecekan Route Cetak Rapor PDF yang Aman
                                        $transcriptRoute = \Illuminate\Support\Facades\Route::has('guru.internships.transcript.print') 
                                            ? route('guru.internships.transcript.print', $item->id) 
                                            : (\Illuminate\Support\Facades\Route::has('internships.transcript.print') 
                                                ? route('internships.transcript.print', $item->id) 
                                                : '#');
                                    @endphp

                                    <!-- 1. Tombol Input / Edit TP -->
                                    <a href="{{ route('guru.internships.tp.create', $item->id) }}" class="btn btn-sm btn-{{ $item->tpScore ? 'warning' : 'success' }} fw-bold me-1 mb-1 shadow-sm">
                                        <i class="fas fa-tasks me-1"></i> {{ $item->tpScore ? 'Edit TP' : 'Input TP' }}
                                    </a>

                                    <!-- 2. Tombol Cetak Rapor PKL PDF (Langsung Tampil saat TP Sudah Dinilai) -->
                                    @if($item->tpScore)
                                        <a href="{{ $transcriptRoute }}" class="btn btn-sm btn-dark fw-bold me-1 mb-1 shadow-sm" target="_blank" title="Cetak Rapor PKL PDF">
                                            <i class="fas fa-file-pdf me-1 text-danger"></i> Cetak Rapor
                                        </a>
                                    @endif

                                    <!-- 3. Group Opsi Penilaian Lainnya -->
                                    <div class="btn-group mb-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle fw-bold" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v me-1"></i> Lainnya
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <!-- Input/Edit Nilai Awal (Bawaan) -->
                                            <li>
                                                <a class="dropdown-item py-2" href="{{ route('teacher.internships.assess', $item->id) }}">
                                                    <i class="fas fa-edit me-2 text-warning"></i> {{ $item->grade ? 'Edit Nilai Bawaan' : 'Input Nilai Bawaan' }}
                                                </a>
                                            </li>
                                            
                                            <!-- Ujian Jurnal -->
                                            <li>
                                                <a class="dropdown-item py-2" href="{{ route('internships.exam.create', $item->id) }}">
                                                    <i class="fas fa-file-alt me-2 text-info"></i> {{ $item->exam ? 'Edit Ujian Jurnal' : 'Input Ujian Jurnal' }}
                                                </a>
                                            </li>

                                            <!-- Cetak Hasil Ujian Jurnal -->
                                            @if($item->exam)
                                                <li class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ route('internships.exam.print', $item->id) }}" target="_blank">
                                                        <i class="fas fa-print me-2 text-secondary"></i> Cetak Lembar Ujian PDF
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-muted">Anda belum memiliki siswa bimbingan PKL.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white card-footer">
                {{ $internships->links() }}
            </div>
        </div>
    </div>
</x-app-layout>