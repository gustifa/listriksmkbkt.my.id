@extends('layouts.app')

@section('title', 'Detail Tugas - ' . $assignment->title)

@section('content')
<div class="container-fluid py-3 px-2 px-md-3">
    <!-- Header Navigation & Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Daftar Tugas
            </a>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-md-3">{{ $assignment->title }}</h3>
                <span class="badge bg-primary text-white fw-bold">
                    {{ $assignment->subject->name ?? 'Mata Pelajaran' }}
                </span>
                <span class="badge bg-light text-dark border fw-bold">
                    {{ $assignment->classroom->name ?? 'Kelas' }}
                </span>
            </div>
        </div>

        <!-- Tombol Edit Tugas (HANYA UNTUK GURU & ADMIN) -->
        @hasanyrole('guru|admin')
            <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
                <button type="button" class="btn btn-warning text-dark fw-semibold btn-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#editAssignmentModal-{{ $assignment->id }}">
                    <i class="fas fa-edit me-1"></i> Edit Tugas
                </button>
            </div>
        @endhasanyrole
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Ringkasan Informasi Tugas -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-file-alt text-primary me-2"></i>Instruksi Tugas
                    </h6>
                </div>
                <div class="card-body p-3 p-md-4 text-dark">
                    <div class="mb-3" style="word-break: break-word;">
                        {!! nl2br(e($assignment->instructions)) !!}
                    </div>

                    @if($assignment->file_path)
                        <div class="p-3 bg-light border rounded-3 d-flex align-items-center justify-content-between mt-3">
                            <div class="d-flex align-items-center me-2">
                                <i class="fas fa-paperclip text-info fa-lg me-2"></i>
                                <span class="fw-semibold text-dark small text-truncate" style="max-width: 250px;">
                                    {{ basename($assignment->file_path) }}
                                </span>
                            </div>
                            <a href="{{ Storage::url($assignment->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="fas fa-download me-1"></i> Unduh Lampiran
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">Informasi Penugasan</h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Guru Pengampu</small>
                        <span class="fw-bold text-dark fs-6">{{ $assignment->teacher->name ?? '-' }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Tenggat Waktu (Due Date)</small>
                        <span class="fw-bold text-danger fs-6">
                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y, H:i') }}
                        </span>
                    </div>

                    <div class="mb-0">
                        <small class="text-muted d-block mb-1">Nilai Maksimal</small>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-6">
                            {{ $assignment->max_score }} Poin
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- TAMPILAN KHUSUS SISWA: FORMULIR PENGUMPULAN TUGAS -->
    <!-- ============================================================ -->
    @hasrole('siswa')
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-upload text-primary me-2"></i>Formulir Pengumpulan Tugas Saya
                </h6>
            </div>
            <div class="card-body p-4">
                @php
                    $mySubmission = $assignment->submissions->where('student_id', optional(Auth::user()->student)->id)->first();
                @endphp

                @if($mySubmission && $mySubmission->status === 'graded')
                    <!-- Tampilan Jika Sudah Dinilai Guru -->
                    <div class="alert alert-success border-success-subtle mb-0 p-3">
                        <h6 class="fw-bold text-success mb-2">
                            <i class="fas fa-check-circle me-1"></i> Tugas Anda Sudah Dinilai!
                        </h6>
                        <div class="fs-4 fw-bold text-dark mb-2">
                            Nilai: <span class="text-success">{{ $mySubmission->score }}</span> / {{ $assignment->max_score }}
                        </div>
                        @if($mySubmission->feedback)
                            <div class="p-2 bg-white rounded border text-muted small">
                                <strong>Catatan Guru:</strong> <em>"{{ $mySubmission->feedback }}"</em>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Form Kirim / Perbarui Jawaban -->
                    <form action="{{ route('student.submissions.store', $assignment->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Teks Jawaban / Catatan untuk Guru</label>
                            <textarea name="submission_text" rows="4" class="form-control" placeholder="Tuliskan teks jawaban atau catatan ringkas di sini...">{{ old('submission_text', $mySubmission->submission_text ?? '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Unggah Berkas Tugas (PDF, Docx, Zip, Foto)</label>
                            <input type="file" name="file" class="form-control">
                            @if($mySubmission && $mySubmission->file_path)
                                <small class="text-muted d-block mt-1">
                                    File terunggah saat ini: <a href="{{ Storage::url($mySubmission->file_path) }}" target="_blank" class="text-primary fw-bold">{{ basename($mySubmission->file_path) }}</a>
                                </small>
                            @endif
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2">
                            <div>
                                @if($mySubmission)
                                    <span class="badge bg-success-subtle text-success border px-3 py-2">
                                        <i class="fas fa-check me-1"></i> Sudah Dikirim ({{ \Carbon\Carbon::parse($mySubmission->submitted_at)->format('d M Y, H:i') }})
                                    </span>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="fas fa-paper-plane me-1"></i> {{ $mySubmission ? 'Perbarui Pengumpulan' : 'Kirimkan Tugas' }}
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endhasrole

    <!-- ============================================================ -->
    <!-- TAMPILAN KHUSUS GURU & ADMIN: TABEL PENILAIAN SISWA -->
    <!-- ============================================================ -->
    @hasanyrole('guru|admin')
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0 text-dark fs-6">Daftar Pengumpulan Siswa</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Nama Siswa</th>
                                <th>Waktu Mengumpulkan</th>
                                <th>Berkas / Jawaban</th>
                                <th>Nilai</th>
                                <th class="text-center pe-4">Aksi / Penilaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignment->submissions as $index => $submission)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $submission->student->name ?? '-' }}</div>
                                        <small class="text-muted">NISN: {{ $submission->student->nis ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="small text-dark">
                                            {{ \Carbon\Carbon::parse($submission->submitted_at)->format('d M Y, H:i') }}
                                        </span>
                                        @if(\Carbon\Carbon::parse($submission->submitted_at)->gt($assignment->due_date))
                                            <span class="badge bg-danger ms-1">Terlambat</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($submission->file_path)
                                            <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="badge bg-info-subtle text-info border border-info-subtle text-decoration-none px-2 py-1">
                                                <i class="fas fa-file-download me-1"></i> File Siswa
                                            </a>
                                        @else
                                            <span class="text-muted small">Teks Jawaban</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($submission->score !== null)
                                            <span class="fw-bold text-success fs-6">{{ $submission->score }}</span> / {{ $assignment->max_score }}
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border">Belum Dinilai</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <button type="button" class="btn btn-sm btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalGrade-{{ $submission->id }}">
                                            <i class="fas fa-check-circle me-1"></i> Beri Nilai
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Belum ada siswa yang mengumpulkan tugas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Beri Nilai Siswa (Guru/Admin) -->
        @foreach($assignment->submissions as $submission)
            <div class="modal fade" id="modalGrade-{{ $submission->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="{{ route('submissions.grade', $submission->id) }}" method="POST">
                            @csrf
                            <div class="modal-header bg-light">
                                <h6 class="modal-title fw-bold text-dark">
                                    <i class="fas fa-star text-warning me-2"></i>Penilaian Tugas - {{ $submission->student->name ?? 'Siswa' }}
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                @if($submission->submission_text)
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-dark">Teks Jawaban Siswa:</label>
                                        <div class="p-3 bg-light border rounded small">{!! nl2br(e($submission->submission_text)) !!}</div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">Nilai (Maksimal {{ $assignment->max_score }}) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.1" name="score" class="form-control" value="{{ $submission->score }}" max="{{ $assignment->max_score }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">Catatan / Feedback Guru</label>
                                    <textarea name="feedback" rows="3" class="form-control" placeholder="Tuliskan masukan untuk siswa...">{{ $submission->feedback }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer bg-light py-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan Nilai</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endhasanyrole
</div>
@endsection