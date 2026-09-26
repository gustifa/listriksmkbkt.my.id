@section('title', 'Input Nilai PKL')

<x-app-layout>
    <div class="page-content">
        <div class="mb-4">
            <a href="{{ route('teacher.internships.index') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            <h4 class="mt-2 fw-bold text-primary">Form Penilaian PKL</h4>
        </div>

        <div class="row">
            <!-- KOLOM KIRI: DATA SISWA & JURNAL -->
            <div class="mb-4 col-md-5">

                <!-- 1. DATA SISWA -->
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2"></i>Data Siswa</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-center">
                            <div class="mx-auto mb-2 text-white avatar-circle bg-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 24px; border-radius: 50%;">
                                {{ substr($internship->student->name, 0, 1) }}
                            </div>
                            <h5 class="mb-0 fw-bold">{{ $internship->student->name }}</h5>
                            <span class="text-muted">{{ $internship->student->nis }}</span>
                        </div>
                        <hr>
                        <div class="row g-2 small">
                            <div class="col-4 fw-bold text-muted">KELAS</div>
                            <div class="col-8">: {{ $internship->student->classroom->name ?? '-' }}</div>

                            <div class="col-4 fw-bold text-muted">INDUSTRI</div>
                            <div class="col-8">: {{ $internship->industry->name }}</div>

                            <div class="col-4 fw-bold text-muted">PERIODE</div>
                            <div class="col-8">: {{ $internship->start_date->format('d M') }} - {{ $internship->end_date->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- 2. REKAP KEHADIRAN (NEW) -->
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2"></i>Rekap Kehadiran</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center row">
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-success">{{ $summary['present'] ?? 0 }}</h4>
                                <small class="text-muted">Hadir</small>
                            </div>
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-warning">{{ $summary['sick'] ?? 0 }}</h4>
                                <small class="text-muted">Sakit</small>
                            </div>
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-info">{{ $summary['permit'] ?? 0 }}</h4>
                                <small class="text-muted">Izin</small>
                            </div>
                            <div class="col">
                                <h4 class="mb-0 fw-bold text-secondary">{{ $summary['total'] ?? 0 }}</h4>
                                <small class="text-muted">Total Hari</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. JURNAL KEGIATAN (NEW) -->
                <div class="border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-book-open me-2"></i>Jurnal Kegiatan</h6>
                        <small class="text-muted">Log Aktivitas Siswa</small>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table mb-0 table-sm table-hover small">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="ps-3">Tanggal</th>
                                        <th class="text-center">Jam</th>
                                        <th>Kegiatan</th>
                                        <th class="text-center">Bukti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendances as $log)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ \Carbon\Carbon::parse($log->date)->format('d/m') }}</td>
                                        <td class="text-center">
                                            <span class="d-block text-success">{{ \Carbon\Carbon::parse($log->time)->format('H:i') }}</span>
                                            <span class="d-block text-danger">{{ $log->check_out_time ? \Carbon\Carbon::parse($log->check_out_time)->format('H:i') : '-' }}</span>
                                        </td>
                                        <td>
                                            @if($log->status == 'present')
                                                <div class="text-truncate" style="max-width: 180px;" title="{{ $log->activity_log }}">
                                                    {{ $log->activity_log ?? '-' }}
                                                </div>
                                            @else
                                                <span class="badge bg-{{ $log->status == 'sick' ? 'warning' : 'info' }}">
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($log->photo_path)
                                                <a href="{{ asset('storage/'.$log->photo_path) }}" target="_blank" class="text-secondary"><i class="fas fa-image"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="py-3 text-center text-muted">Belum ada data jurnal.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: FORM PENILAIAN -->
            <div class="col-md-7">
                <div class="border-0 shadow-sm card sticky-top" style="top: 20px; z-index: 1;">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-star me-2"></i>Input Nilai</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('teacher.internships.store', $internship->id) }}" method="POST">
                            @csrf

                            @php $g = $internship->grade; @endphp

                            <div class="mb-4 alert alert-info small">
                                <i class="fas fa-info-circle me-1"></i> Silakan isi nilai (skala 0-100) berdasarkan pemantauan jurnal di samping dan laporan industri.
                            </div>

                            <h6 class="pb-2 mb-3 text-primary fw-bold border-bottom">A. Aspek Non-Teknis (Soft Skills)</h6>
                            <div class="mb-4 row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Disiplin</label>
                                    <input type="number" name="discipline" class="form-control" min="0" max="100" required value="{{ $g->discipline ?? 0 }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Kerjasama</label>
                                    <input type="number" name="teamwork" class="form-control" min="0" max="100" required value="{{ $g->teamwork ?? 0 }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Inisiatif</label>
                                    <input type="number" name="initiative" class="form-control" min="0" max="100" required value="{{ $g->initiative ?? 0 }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Tanggung Jawab</label>
                                    <input type="number" name="responsibility" class="form-control" min="0" max="100" required value="{{ $g->responsibility ?? 0 }}">
                                </div>
                            </div>

                            <h6 class="pb-2 mb-3 text-primary fw-bold border-bottom">B. Aspek Teknis (Hard Skills)</h6>
                            <div class="mb-4 row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Penguasaan Materi</label>
                                    <input type="number" name="technical_mastery" class="form-control" min="0" max="100" required value="{{ $g->technical_mastery ?? 0 }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Kualitas Kerja</label>
                                    <input type="number" name="work_quality" class="form-control" min="0" max="100" required value="{{ $g->work_quality ?? 0 }}">
                                </div>
                            </div>

                            <h6 class="pb-2 mb-3 text-primary fw-bold border-bottom">C. Catatan & Evaluasi</h6>
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Catatan Pembimbing</label>
                                <textarea name="notes" class="form-control" rows="4" placeholder="Tuliskan catatan evaluasi untuk siswa...">{{ $g->notes ?? '' }}</textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if($g)
                                        <span class="badge bg-success">Nilai Akhir Saat Ini: {{ $g->final_score }}</span>
                                    @endif
                                </div>
                                <button type="submit" class="px-4 btn btn-primary fw-bold">
                                    <i class="fas fa-save me-2"></i> Simpan Penilaian
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
