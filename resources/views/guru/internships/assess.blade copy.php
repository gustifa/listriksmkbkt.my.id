@section('title', 'Input Nilai PKL')

<x-app-layout>
    <div class="page-content">
        <div class="mb-4">
            <a href="{{ route('teacher.internships.index') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            <h4 class="mt-2 fw-bold text-primary">Form Penilaian PKL</h4>
        </div>

        <div class="row">
            <!-- INFO SISWA -->
            <div class="mb-4 col-md-4">
                <div class="border-0 shadow-sm card h-100">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2"></i>Data Siswa</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-center">
                            <div class="mx-auto mb-2 text-white avatar-circle bg-primary" style="width: 60px; height: 60px; line-height: 60px; font-size: 24px; border-radius: 50%;">
                                {{ substr($internship->student->name, 0, 1) }}
                            </div>
                            <h5 class="mb-0 fw-bold">{{ $internship->student->name }}</h5>
                            <span class="text-muted">{{ $internship->student->nis }}</span>
                        </div>
                        <hr>
                        <p class="mb-1 small fw-bold text-muted">KELAS</p>
                        <p>{{ $internship->student->classroom->name ?? '-' }}</p>

                        <p class="mb-1 small fw-bold text-muted">TEMPAT PKL</p>
                        <p class="fw-bold text-primary">{{ $internship->industry->name }}</p>
                        <p class="small text-muted">{{ $internship->industry->address }}</p>

                        <p class="mb-1 small fw-bold text-muted">PERIODE</p>
                        <p class="mb-0">{{ $internship->start_date->format('d M Y') }} - {{ $internship->end_date->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- FORM PENILAIAN -->
            <div class="col-md-8">
                <div class="border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-star me-2"></i>Input Nilai</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('teacher.internships.store', $internship->id) }}" method="POST">
                            @csrf

                            @php $g = $internship->grade; @endphp

                            <h6 class="pb-2 mb-3 text-primary fw-bold border-bottom">A. Aspek Non-Teknis (Soft Skills)</h6>
                            <div class="mb-4 row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Disiplin (0-100)</label>
                                    <input type="number" name="discipline" class="form-control" min="0" max="100" required value="{{ $g->discipline ?? 0 }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Kerjasama (0-100)</label>
                                    <input type="number" name="teamwork" class="form-control" min="0" max="100" required value="{{ $g->teamwork ?? 0 }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Inisiatif (0-100)</label>
                                    <input type="number" name="initiative" class="form-control" min="0" max="100" required value="{{ $g->initiative ?? 0 }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Tanggung Jawab (0-100)</label>
                                    <input type="number" name="responsibility" class="form-control" min="0" max="100" required value="{{ $g->responsibility ?? 0 }}">
                                </div>
                            </div>

                            <h6 class="pb-2 mb-3 text-primary fw-bold border-bottom">B. Aspek Teknis (Hard Skills)</h6>
                            <div class="mb-4 row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Penguasaan Materi (0-100)</label>
                                    <input type="number" name="technical_mastery" class="form-control" min="0" max="100" required value="{{ $g->technical_mastery ?? 0 }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Kualitas Kerja (0-100)</label>
                                    <input type="number" name="work_quality" class="form-control" min="0" max="100" required value="{{ $g->work_quality ?? 0 }}">
                                </div>
                            </div>

                            <h6 class="pb-2 mb-3 text-primary fw-bold border-bottom">C. Catatan & Evaluasi</h6>
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Catatan Pembimbing</label>
                                <textarea name="notes" class="form-control" rows="4" placeholder="Tuliskan catatan evaluasi untuk siswa...">{{ $g->notes ?? '' }}</textarea>
                            </div>

                            <div class="d-flex justify-content-end">
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
