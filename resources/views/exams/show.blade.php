@extends('layouts.app')

@section('title', 'Detail Ujian - ' . $exam->title)

@section('content')
<!-- Header & Navigasi -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary btn-sm mb-2">&larr; Kembali ke Daftar Ujian</a>
        <h4 class="fw-bold mb-0">{{ $exam->title }}</h4>
    </div>
    <div>
        <a href="{{ route('teacher.questions.import.form', $exam->id) }}" class="btn btn-success me-1">
            Import Excel
        </a>
        <a href="#" class="btn btn-warning">Edit Ujian</a>
    </div>
</div>

<!-- Informasi Utama Ujian -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Informasi Ujian</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Mata Pelajaran</small>
                        <strong>{{ $exam->subject->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Guru Pengampu</small>
                        <strong>{{ $exam->teacher->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Tipe Ujian</small>
                        <span class="badge bg-info text-dark">{{ strtoupper(str_replace('_', ' ', $exam->type)) }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Durasi</small>
                        <strong>{{ $exam->duration_minutes }} Menit</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Waktu Pelaksanaan</small>
                        <span>{{ \Carbon\Carbon::parse($exam->start_time)->format('d M Y H:i') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('d M Y H:i') }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Target Kelas</small>
                        @foreach($exam->classrooms as $cls)
                            <span class="badge bg-secondary me-1">{{ $cls->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Ringkasan Soal</div>
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="mb-3">
                    <h2 class="display-5 fw-bold text-primary mb-0">{{ $totalQuestions }}</h2>
                    <span class="text-muted">Total Soal</span>
                </div>
                <div>
                    <h3 class="fw-bold text-success mb-0">{{ $totalScoreWeight }}</h3>
                    <span class="text-muted">Total Bobot Nilai</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Soal -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="fw-bold mb-0">Daftar Soal</h5>
    </div>
    <div class="card-body">
        @forelse($exam->questions as $index => $q)
            <div class="border rounded p-3 mb-3 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">Soal #{{ $index + 1 }}</h6>
                    <div>
                        <span class="badge bg-secondary me-1">Tipe: {{ strtoupper($q->question_type) }}</span>
                        <span class="badge bg-primary">Bobot: {{ $q->score_weight }}</span>
                    </div>
                </div>

                <p class="mb-3 fs-6">{{ $q->question_text }}</p>

                <!-- Pilihan Jawaban jika bukan Essay -->
                @if($q->question_type !== 'essay' && !empty($q->options))
                    <div class="row">
                        @foreach($q->options as $opt)
                            @php
                                $isCorrect = is_array($q->correct_answer) && in_array($opt['key'], $q->correct_answer);
                            @endphp
                            <div class="col-md-6 mb-2">
                                <div class="p-2 border rounded {{ $isCorrect ? 'bg-success text-white' : 'bg-white text-dark' }}">
                                    <strong>{{ $opt['key'] }}.</strong> {{ $opt['text'] }}
                                    @if($isCorrect)
                                        <span class="badge bg-light text-success ms-2 float-end">Kunci</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <p class="mb-2">Belum ada soal untuk ujian ini.</p>
                <a href="{{ route('teacher.questions.import.form', $exam->id) }}" class="btn btn-sm btn-success">Import Soal Sekarang</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
