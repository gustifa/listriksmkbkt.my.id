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

                    <!-- Guru Pengampu (Khusus Admin) -->
                    @if(Auth::user()->hasRole('admin'))
                        <div class="col-12">
                            <label for="teacher_id" class="form-label fw-semibold text-dark">Guru Pengampu <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <!-- Pilih Target Kelas -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark mb-2">Target Kelas <span class="text-danger">*</span></label>
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="row g-2">
                                @foreach($classrooms as $classroom)
                                    <div class="col-md-3 col-6">
                                        <div class="form-check bg-white p-2 border rounded">
                                            <input class="form-check-input ms-1 me-2" type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}" id="class_{{ $classroom->id }}"
                                                {{ is_array(old('classroom_ids')) && in_array($classroom->id, old('classroom_ids')) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium text-dark" for="class_{{ $classroom->id }}">
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

                    <!-- Status Switch -->
                    <div class="col-12 mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label fw-semibold text-dark" for="is_active">Aktifkan Ujian Ini</label>
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
@endsection