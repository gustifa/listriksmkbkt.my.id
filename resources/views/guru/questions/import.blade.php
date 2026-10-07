@extends('layouts.app')

@section('title', 'Import Soal Excel')

@section('content')
<div class="container-fluid py-3 px-2 px-md-4">
    
    <!-- Header Page Responsif (Judul & Tombol Kembali Rapi) -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold text-success mb-1 fs-5 fs-md-4">
                <i class="fas fa-file-excel me-2"></i>Import Soal Excel
            </h4>
            <p class="text-muted small mb-0">
                Ujian: <strong>{{ $exam->title ?? 'Assesmen' }}</strong>
            </p>
        </div>
        <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-secondary btn-sm fw-semibold align-self-start align-self-sm-auto">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 p-md-4">
                    
                    <!-- Box Petunjuk Import -->
                    <div class="alert alert-info border-0 shadow-none mb-4 p-3 rounded-3" style="background-color: #e0f2fe; color: #0369a1;">
                        <h6 class="fw-bold mb-2 fs-6">
                            <i class="fas fa-info-circle me-2"></i>Petunjuk Import Soal:
                        </h6>
                        <ul class="mb-0 ps-3 small" style="line-height: 1.6;">
                            <li>Format file menggunakan kolom: <code class="bg-white px-1 py-0.5 rounded text-danger">No</code>, <code class="bg-white px-1 py-0.5 rounded text-danger">Tipe Soal</code>, <code class="bg-white px-1 py-0.5 rounded text-danger">Pertanyaan Soal</code>, <code class="bg-white px-1 py-0.5 rounded text-danger">Opsi A</code> - <code class="bg-white px-1 py-0.5 rounded text-danger">E</code>, <code class="bg-white px-1 py-0.5 rounded text-danger">Kunci Jawaban</code>, <code class="bg-white px-1 py-0.5 rounded text-danger">Bobot Nilai</code>.</li>
                            <li>Isi <strong>Tipe Soal</strong> dengan: <span class="badge bg-white text-dark border">Pilihan Ganda</span>, <span class="badge bg-white text-dark border">Multiple Choice</span>, atau <span class="badge bg-white text-dark border">Essay</span>.</li>
                            <li>Untuk Multiple Choice dengan banyak kunci jawaban, pisahkan dengan koma (contoh: <code class="bg-white px-1 rounded text-primary">A,B</code>).</li>
                        </ul>
                    </div>

                    <!-- Form Upload Excel -->
                    <form action="{{ route('guru.questions.import', $exam->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2">
                                Pilih File Excel / CSV <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="file" class="form-control form-control-md" accept=".xlsx, .xls, .csv" required>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                Format yang didukung: .xlsx, .xls, .csv (Maksimal 5MB)
                            </small>
                        </div>

                        <!-- Baris Tombol Aksi Responsif (Vertikal di HP, Horisontal di Desktop) -->
                        <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                            <!-- Download Template -->
                            <a href="{{ route('guru.questions.template.download') }}" class="btn btn-outline-primary fw-semibold py-2 px-3 flex-fill text-center">
                                <i class="fas fa-download me-1"></i> Download Template
                            </a>

                            <!-- Tombol Submit / Upload -->
                            <button type="submit" class="btn btn-success fw-bold py-2 px-4 flex-fill text-center">
                                <i class="fas fa-file-upload me-1"></i> Unggah & Import Soal
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection