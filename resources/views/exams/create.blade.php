@extends('layouts.app')

@section('title', 'Buat Ujian Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 fw-bold">Tambah Ujian Baru</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('exams.store') }}" method="POST">
                    @csrf

                    <!-- Nama / Judul Ujian -->
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Nama Ujian / Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Penilaian Tengah Semester - Matematika" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <!-- Jenis Ujian -->
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label fw-bold">Tipe Ujian <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="pilihan_ganda" {{ old('type') == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda (Single Choice)</option>
                                <option value="multiple_choice" {{ old('type') == 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda Kompleks (Centang Banyak)</option>
                                <option value="essay" {{ old('type') == 'essay' ? 'selected' : '' }}>Essay</option>
                                <option value="campuran" {{ old('type') == 'campuran' ? 'selected' : '' }}>Campuran (PG + Essay)</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Mata Pelajaran -->
                        <div class="col-md-6 mb-3">
                            <label for="subject_id" class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Khusus Admin: Pilih Guru Pengampu -->
                    @if(Auth::user()->hasRole('admin'))
                        <div class="mb-3">
                            <label for="teacher_id" class="form-label fw-bold">Guru Pengampu <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <!-- Target Kelas (Multiple Checkbox/Select) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Target Kelas <span class="text-danger">*</span></label>
                        <div class="row border rounded p-3 bg-light mx-0">
                            @foreach($classrooms as $classroom)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}" id="class_{{ $classroom->id }}"
                                            {{ is_array(old('classroom_ids')) && in_array($classroom->id, old('classroom_ids')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="class_{{ $classroom->id }}">
                                            {{ $classroom->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('classroom_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <!-- Durasi (Menit) -->
                        <div class="col-md-4 mb-3">
                            <label for="duration_minutes" class="form-label fw-bold">Durasi Ujian (Menit) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_minutes" id="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes', 60) }}" min="1" required>
                            @error('duration_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Waktu Mulai -->
                        <div class="col-md-4 mb-3">
                            <label for="start_time" class="form-label fw-bold">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                            @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Waktu Selesai -->
                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label fw-bold">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                            @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Status Aktif -->
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label fw-bold" for="is_active">Aktifkan Ujian Sekarang</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan & Lanjut Tambah Soal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
