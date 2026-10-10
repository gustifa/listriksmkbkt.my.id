@extends('layouts.app')

@section('title', 'Buat Ujian Baru')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            &larr; Kembali
        </a>
        <div>
            <h3 class="fw-bold text-dark mb-0">Buat Ujian Baru</h3>
            <p class="text-muted small mb-0">Lengkapi formulir di bawah ini untuk menambahkan jadwal ujian baru</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('exams.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <!-- Judul / Nama Ujian -->
                    <div class="col-12">
                        <label for="title" class="form-label fw-semibold text-dark">Nama / Judul Ujian <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control form-control-lg fs-6 @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Penilaian Tengah Semester - Matematika" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Tipe Ujian -->
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-semibold text-dark">Tipe Ujian <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="pilihan_ganda" {{ old('type') == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda (Single Choice)</option>
                            <option value="multiple_choice" {{ old('type') == 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda Kompleks</option>
                            <option value="essay" {{ old('type') == 'essay' ? 'selected' : '' }}>Essay</option>
                            <option value="campuran" {{ old('type') == 'campuran' ? 'selected' : '' }}>Campuran (PG + Essay)</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Mata Pelajaran -->
                    <div class="col-md-6">
                        <label for="subject_id" class="form-label fw-semibold text-dark">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Guru Pengampu Utama (Khusus Admin) -->
                    @if(Auth::user()->hasRole('admin'))
                        <div class="col-12">
                            <label for="teacher_id" class="form-label fw-semibold text-dark">Guru Pembuat Utama <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <!-- PENGATURAN TEAM TEACHING / GURU KOLABORATOR -->
                    <div class="col-12 mt-3">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="form-check form-switch d-flex align-items-center gap-2 mb-2 ps-0">
                                <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" id="is_team_teaching" name="is_team_teaching" value="1" {{ old('is_team_teaching') ? 'checked' : '' }} onchange="toggleTeamTeaching(this.checked)">
                                <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="is_team_teaching">
                                    <i class="fas fa-users text-info me-1"></i> Aktifkan Team Teaching (Guru Kolaborator)
                                </label>
                            </div>
                            <small class="text-muted d-block ms-1">Aktifkan jika ujian ini diampu atau dikelola oleh lebih dari satu guru.</small>

                            <div id="collaborators_section" class="mt-3 {{ old('is_team_teaching') ? '' : 'd-none' }}">
                                <label class="form-label fw-semibold text-dark mb-2">Pilih Guru Kolaborator:</label>
                                <div class="border rounded-3 p-3 bg-white" style="max-height: 180px; overflow-y: auto;">
                                    <div class="row g-2">
                                        @forelse($teachers as $teacher)
                                            <div class="col-md-4 col-6">
                                                <div class="form-check p-1">
                                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="collaborator_ids[]" value="{{ $teacher->id }}" id="teacher_{{ $teacher->id }}"
                                                        {{ is_array(old('collaborator_ids')) && in_array($teacher->id, old('collaborator_ids')) ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-medium text-dark cursor-pointer mb-0" for="teacher_{{ $teacher->id }}">
                                                        {{ $teacher->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12"><small class="text-muted">Tidak ada data guru lain tersedia.</small></div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilih Target Kelas -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark mb-2">Target Kelas <span class="text-danger">*</span></label>
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="row g-2">
                                @foreach($classrooms as $classroom)
                                    <div class="col-md-3 col-6">
                                        <div class="form-check bg-white p-2 border rounded d-flex align-items-center">
                                            <input class="form-check-input ms-1 me-2" type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}" id="class_{{ $classroom->id }}"
                                                {{ is_array(old('classroom_ids')) && in_array($classroom->id, old('classroom_ids')) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium text-dark cursor-pointer mb-0" for="class_{{ $classroom->id }}">
                                                {{ $classroom->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('classroom_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- Durasi -->
                    <div class="col-md-4">
                        <label for="duration_minutes" class="form-label fw-semibold text-dark">Durasi (Menit) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_minutes" id="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes', 60) }}" min="1" required>
                        @error('duration_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Waktu Mulai -->
                    <div class="col-md-4">
                        <label for="start_time" class="form-label fw-semibold text-dark">Waktu Mulai <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                        @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Waktu Selesai -->
                    <div class="col-md-4">
                        <label for="end_time" class="form-label fw-semibold text-dark">Waktu Selesai <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                        @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Pengaturan Acak Ujian -->
                    <div class="col-12 mt-4">
                        <label class="form-label fw-semibold text-dark mb-2">Pengaturan Acak Ujian</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <div class="form-check form-switch d-flex align-items-center gap-2 mb-2 ps-0">
                                        <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" id="randomize_questions" name="randomize_questions" value="1" {{ old('randomize_questions') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="randomize_questions">
                                            <i class="fas fa-random text-primary me-1"></i> Acak Urutan Soal
                                        </label>
                                    </div>
                                    <small class="text-muted d-block ms-1">Urutan nomor soal akan diacak secara berbeda untuk setiap siswa.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <div class="form-check form-switch d-flex align-items-center gap-2 mb-2 ps-0">
                                        <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" id="randomize_options" name="randomize_options" value="1" {{ old('randomize_options') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="randomize_options">
                                            <i class="fas fa-sort-alpha-down-alt text-success me-1"></i> Acak Pilihan Jawaban (Opsi)
                                        </label>
                                    </div>
                                    <small class="text-muted d-block ms-1">Pilihan jawaban (A, B, C, D, E) akan diacak secara otomatis saat siswa mengerjakan.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Hasil & Review Siswa -->
                    <div class="col-12 mt-3">
                        <label class="form-label fw-semibold text-dark mb-2">Pengaturan Hasil & Review Siswa</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <div class="form-check form-switch d-flex align-items-center gap-2 mb-2 ps-0">
                                        <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" id="allow_review" name="allow_review" value="1" {{ old('allow_review', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="allow_review">
                                            <i class="fas fa-eye text-info me-1"></i> Izinkan Review Ujian
                                        </label>
                                    </div>
                                    <small class="text-muted d-block ms-1">Siswa diizinkan membuka kembali lembar pengerjaan setelah selesai.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <div class="form-check form-switch d-flex align-items-center gap-2 mb-2 ps-0">
                                        <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" id="show_correct_answer" name="show_correct_answer" value="1" {{ old('show_correct_answer') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="show_correct_answer">
                                            <i class="fas fa-check-circle text-warning me-1"></i> Tampilkan Kunci Jawaban
                                        </label>
                                    </div>
                                    <small class="text-muted d-block ms-1">Menampilkan indikator benar/salah dan kunci jawaban pada halaman review.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Switch -->
                    <div class="col-12 mt-3">
                        <div class="form-check form-switch d-flex align-items-center gap-2 p-2 ps-0">
                            <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label fw-semibold text-dark cursor-pointer mb-0" for="is_active">Aktifkan Ujian Ini</label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('exams.index') }}" class="btn btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Simpan Ujian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleTeamTeaching(isChecked) {
        const section = document.getElementById('collaborators_section');
        if (isChecked) {
            section.classList.remove('d-none');
        } else {
            section.classList.add('d-none');
        }
    }
</script>
@endsection