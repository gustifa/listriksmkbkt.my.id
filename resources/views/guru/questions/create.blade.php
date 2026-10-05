@extends('layouts.app')

@section('title', 'Tambah Soal Manual')

@section('content')
<div class="page-content">
    <!-- Breadcrumb -->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Ujian</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('guru.exams.show', $exam->id) }}">
                            <i class="fas fa-file-alt me-2"></i>Detail Ujian
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Soal Manual</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-plus-circle me-2"></i> Tambah Soal Manual</h4>
                        <p class="mb-0 text-muted small">Ujian: <strong>{{ $exam->title }}</strong></p>
                    </div>
                    <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="border-0 shadow-sm card">
                    <div class="card-body p-4">

                        <form action="{{ route('guru.questions.store', $exam->id) }}" method="POST">
                            @csrf

                            <!-- PILIHAN TIPE SOAL -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Jenis Soal <span class="text-danger">*</span></label>
                                <select name="type" id="question_type_select" class="form-select form-select-lg" onchange="switchQuestionType(this.value)">
                                    <option value="pilihan_ganda" {{ old('type', $exam->type ?? '') == 'pilihan_ganda' ? 'selected' : '' }}>
                                        Pilihan Ganda (1 Kunci Jawaban Benar)
                                    </option>
                                    <option value="multiple_choice" {{ old('type', $exam->type ?? '') == 'multiple_choice' ? 'selected' : '' }}>
                                        Multiple Choice (Lebih Dari 1 Jawaban Benar)
                                    </option>
                                    <option value="essay" {{ old('type', $exam->type ?? '') == 'essay' ? 'selected' : '' }}>
                                        Essay (Uraian / Jawaban Teks)
                                    </option>
                                </select>
                            </div>

                            <!-- TEXT PERTANYAAN -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Pertanyaan Soal <span class="text-danger">*</span></label>
                                <textarea name="question_text" class="form-control" rows="4" placeholder="Tuliskan isi pertanyaan di sini..." required>{{ old('question_text') }}</textarea>
                            </div>

                            <!-- OPSI JAWABAN: PILIHAN GANDA -->
                            <div id="section_pilihan_ganda" class="mb-4 type-section">
                                <div class="alert alert-info py-2 small mb-3">
                                    <i class="fas fa-info-circle me-1"></i> Isikan opsi A sampai E dan pilih <strong>1 Kunci Jawaban</strong> yang benar pada tombol radio.
                                </div>

                                @foreach(['A', 'B', 'C', 'D', 'E'] as $key)
                                    <div class="mb-3 input-group">
                                        <div class="input-group-text bg-light">
                                            <input class="form-check-input mt-0 me-2"
                                                   type="radio"
                                                   name="correct_answer_single"
                                                   value="{{ $key }}"
                                                   id="radio_{{ $key }}"
                                                   {{ old('correct_answer_single', 'A') == $key ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-dark" for="radio_{{ $key }}">Opsi {{ $key }}</label>
                                        </div>
                                        <input type="text"
                                               name="options_pg[{{ $key }}]"
                                               class="form-control"
                                               placeholder="Isi opsi {{ $key }}"
                                               value="{{ old('options_pg.'.$key) }}">
                                    </div>
                                @endforeach
                            </div>

                            <!-- OPSI JAWABAN: MULTIPLE CHOICE -->
                            <div id="section_multiple_choice" class="mb-4 type-section d-none">
                                <div class="alert alert-warning py-2 small mb-3">
                                    <i class="fas fa-check-square me-1"></i> Beri centang pada <strong>1 atau lebih Kunci Jawaban</strong> yang dianggap benar.
                                </div>

                                @foreach(['A', 'B', 'C', 'D', 'E'] as $key)
                                    <div class="mb-3 input-group">
                                        <div class="input-group-text bg-light">
                                            <input class="form-check-input mt-0 me-2"
                                                   type="checkbox"
                                                   name="correct_answer_multi[]"
                                                   value="{{ $key }}"
                                                   id="check_{{ $key }}"
                                                   {{ is_array(old('correct_answer_multi')) && in_array($key, old('correct_answer_multi')) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-dark" for="check_{{ $key }}">Opsi {{ $key }}</label>
                                        </div>
                                        <input type="text"
                                               name="options_mc[{{ $key }}]"
                                               class="form-control"
                                               placeholder="Isi opsi {{ $key }}"
                                               value="{{ old('options_mc.'.$key) }}">
                                    </div>
                                @endforeach
                            </div>

                            <!-- OPSI JAWABAN: ESSAY -->
                            <div id="section_essay" class="mb-4 type-section d-none">
                                <label class="form-label fw-bold">Pedoman / Kunci Jawaban Essay <span class="text-danger">*</span></label>
                                <textarea name="essay_answer" class="form-control" rows="3" placeholder="Tuliskan acuan atau pedoman jawaban benar untuk soal essay...">{{ old('essay_answer') }}</textarea>
                            </div>

                            <!-- BOBOT NILAI -->
                            <div class="mb-4 col-md-4">
                                <label class="form-label fw-bold">Bobot Nilai <span class="text-danger">*</span></label>
                                <input type="number" name="score" class="form-control" min="0.1" step="0.5" value="{{ old('score', 2) }}" required>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary fw-bold px-4">
                                    <i class="fas fa-save me-1"></i> Simpan Soal
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function switchQuestionType(type) {
        document.querySelectorAll('.type-section').forEach(el => el.classList.add('d-none'));

        if (type === 'multiple_choice') {
            document.getElementById('section_multiple_choice').classList.remove('d-none');
        } else if (type === 'essay') {
            document.getElementById('section_essay').classList.remove('d-none');
        } else {
            document.getElementById('section_pilihan_ganda').classList.remove('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const currentType = document.getElementById('question_type_select').value;
        switchQuestionType(currentType);
    });
</script>
@endsection
