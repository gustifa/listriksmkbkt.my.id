@extends('layouts.app')

@section('title', 'Buat Tugas Baru')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            &larr; Kembali
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
                    <div class="col-12">
                        <label for="title" class="form-label fw-semibold text-dark">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control form-control-lg fs-6 @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Tugas Praktikum 1 - Jaringan Komputer" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

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

                    <div class="col-md-6">
                        <label for="classroom_id" class="form-label fw-semibold text-dark">Target Kelas <span class="text-danger">*</span></label>
                        <select name="classroom_id" id="classroom_id" class="form-select @error('classroom_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                        @error('classroom_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @if(Auth::user()->hasRole('admin'))
                        <div class="col-12">
                            <label for="teacher_id" class="form-label fw-semibold text-dark">Guru Pembuat <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label for="due_date" class="form-label fw-semibold text-dark">Tenggat Waktu Pengumpulan (Due Date) <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}" required>
                        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="max_score" class="form-label fw-semibold text-dark">Nilai Maksimal <span class="text-danger">*</span></label>
                        <input type="number" name="max_score" id="max_score" class="form-control @error('max_score') is-invalid @enderror" value="{{ old('max_score', 100) }}" min="1" max="100" required>
                        @error('max_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="instructions" class="form-label fw-semibold text-dark">Instruksi & Soal Tugas <span class="text-danger">*</span></label>
                        <textarea name="instructions" id="instructions" rows="5" class="form-control @error('instructions') is-invalid @enderror" placeholder="Tuliskan petunjuk pengerjaan tugas secara rinci di sini..." required>{{ old('instructions') }}</textarea>
                        @error('instructions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="file" class="form-label fw-semibold text-dark">Lampiran Berkas Instruksi / Panduan (PDF, Doc, Zip)</label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror">
                        <small class="text-muted d-block mt-1">Ukuran maksimal file: 10 MB</small>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
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