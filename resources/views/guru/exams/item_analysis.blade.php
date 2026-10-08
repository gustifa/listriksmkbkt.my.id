@extends('layouts.app')

@section('title', 'Analisis Butir Soal - ' . $exam->title)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-secondary btn-sm mb-2">&larr; Kembali ke Detail Ujian</a>
            <h3 class="fw-bold text-dark mb-0">Analisis Butir Soal (Standar Ilmiah)</h3>
            <p class="text-muted small mb-0">Ujian: <strong>{{ $exam->title }}</strong> | Sampel Siswa: <strong>{{ $totalStudents }} Siswa</strong></p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">No</th>
                            <th>Butir Soal</th>
                            <th class="text-center">Benar / Salah</th>
                            <th class="text-center">Indeks Kesukaran (P)</th>
                            <th class="text-center">Kategori Kesukaran</th>
                            <th class="text-center">Daya Beda (D)</th>
                            <th class="text-center pe-3">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analysisResult as $item)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $item['no'] }}</td>
                            <td>{!! Str::limit(strip_tags($item['question_text']), 60) !!}</td>
                            <td class="text-center">
                                <span class="text-success fw-bold">{{ $item['correct_count'] }}</span> / 
                                <span class="text-danger fw-bold">{{ $item['wrong_count'] }}</span>
                            </td>
                            <td class="text-center fw-bold">{{ $item['facility_value'] }}</td>
                            <td class="text-center">
                                <span class="badge {{ $item['difficulty_badge'] }} px-3 py-2">{{ $item['difficulty_category'] }}</span>
                            </td>
                            <td class="text-center fw-bold">{{ $item['discrimination_index'] }}</td>
                            <td class="text-center pe-3">
                                <small class="fw-semibold">{{ $item['discrimination_category'] }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection