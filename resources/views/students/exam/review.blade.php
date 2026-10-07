@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold">Review Jawaban: {{ $exam->title }}</h5>
            <span class="badge bg-light text-primary fs-6">Nilai: {{ number_format($session->score, 2) }}</span>
        </div>
        <div class="card-body p-4">
            @foreach($questions as $index => $question)
                @php
                    $ansRecord = $studentAnswers->get($question->id);
                    
                    // Decode jawaban siswa
                    $userAnsRaw = $ansRecord ? $ansRecord->answer : null;
                    if (is_string($userAnsRaw)) {
                        $userAnsRaw = json_decode($userAnsRaw, true);
                    }
                    $userAnswers = is_array($userAnsRaw) ? array_map('strtoupper', $userAnsRaw) : [strtoupper((string)$userAnsRaw)];

                    // Decode kunci jawaban
                    $correctAnsRaw = $question->correct_answer;
                    if (is_string($correctAnsRaw)) {
                        $correctAnsRaw = json_decode($correctAnsRaw, true);
                    }
                    $correctAnswers = is_array($correctAnsRaw) ? array_map('strtoupper', $correctAnsRaw) : [strtoupper((string)$correctAnsRaw)];
                    
                    // Ambil opsi dari kolom JSON options
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                @endphp

                <div class="p-3 mb-4 border rounded-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark mb-0">Soal No. {{ $index + 1 }}</h6>
                        @if($ansRecord && $ansRecord->is_correct)
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i> Benar</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i> Salah / Belum Dijawab</span>
                        @endif
                    </div>

                    <p class="text-dark mb-3">{!! $question->question_text !!}</p>

                    <div class="list-group">
                        @if(!empty($options))
                            @foreach($options as $opt)
                                @php
                                    $key = strtoupper($opt['key'] ?? '');
                                    $text = $opt['text'] ?? '';
                                    
                                    $isSelected = in_array($key, $userAnswers);
                                    $isCorrect = in_array($key, $correctAnswers);

                                    $bgClass = '';
                                    if ($isSelected && $isCorrect) {
                                        $bgClass = 'list-group-item-success';
                                    } elseif ($isSelected && !$isCorrect) {
                                        $bgClass = 'list-group-item-danger';
                                    } elseif ($isCorrect) {
                                        $bgClass = 'list-group-item-warning';
                                    }
                                @endphp

                                <div class="list-group-item {{ $bgClass }} d-flex justify-content-between align-items-center mb-1 rounded border">
                                    <div>
                                        <strong>{{ $key }}.</strong> {{ $text }}
                                    </div>
                                    <div>
                                        @if($isSelected && $isCorrect)
                                            <span class="badge bg-success">Jawaban Anda (Benar)</span>
                                        @elseif($isSelected && !$isCorrect)
                                            <span class="badge bg-danger">Jawaban Anda (Salah)</span>
                                        @elseif($isCorrect)
                                            <span class="badge bg-warning text-dark">Kunci Jawaban</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="mt-4">
                <a href="{{ route('student.exam.index') }}" class="btn btn-secondary fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Ujian
                </a>
            </div>
        </div>
    </div>
</div>
@endsection