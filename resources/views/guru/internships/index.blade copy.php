@section('title', 'Penilaian PKL Siswa')

<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-chalkboard-teacher me-2"></i>Siswa Bimbingan PKL</h4>
            
            <!-- Export Excel Rekap Penilaian Ujian Jurnal (Dipertahankan) -->
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
                                <th class="ps-4">Siswa</th>
                                <th>Tempat PKL</th>
                                <th>Periode</th>
                                <th class="text-center">Nilai Akhir</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($internships as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $item->student->name }}</div>
                                    <small class="text-muted">{{ $item->student->nis }} • {{ $item->student->classroom->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $item->industry->name }}</div>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> {{ \Illuminate\Support\Str::limit($item->industry->address, 30) }}</small>
                                </td>
                                <td>
                                    <small>
                                        {{ $item->start_date->format('d/m/y') }} s.d {{ $item->end_date->format('d/m/y') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if($item->tpScore)
                                        <span class="badge bg-success fs-6" title="Rerata Observasi TP">{{ number_format($item->tpScore->final_tp_score, 1) }}</span>
                                    @elseif($item->grade)
                                        <span class="badge bg-success fs-6">{{ $item->grade->final_score }}</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <!-- 1. Tombol Bawaan (Dipertahankan) -->
                                    <a href="{{ route('teacher.internships.assess', $item->id) }}" class="btn btn-sm btn-{{ $item->grade ? 'warning' : 'primary' }} fw-bold me-1 mb-1">
                                        <i class="fas fa-edit me-1"></i> {{ $item->grade ? 'Edit Nilai' : 'Input Nilai' }}
                                    </a>

                                    <!-- 2. Tombol Penilaian Observasi TP Dinamis -->
                                    <a href="{{ route('guru.internships.tp.create', $item->id) }}" class="btn btn-sm btn-{{ $item->tpScore ? 'warning' : 'success' }} fw-bold me-1 mb-1" title="Penilaian Observasi TP">
                                        <i class="fas fa-tasks me-1"></i> {{ $item->tpScore ? 'Edit TP' : 'Input TP' }}
                                    </a>

                                    <!-- 3. Tombol Ujian Jurnal (Dipertahankan) -->
                                    <a href="{{ route('internships.exam.create', $item->id) }}" class="btn btn-sm btn-info text-white fw-bold me-1 mb-1">
                                        <i class="fas fa-file-alt me-1"></i> {{ $item->exam ? 'Edit Ujian' : 'Ujian Jurnal' }}
                                    </a>

                                    <!-- 4. Tombol Cetak PDF Hasil Ujian Jurnal (Dipertahankan) -->
                                    @if($item->exam)
                                        <a href="{{ route('internships.exam.print', $item->id) }}" class="btn btn-sm btn-secondary fw-bold me-1 mb-1" target="_blank" title="Cetak Lembar Penilaian Ujian">
                                            <i class="fas fa-print me-1"></i> Cetak Ujian
                                        </a>
                                    @endif

                                    <!-- 5. Tombol Cetak Rapor / Transkrip PKL (Aman dari RouteNotFoundException) -->
                                    @if($item->tpScore)
                                        @php
                                            $transcriptRoute = \Illuminate\Support\Facades\Route::has('guru.internships.transcript.print') 
                                                ? route('guru.internships.transcript.print', $item->id) 
                                                : (\Illuminate\Support\Facades\Route::has('internships.transcript.print') 
                                                    ? route('internships.transcript.print', $item->id) 
                                                    : '#');
                                        @endphp
                                        <a href="{{ $transcriptRoute }}" class="btn btn-sm btn-dark fw-bold mb-1" target="_blank" title="Cetak Transkrip / Rapor PKL PDF">
                                            <i class="fas fa-file-pdf me-1"></i> Cetak Rapor
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-muted">Anda belum memiliki siswa bimbingan PKL.</td>
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