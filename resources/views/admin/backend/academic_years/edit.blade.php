@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">Edit Tahun Pelajaran & Semester</h5>
                </div>
                <div class="card-body p-4">

                    {{-- Alert Error Validasi --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('settings.academic-years.update', $academicYear->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Input Tahun Pelajaran --}}
                        <div class="mb-3">
                            <label for="year" class="form-label font-weight-bold">
                                Tahun Pelajaran <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('year') is-invalid @enderror"
                                id="year"
                                name="year"
                                value="{{ old('year', $academicYear->year) }}"
                                placeholder="Contoh: 2025/2026"
                                required
                            >
                            <small class="text-muted">Gunakan format tahun: YYYY/YYYY (contoh: 2025/2026)</small>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Input Semester --}}
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">
                                Semester <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="semester"
                                        id="ganjil"
                                        value="ganjil"
                                        {{ old('semester', $academicYear->semester) == 'ganjil' ? 'checked' : '' }}
                                        required
                                    >
                                    <label class="form-check-label" for="ganjil">
                                        Ganjil
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="semester"
                                        id="genap"
                                        value="genap"
                                        {{ old('semester', $academicYear->semester) == 'genap' ? 'checked' : '' }}
                                        required
                                    >
                                    <label class="form-check-label" for="genap">
                                        Genap
                                    </label>
                                </div>
                            </div>
                            @error('semester')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Switch Semester Aktif --}}
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="is_active"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }}
                                >
                                <label class="form-check-label font-weight-bold" for="is_active">
                                    Jadikan Semester & Tahun Pelajaran Aktif
                                </label>
                            </div>
                            <small class="text-muted">Jika diaktifkan, periode lain yang sedang aktif akan otomatis dinonaktifkan.</small>
                        </div>

                        {{-- Tombol Batal & Simpan --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('settings.academic-years.index') }}" class="btn btn-secondary px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
