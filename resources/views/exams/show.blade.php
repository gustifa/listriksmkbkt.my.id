@extends('layouts.app')

@section('title', 'Detail Ujian - ' . $exam->title)

@section('content')
<div class="container-fluid py-3">
    <!-- Navigation Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Daftar
            </a>
            <h3 class="fw-bold text-dark mb-0">{{ $exam->title }}</h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('guru.questions.import.form', $exam->id) }}" class="btn btn-success fw-semibold">
                Import Excel
            </a>
        </div>
    </div>

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
        </div>
        <div class="card-body p-4">
            @forelse($exam->questions as $index => $q)
                <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-primary">Soal #{{ $index + 1 }}</span>
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary me-1">Tipe: {{ strtoupper($q->question_type) }}</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Bobot: {{ $q->score_weight }}</span>
                        </div>
                    </div>

                    <p class="fs-6 text-dark mb-3">{{ $q->question_text }}</p>

                    <!-- Pilihan Jawaban jika ada -->
                    @if($q->question_type !== 'essay' && !empty($q->options))
                        <div class="row g-2">
                            @foreach($q->options as $opt)
                                @php
                                    $isCorrect = is_array($q->correct_answer) && in_array($opt['key'], $q->correct_answer);
                                @endphp
                                <div class="col-md-6">
                                    <div class="p-2 border rounded-3 text-dark {{ $isCorrect ? 'bg-success text-white fw-medium' : 'bg-white' }}">
                                        <strong>{{ $opt['key'] }}.</strong> {{ $opt['text'] }}
                                        @if($isCorrect)
                                            <span class="badge bg-light text-success float-end mt-1">Kunci Jawaban</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <p class="mb-2">Belum ada soal yang ditambahkan pada ujian ini.</p>
                    <a href="{{ route('guru.questions.import.form', $exam->id) }}" class="btn btn-sm btn-success fw-semibold">
                        Import Excel Sekarang
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection