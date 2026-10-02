@extends('layouts.app')

@section('title', 'Kelola Ujian - ' . $exam->title)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="card-title">{{ $exam->title }}</h3>
                <p class="text-muted mb-1">Mata Pelajaran: <strong>{{ $exam->subject->name ?? '-' }}</strong> | Durasi: <strong>{{ $exam->duration_minutes }} Menit</strong></p>
                <hr>
                
                <h5 class="fw-bold">Unggah Soal via Excel</h5>
                <form action="{{ route('teacher.exam.import', $exam->id) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                    @csrf
                    <div class="col-auto">
                        <input type="file" name="file" class="form-control" required accept=".xlsx, .xls, .csv">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-success">Import Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Soal (Total: {{ $exam->questions->count() }})</div>
            <div class="card-body">
                @forelse($exam->questions as $index => $q)
                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between">
                            <h6><strong>Soal #{{ $index + 1 }}</strong> <span class="badge bg-info text-dark">{{ strtoupper($q->question_type) }}</span></h6>
                            <span class="badge bg-secondary">Bobot: {{ $q->score_weight }}</span>
                        </div>
                        <p class="mt-2">{{ $q->question_text }}</p>

                        @if($q->question_type !== 'essay' && !empty($q->options))
                            <div class="row">
                                @foreach($q->options as $opt)
                                    <div class="col-md-6 mb-1">
                                        <div class="p-2 border rounded {{ in_array($opt['key'], $q->correct_answer ?? []) ? 'bg-success text-white' : 'bg-white' }}">
                                            <strong>{{ $opt['key'] }}.</strong> {{ $opt['text'] }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted text-center py-4">Belum ada soal yang diunggah.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection