@extends('layouts.app')

@section('title', 'Tambah Materi Pembelajaran')

@section('content')
<div class="container-fluid py-3">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            &larr; Kembali
        </a>
        <div>
            <h3 class="fw-bold text-dark mb-0">Tambah Materi Pembelajaran</h3>
            <p class="text-muted small mb-0">Lengkapi formulir di bawah ini untuk menambahkan modul atau bahan ajar baru.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <!-- Judul Materi -->
                    <div class="col-12">
                        <label for="title" class="form-label fw-semibold text-dark">Judul Materi Pembelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control form-control-lg fs-6 @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Bab 1 - Pengenalan Dasar Sistem Operasi" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

                    <!-- Target Kelas -->
                    <div class="col-md-6">
                        <label for="classroom_id" class="form-label fw-semibold text-dark">Target Kelas (Opsional)</label>
                        <select name="classroom_id" id="classroom_id" class="form-select @error('classroom_id') is-invalid @enderror">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Kosongkan jika materi dapat diakses oleh semua kelas.</small>
                        @error('classroom_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Pilihan Guru Pembuat (Hanya Tampil Jika Login Sebagai Admin / Super Admin) -->
                    @hasanyrole('super_admin|admin')
                        <div class="col-12">
                            <label for="teacher_id" class="form-label fw-semibold text-dark">Guru Pembuat Materi <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Guru Pembuat --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endhasanyrole

                    <!-- Deskripsi / Ringkasan Materi -->
                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold text-dark">Deskripsi Singkat / Ringkasan</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Tuliskan petunjuk atau pengantar mengenai materi ini...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Isi / Konten Teks Materi -->
                    <div class="col-12">
                        <label for="content" class="form-label fw-semibold text-dark">Isi Materi Pembelajaran (Teks/Artikel)</label>
                        <textarea name="content" id="content" rows="6" class="form-control @error('content') is-invalid @enderror" placeholder="Tuliskan isi materi lengkap di sini jika ada...">{{ old('content') }}</textarea>
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- File Lampiran -->
                    <div class="col-md-6">
                        <label for="file" class="form-label fw-semibold text-dark">File Dokumen / Modul (PDF, DOCX, PPTX, ZIP)</label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror">
                        <small class="text-muted d-block mt-1">Ukuran maksimal file: 10 MB</small>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Link Video Pembelajaran -->
                    <div class="col-md-6">
                        <label for="video_url" class="form-label fw-semibold text-dark">Link Video Pembelajaran (YouTube / Google Drive)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fab fa-youtube text-danger"></i>
                            </span>
                            <input type="url" name="video_url" id="video_url" class="form-control border-start-0 @error('video_url') is-invalid @enderror" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        @error('video_url') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- Status Dipublikasikan -->
                    <div class="col-12 mt-3">
                        <div class="form-check form-switch d-flex align-items-center gap-2 p-2 ps-0">
                            <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" name="is_published" id="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark cursor-pointer mb-0" for="is_published">
                                Publikasikan Materi Ini (Siswa dapat langsung melihat materi)
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('materials.index') }}" class="btn btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fas fa-save me-1"></i> Simpan Materi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection