@extends('layouts.app')

@section('title', 'Tambah Soal Manual - ' . $exam->title)

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">

            <!-- HEADER RESPONSIF (Stack Vertikal di HP, Sebaris di Desktop) -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
                <div>
                    <h4 class="fw-bold text-primary mb-1 fs-5 fs-md-4">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Soal Manual
                    </h4>
                    <p class="text-muted small mb-0">
                        Ujian: <strong>{{ $exam->title }}</strong>
                    </p>
                </div>
                <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- CARD FORM SOAL -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 p-md-4">
                    
                    <form action="{{ route('guru.questions.store', $exam->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- JENIS SOAL -->
                        <div class="mb-3">
                            <label for="question_type" class="form-label fw-bold small text-secondary">
                                Jenis Soal <span class="text-danger">*</span>
                            </label>
                            <select name="question_type" id="question_type" class="form-select form-select-md shadow-none" required>
                                <option value="single">Pilihan Ganda (1 Kunci Jawaban Benar)</option>
                                <option value="multiple">Pilihan Ganda Kompleks (Bisa >1 Jawaban Benar)</option>
                                <option value="essay">Essay / Uraian</option>
                            </select>
                        </div>

                        <!-- PERTANYAAN SOAL -->
                        <div class="mb-3">
                            <label for="question_text" class="form-label fw-bold small text-secondary">
                                Pertanyaan Soal <span class="text-danger">*</span>
                            </label>
                            <textarea name="question_text" id="question_text" rows="4" class="form-control shadow-none" placeholder="Tuliskan isi pertanyaan di sini..." required></textarea>
                        </div>

                        <!-- BOBOT SOAL -->
                        <div class="mb-3">
                            <label for="score_weight" class="form-label fw-bold small text-secondary">
                                Bobot Nilai Soal
                            </label>
                            <input type="number" name="score_weight" id="score_weight" class="form-control shadow-none" value="10" min="1" required>
                        </div>

                        <hr class="my-4">

                        <!-- CONTAINER OPSI JAWABAN (PILGAN) -->
                        <div id="options-container">
                            <div class="alert alert-info py-2 px-3 small mb-3">
                                <i class="fas fa-info-circle me-1"></i> Isikan opsi A sampai E dan tandai <strong>Kunci Jawaban</strong> yang benar.
                            </div>

                            @foreach(['A', 'B', 'C', 'D', 'E'] as $key)
                            <div class="mb-3 border rounded p-2 p-md-3 bg-light">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="form-check m-0">
                                        <input class="form-check-input key-selector" type="radio" name="correct_answer[]" value="{{ $key }}" id="correct_{{ $key }}" {{ $key === 'A' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark small" for="correct_{{ $key }}">
                                            Kunci Jawaban Opsi {{ $key }}
                                        </label>
                                    </div>
                                </div>
                                <input type="text" name="options[{{ $key }}]" class="form-control form-control-md shadow-none bg-white" placeholder="Isi teks opsi {{ $key }}">
                            </div>
                            @endforeach
                        </div>

                        <!-- TOMBOL SIMPAN -->
                        <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                            <button type="submit" class="btn btn-primary fw-bold px-4 py-2 w-100 w-sm-auto">
                                <i class="fas fa-save me-1"></i> Simpan Soal
                            </button>
                            <button type="submit" name="save_and_more" value="1" class="btn btn-outline-primary fw-bold px-4 py-2 w-100 w-sm-auto">
                                <i class="fas fa-plus me-1"></i> Simpan & Tambah Lagi
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const typeSelect = document.getElementById('question_type');
        const optionsContainer = document.getElementById('options-container');

        typeSelect.addEventListener('change', function() {
            const val = this.value;
            const keySelectors = document.querySelectorAll('.key-selector');

            if (val === 'essay') {
                optionsContainer.style.display = 'none';
            } else {
                optionsContainer.style.display = 'block';
                keySelectors.forEach(el => {
                    el.type = (val === 'multiple') ? 'checkbox' : 'radio';
                });
            }
        });
    });
</script>
@endsection