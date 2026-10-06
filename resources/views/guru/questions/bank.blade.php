@extends('layouts.app')

@section('title', 'Bank Soal - Ambil Soal')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Ujian
            </a>
            <h3 class="fw-bold text-dark mb-0">Bank Soal (Gunakan Soal yang Ada)</h3>
            <p class="text-muted small mb-0">Pilih soal dari guru lain atau ujian lain untuk dimasukkan ke <strong>{{ $exam->title }}</strong></p>
        </div>
    </div>

    <!-- Form Pencarian -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('guru.questions.bank', $exam->id) }}" method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan teks soal..." value="{{ $search ?? '' }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="fas fa-search me-1"></i> Cari Soal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form Salin Soal Massal -->
    <form action="{{ route('guru.questions.copy_bank', $exam->id) }}" method="POST">
        @csrf
        
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                    <label class="form-check-label fw-bold text-dark" for="selectAll">Pilih Semua Soal</label>
                </div>
                <button type="submit" class="btn btn-success fw-semibold">
                    <i class="fas fa-copy me-1"></i> Salin Soal Terpilih ke Ujian Ini
                </button>
            </div>
            
            <div class="card-body p-4">
                @forelse($questions as $q)
                    <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="form-check">
                                <input class="form-check-input q-checkbox" type="checkbox" name="question_ids[]" value="{{ $q->id }}" id="q_{{ $q->id }}">
                                <label class="form-check-label fw-bold text-primary" for="q_{{ $q->id }}">
                                    Mapel: {{ $q->exam->subject->name ?? '-' }} | Dibuat Oleh: {{ $q->exam->teacher->name ?? '-' }}
                                </label>
                            </div>
                            <span class="badge bg-info-subtle text-info border">
                                Tipe: {{ strtoupper($q->type ?? $q->question_type ?? '-') }}
                            </span>
                        </div>
                        <p class="fs-6 text-dark mb-2 ms-4">{!! nl2br(e($q->question_text)) !!}</p>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
                        <p class="mb-0">Tidak ada soal yang ditemukan di bank soal.</p>
                    </div>
                @endforelse

                <div class="mt-3">
                    {{ $questions->links() }}
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.q-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }
</script>
@endsection