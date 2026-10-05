@extends('layouts.app')

@section('title', 'Import Soal Excel')

@section('content')
<div class="page-content">
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
                    <li class="breadcrumb-item active" aria-current="page">Import Soal</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 text-success fw-bold"><i class="fas fa-file-excel me-2"></i> Import Soal Excel</h4>
                        <p class="mb-0 text-muted small">Ujian: <strong>{{ $exam->title }}</strong></p>
                    </div>
                    <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="border-0 shadow-sm card">
                    <div class="card-body p-4">

                        <div class="alert alert-info mb-4">
                            <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-1"></i> Petunjuk Import Soal:</h6>
                            <ul class="mb-0 small ps-3">
                                <li>Format file menggunakan kolom: <code>No</code>, <code>Tipe Soal</code>, <code>Pertanyaan Soal</code>, <code>Opsi A</code>, <code>Opsi B</code>, <code>Opsi C</code>, <code>Opsi D</code>, <code>Opsi E</code>, <code>Kunci Jawaban</code>, <code>Bobot Nilai</code>.</li>
                                <li>Isi <strong>Tipe Soal</strong> dengan: <code>Pilihan Ganda</code>, <code>Multiple Choice</code>, atau <code>Essay</code>.</li>
                                <li>Untuk Multiple Choice dengan banyak kunci jawaban, pisahkan dengan koma (contoh: <code>A,B</code>).</li>
                            </ul>
                        </div>

                        <form action="{{ route('guru.questions.import', $exam->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-bold">Pilih File Excel / CSV <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control form-control-lg" accept=".xlsx,.xls,.csv" required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <a href="{{ route('guru.questions.export', $exam->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download me-1"></i> Download File Export / Template
                                </a>
                                <button type="submit" class="btn btn-success fw-bold px-4">
                                    <i class="fas fa-upload me-1"></i> Unggah & Import Soal
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
