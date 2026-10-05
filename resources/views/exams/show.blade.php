@extends('layouts.app')

@section('title', 'Detail Ujian - ' . $exam->title)

@section('content')
<div class="container-fluid py-3">
    <!-- Navigation Header & Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Daftar
            </a>
            <h3 class="fw-bold text-dark mb-0">{{ $exam->title }}</h3>
        </div>
        <div class="d-flex gap-2">
            <!-- Tombol Tambah Soal Manual -->
            <a href="{{ route('guru.questions.create', $exam->id) }}" class="btn btn-primary fw-semibold">
                <i class="fas fa-plus me-1"></i> Tambah Soal Manual
            </a>

            <!-- Tombol Import Excel -->
            <a href="{{ route('guru.questions.import.form', $exam->id) }}" class="btn btn-success fw-semibold">
                <i class="fas fa-file-excel me-1"></i> Import Excel
            </a>

            <!-- Tombol Export Excel -->
            <a href="{{ route('guru.questions.export', $exam->id) }}" class="btn btn-outline-success fw-semibold">
                <i class="fas fa-file-download me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi Sukses / Error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Informasi Ringkas Ujian -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark">Informasi Ujian</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Mata Pelajaran</small>
                            <span class="fw-semibold text-dark fs-6">{{ $exam->subject->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Guru Pengampu</small>
                            <span class="fw-semibold text-dark fs-6">{{ $exam->teacher->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Tipe Ujian</small>
                            <span class="badge bg-info-subtle text-info border border-info-subtle text-uppercase">
                                {{ str_replace('_', ' ', $exam->type) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Durasi</small>
                            <span class="fw-semibold text-dark fs-6">{{ $exam->duration_minutes }} Menit</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Jadwal Pelaksanaan</small>
                            <span class="fw-medium text-dark">
                                {{ \Carbon\Carbon::parse($exam->start_time)->format('d M Y, H:i') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('d M Y, H:i') }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Target Kelas</small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($exam->classrooms as $cls)
                                    <span class="badge bg-secondary-subtle text-secondary border">{{ $cls->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Ringkas -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark">Ringkasan Soal</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                    <div class="text-center mb-3">
                        <h1 class="display-4 fw-bold text-primary mb-0">{{ $exam->questions->count() }}</h1>
                        <span class="text-muted fw-medium">Total Soal Terdaftar</span>
                    </div>
                    <div class="text-center">
                        <h3 class="fw-bold text-success mb-0">{{ $exam->questions->sum('score_weight') }}</h3>
                        <span class="text-muted fw-medium">Total Bobot Nilai</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Soal Ujian -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">Daftar Soal</h5>
            <a href="{{ route('guru.questions.create', $exam->id) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                <i class="fas fa-plus me-1"></i> Tambah Soal
            </a>
        </div>
        <div class="card-body p-4">
            @forelse($exam->questions as $index => $q)
                <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-primary">Soal #{{ $index + 1 }}</span>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-secondary-subtle text-secondary me-1">
                                Tipe: {{ strtoupper($q->question_type ?? $q->type) }}
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-2">
                                Bobot: {{ $q->score_weight ?? $q->score }}
                            </span>

                            <!-- Tombol Copy / Duplikat Soal -->
                            <form action="{{ route('guru.questions.duplicate', $q->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menduplikasi soal ini?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-info py-0 px-2 fw-semibold" title="Duplikat Soal">
                                    <i class="fas fa-copy me-1"></i> Copy
                                </button>
                            </form>

                            <!-- Tombol Edit Soal -->
                            <a href="{{ route('guru.questions.edit', $q->id) }}" class="btn btn-sm btn-outline-warning py-0 px-2 fw-semibold" title="Edit Soal">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>

                            <!-- Tombol Hapus Soal -->
                            <form action="{{ route('guru.questions.destroy', $q->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini secara permanen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 fw-semibold" title="Hapus Soal">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    <p class="fs-6 text-dark mb-3">{!! nl2br(e($q->question_text)) !!}</p>

                    <!-- Handling Parsing Data Opsi & Kunci Jawaban -->
                    @php
                        $optionsData = is_string($q->options) ? json_decode($q->options, true) : $q->options;
                        $correctAnswer = is_string($q->correct_answer) ? json_decode($q->correct_answer, true) : $q->correct_answer;
                        if (!is_array($correctAnswer)) {
                            $correctAnswer = explode(',', (string)$q->correct_answer);
                        }
                        $qType = strtolower($q->question_type ?? $q->type ?? '');
                    @endphp

                    <!-- Opsi Jawaban (Pilihan Ganda / Multiple Choice) -->
                    @if(in_array($qType, ['single', 'multiple', 'pilihan_ganda', 'multiple_choice', 'pg', 'mc']) && !empty($optionsData))
                        <div class="row g-2">
                            @foreach($optionsData as $item)
                                @php
                                    $optionKey = is_array($item) ? ($item['key'] ?? '') : '';
                                    $optionText = is_array($item) ? ($item['text'] ?? '') : $item;
                                    $isCorrect = is_array($correctAnswer) && in_array($optionKey, $correctAnswer);
                                @endphp
                                <div class="col-md-6">
                                    <div class="p-2 border rounded-3 text-dark {{ $isCorrect ? 'bg-success text-white fw-medium' : 'bg-white' }}">
                                        <strong>{{ $optionKey }}.</strong> {{ $optionText }}
                                        @if($isCorrect)
                                            <span class="badge bg-light text-success float-end mt-1">Kunci Jawaban</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    <!-- Display Kunci Jawaban Essay -->
                    @elseif($qType === 'essay' && !empty($correctAnswer))
                        <div class="p-2 border rounded-3 bg-white text-dark">
                            <small class="text-muted d-block fw-bold mb-1">Pedoman / Kunci Jawaban Essay:</small>
                            <span>{{ is_array($correctAnswer) ? ($correctAnswer[0] ?? '-') : $correctAnswer }}</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <p class="mb-3">Belum ada soal yang ditambahkan pada ujian ini.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('guru.questions.create', $exam->id) }}" class="btn btn-sm btn-primary fw-semibold">
                            <i class="fas fa-plus me-1"></i> Tambah Soal Manual
                        </a>
                        <a href="{{ route('guru.questions.import.form', $exam->id) }}" class="btn btn-sm btn-success fw-semibold">
                            <i class="fas fa-file-excel me-1"></i> Import Excel
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
