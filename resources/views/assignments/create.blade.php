@extends('layouts.app')

@section('title', 'Buat Tugas Baru')

@push('styles')
    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 42px;
            padding-top: 5px;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            ← Kembali
        </a>
        <div>
            <h3 class="fw-bold text-dark mb-0">Buat Tugas Baru</h3>
            <p class="text-muted small mb-0">Lengkapi formulir di bawah ini untuk menambahkan tugas siswa baru.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('assignments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <!-- Judul Tugas -->
                    <div class="col-12">
                        <label for="title" class="form-label fw-semibold text-dark">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control form-control-lg fs-6 @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Tugas Praktikum 1 - Jaringan Komputer" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- 1. PILIH KELAS -->
                    <div class="col-md-6">
                        <label for="classroom_id" class="form-label fw-semibold text-dark">Target Kelas <span class="text-danger">*</span></label>
                        <select name="classroom_id" id="classroom_id" class="form-select select2 @error('classroom_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                        @error('classroom_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <!-- 2. PILIH MATA PELAJARAN (Otomatis terisi sesuai kelas) -->
                    <div class="col-md-6">
                        <label for="subject_id" class="form-label fw-semibold text-dark">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-select select2 @error('subject_id') is-invalid @enderror" required disabled>
                            <option value="">-- Pilih Kelas Dulu --</option>
                        </select>
                        @error('subject_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <!-- 3. PILIH TUJUAN PEMBELAJARAN (Otomatis terisi sesuai mapel) -->
                    <div class="col-12">
                        <label for="learning_objective_id" class="form-label fw-semibold text-dark">Tujuan Pembelajaran (TP)</label>
                        <select name="learning_objective_id" id="learning_objective_id" class="form-select select2 @error('learning_objective_id') is-invalid @enderror" disabled>
                            <option value="">-- Pilih Mata Pelajaran Dulu --</option>
                        </select>
                        @error('learning_objective_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <!-- Guru Pembuat (Khusus Admin) -->
                    @if(Auth::user()->hasRole('admin'))
                        <div class="col-12">
                            <label for="teacher_id" class="form-label fw-semibold text-dark">Guru Pembuat <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="teacher_id" class="form-select select2 @error('teacher_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <!-- Tenggat Waktu (Due Date) -->
                    <div class="col-md-6">
                        <label for="due_date" class="form-label fw-semibold text-dark">Tenggat Waktu Pengumpulan (Due Date) <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}" required>
                        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Nilai Maksimal -->
                    <div class="col-md-6">
                        <label for="max_score" class="form-label fw-semibold text-dark">Nilai Maksimal <span class="text-danger">*</span></label>
                        <input type="number" name="max_score" id="max_score" class="form-control @error('max_score') is-invalid @enderror" value="{{ old('max_score', 100) }}" min="1" max="100" step="1" required>
                        @error('max_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Instruksi Tugas -->
                    <div class="col-12">
                        <label for="instructions" class="form-label fw-semibold text-dark">Instruksi & Soal Tugas <span class="text-danger">*</span></label>
                        <textarea name="instructions" id="instructions" rows="5" class="form-control @error('instructions') is-invalid @enderror" placeholder="Tuliskan petunjuk pengerjaan tugas secara rinci di sini..." required>{{ old('instructions') }}</textarea>
                        @error('instructions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Lampiran File -->
                    <div class="col-12">
                        <label for="file" class="form-label fw-semibold text-dark">Lampiran Berkas Instruksi / Panduan (PDF, Doc, Zip)</label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror">
                        <small class="text-muted d-block mt-1">Ukuran maksimal file: 10 MB</small>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Pilihan Publikasi ke Siswa -->
                    <div class="col-12 mt-3">
                        <div class="p-3 bg-light rounded border">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="is_published">
                                    Publikasikan Tugas ke Siswa
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Jika <strong>tidak dicentang</strong>, tugas hanya akan tampil pada halaman rekapitulasi guru dan tidak terlihat di dashboard siswa.
                            </small>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('assignments.index') }}" class="btn btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fas fa-save me-1"></i> Simpan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- jQuery & Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            const $subjectSelect = $('#subject_id');
            const $tpSelect = $('#learning_objective_id');

            // 1. Event saat KELAS berubah -> Fetch MAPEL
            $('#classroom_id').on('change', function() {
                const classroomId = $(this).val();

                if (classroomId) {
                    $subjectSelect.html('<option value="">-- Memuat Mata Pelajaran... --</option>').prop('disabled', true);
                    $tpSelect.html('<option value="">-- Pilih Mata Pelajaran Dulu --</option>').prop('disabled', true).trigger('change');

                    $.ajax({
                        url: `/api/get-subjects-by-classroom/${classroomId}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(subjects) {
                            let options = '<option value="">-- Pilih Mata Pelajaran --</option>';
                            if (subjects && subjects.length > 0) {
                                subjects.forEach(function(subject) {
                                    options += `<option value="${subject.id}">${subject.name}</option>`;
                                });
                                $subjectSelect.html(options).prop('disabled', false);
                            } else {
                                $subjectSelect.html('<option value="">-- Tidak Ada Mapel di Kelas Ini --</option>').prop('disabled', true);
                            }
                            $subjectSelect.trigger('change');
                        },
                        error: function() {
                            $subjectSelect.html('<option value="">-- Gagal Memuat Mapel --</option>').prop('disabled', true).trigger('change');
                        }
                    });
                } else {
                    $subjectSelect.html('<option value="">-- Pilih Kelas Dulu --</option>').prop('disabled', true).trigger('change');
                    $tpSelect.html('<option value="">-- Pilih Mata Pelajaran Dulu --</option>').prop('disabled', true).trigger('change');
                }
            });

            // 2. Event saat MAPEL berubah -> Fetch TP
            $subjectSelect.on('change', function() {
                const subjectId = $(this).val();

                if (subjectId) {
                    $tpSelect.html('<option value="">-- Memuat Tujuan Pembelajaran... --</option>').prop('disabled', true);

                    $.ajax({
                        url: `/api/get-tp-by-subject/${subjectId}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(tps) {
                            let options = '<option value="">-- Pilih Tujuan Pembelajaran (Opsional) --</option>';
                            if (tps && tps.length > 0) {
                                tps.forEach(function(tp) {
                                    const codeStr = tp.code ? tp.code + ' - ' : '';
                                    options += `<option value="${tp.id}">${codeStr}${tp.description}</option>`;
                                });
                                $tpSelect.html(options).prop('disabled', false);
                            } else {
                                $tpSelect.html('<option value="">-- Tidak Ada TP untuk Mapel Ini --</option>').prop('disabled', false);
                            }
                            $tpSelect.trigger('change');
                        },
                        error: function() {
                            $tpSelect.html('<option value="">-- Gagal Memuat TP --</option>').prop('disabled', true).trigger('change');
                        }
                    });
                } else {
                    $tpSelect.html('<option value="">-- Pilih Mata Pelajaran Dulu --</option>').prop('disabled', true).trigger('change');
                }
            });
        });
    </script>
@endpush