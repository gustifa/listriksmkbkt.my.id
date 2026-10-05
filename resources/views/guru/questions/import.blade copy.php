@extends('layouts.app')

@section('title', 'Import Soal Ujian - ' . $exam->title)

@section('content')
<div class="container-fluid py-3">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-secondary btn-sm me-3">
            &larr; Kembali ke Detail Ujian
        </a>
        <div>
            <h3 class="fw-bold text-dark mb-0">Import Soal Ujian</h3>
            <p class="text-muted small mb-0">Tambahkan soal secara massal menggunakan file Excel (.xlsx / .xls)</p>
        </div>
    </div>

    <!-- Alert Status -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Panel Kiri: Form Upload & Pilihan Jenis Soal -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark">Upload & Konfigurasi Import</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <form action="{{ route('guru.questions.import', $exam->id) }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf

                        <!-- Ringkasan Ujian Target -->
                        <div class="bg-light-subtle border rounded-3 p-3 mb-4">
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block">Ujian Target</small>
                                    <strong class="text-dark">{{ $exam->title }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Mata Pelajaran</small>
                                    <strong class="text-dark">{{ $exam->subject->name ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan Jenis Soal -->
                        <div class="mb-4">
                            <label for="question_type" class="form-label fw-semibold text-dark">
                                Pilih Jenis Soal Yang Ingin Di-import <span class="text-danger">*</span>
                            </label>
                            <select name="question_type" id="question_type" class="form-select form-select-lg fs-6 @error('question_type') is-invalid @enderror" onchange="updateGuideAndTemplate()" required>
                                <option value="pilihan_ganda" {{ old('question_type') == 'pilihan_ganda' ? 'selected' : '' }}>
                                    Pilihan Ganda (Single Choice)
                                </option>
                                <option value="multiple_choice" {{ old('question_type') == 'multiple_choice' ? 'selected' : '' }}>
                                    Pilihan Ganda Kompleks (Centang Banyak)
                                </option>
                                <option value="essay" {{ old('question_type') == 'essay' ? 'selected' : '' }}>
                                    Soal Essay / Uraian
                                </option>
                            </select>
                            <small class="text-muted mt-1 d-block">Format Excel yang harus diunggah menyesuaikan dengan jenis soal yang dipilih.</small>
                            @error('question_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Upload File Excel -->
                        <div class="mb-4">
                            <label for="excel_file" class="form-label fw-semibold text-dark">Pilih File Excel (.xlsx / .xls) <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="excel_file" class="form-control form-control-lg @error('file') is-invalid @enderror" accept=".xlsx, .xls" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-1 d-block">Ukuran maksimum file: 5 MB</small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-light border px-4">Batal</a>
                            <button type="submit" class="btn btn-success px-4 fw-semibold">
                                <i class="bi bi-upload me-1"></i> Mulai Import Soal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Petunjuk Format Excel Dinamis & Download Template -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark">Petunjuk Format Excel</h5>
                </div>
                <div class="card-body">
                    <div id="guide-content">
                        <!-- Konten petunjuk akan diisi oleh JavaScript berdasarkan pilihan jenis soal -->
                    </div>

                    <!-- Card Download Template -->
                    <div class="p-3 bg-light rounded-3 border mt-4">
                        <span class="fw-semibold text-dark d-block mb-1 small">Download Template Excel</span>
                        <p class="small text-muted mb-2">Unduh template Excel sesuai jenis soal yang telah Anda pilih:</p>
                        <a href="#" id="btn-download-template" class="btn btn-sm btn-outline-success w-100 fw-semibold">
                            <i class="bi bi-file-earmark-excel me-1"></i> Download Template <span id="template-type-text">Pilihan Ganda</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript untuk Mengubah Petunjuk & Link Template secara Dinamis -->
<script>
    const baseUrlTemplate = "{{ route('guru.questions.template.download') }}";

    function updateGuideAndTemplate() {
        const type = document.getElementById('question_type').value;
        const guideContainer = document.getElementById('guide-content');
        const btnDownload = document.getElementById('btn-download-template');
        const templateTypeText = document.getElementById('template-type-text');

        // Update URL download template dengan query parameter ?type=...
        btnDownload.href = baseUrlTemplate + "?type=" + type;

        if (type === 'pilihan_ganda') {
            templateTypeText.innerText = 'Pilihan Ganda';
            guideContainer.innerHTML = `
                <p class="small text-muted mb-3">Format kolom Excel untuk <strong>Pilihan Ganda (1 Jawaban Benar)</strong>:</p>
                <ol class="small text-dark ps-3 mb-0">
                    <li class="mb-2"><strong>Kolom A:</strong> Teks Pertanyaan / Soal.</li>
                    <li class="mb-2"><strong>Kolom B:</strong> Opsi A</li>
                    <li class="mb-2"><strong>Kolom C:</strong> Opsi B</li>
                    <li class="mb-2"><strong>Kolom D:</strong> Opsi C</li>
                    <li class="mb-2"><strong>Kolom E:</strong> Opsi D</li>
                    <li class="mb-2"><strong>Kolom F:</strong> Opsi E</li>
                    <li class="mb-2"><strong>Kolom G:</strong> Kunci Jawaban (Contoh: <code>A</code>, <code>B</code>, atau <code>C</code>).</li>
                    <li class="mb-2"><strong>Kolom H:</strong> Bobot Nilai (Contoh: <code>2</code>, <code>5</code>, dsb).</li>
                </ol>
            `;
        } else if (type === 'multiple_choice') {
            templateTypeText.innerText = 'PG Kompleks';
            guideContainer.innerHTML = `
                <p class="small text-muted mb-3">Format kolom Excel untuk <strong>Pilihan Ganda Kompleks (Bisa Centang Banyak)</strong>:</p>
                <ol class="small text-dark ps-3 mb-0">
                    <li class="mb-2"><strong>Kolom A:</strong> Teks Pertanyaan / Soal.</li>
                    <li class="mb-2"><strong>Kolom B:</strong> Opsi A</li>
                    <li class="mb-2"><strong>Kolom C:</strong> Opsi B</li>
                    <li class="mb-2"><strong>Kolom D:</strong> Opsi C</li>
                    <li class="mb-2"><strong>Kolom E:</strong> Opsi D</li>
                    <li class="mb-2"><strong>Kolom F:</strong> Opsi E</li>
                    <li class="mb-2"><strong>Kolom G:</strong> Kunci Jawaban Pisahkan Koma (Contoh: <code>A,C</code> atau <code>B,D,E</code>).</li>
                    <li class="mb-2"><strong>Kolom H:</strong> Bobot Nilai (Contoh: <code>5</code>, <code>10</code>, dsb).</li>
                </ol>
            `;
        } else if (type === 'essay') {
            templateTypeText.innerText = 'Essay';
            guideContainer.innerHTML = `
                <p class="small text-muted mb-3">Format kolom Excel untuk <strong>Soal Essay / Uraian</strong>:</p>
                <ol class="small text-dark ps-3 mb-0">
                    <li class="mb-2"><strong>Kolom A:</strong> Teks Pertanyaan / Soal Essay.</li>
                    <li class="mb-2"><strong>Kolom B:</strong> Pedoman Jawaban / Kunci Jawaban Singkat (Opsional).</li>
                    <li class="mb-2"><strong>Kolom C:</strong> Bobot Nilai Maksimal (Contoh: <code>10</code>, <code>20</code>, dsb).</li>
                </ol>
                <div class="alert alert-info py-2 px-3 small mt-3">
                    <i class="bi bi-info-circle me-1"></i> Kolom Opsi A-E tidak diperlukan untuk tipe Soal Essay.
                </div>
            `;
        }
    }

    // Jalankan fungsi saat pertama kali halaman dimuat
    document.addEventListener('DOMContentLoaded', function () {
        updateGuideAndTemplate();
    });
</script>
@endsection