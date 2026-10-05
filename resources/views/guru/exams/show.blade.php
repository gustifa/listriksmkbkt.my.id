@extends('layouts.app')

@section('title', 'Detail Ujian - ' . $exam->title)

@section('content')
<div class="container-fluid py-3">
    <!-- Navigation Header & Action Buttons -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <a href="{{ route('guru.exams.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Daftar
            </a>
            <h3 class="fw-bold text-dark mb-0">{{ $exam->title }}</h3>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <!-- Tombol Rekap Nilai Ujian -->
            <a href="{{ route('guru.exams.report', $exam->id) }}" class="btn btn-info text-white fw-semibold">
                <i class="fas fa-chart-bar me-1"></i> Rekap Nilai
            </a>

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
                                {{ str_replace('_', ' ', $exam->type ?? '-') }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Durasi</small>
                            <span class="fw-semibold text-dark fs-6">{{ $exam->duration_minutes ?? 0 }} Menit</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Jadwal Pelaksanaan</small>
                            <span class="fw-medium text-dark">
                                @if(!empty($exam->start_time) && !empty($exam->end_time))
                                    {{ \Carbon\Carbon::parse($exam->start_time)->format('d M Y, H:i') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('d M Y, H:i') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Target Kelas</small>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($exam->classrooms as$cls)
                                    <span class="badge bg-secondary-subtle text-secondary border">{{ $cls->name }}</span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
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
            <div class="d-flex align-items-center gap-3">
                <h5 class="fw-bold mb-0 text-dark">Daftar Soal</h5>

                @if($exam->questions->count() > 0)
                    <div class="form-check ms-2 mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        <label class="form-check-label fw-semibold text-secondary small" for="selectAllCheckbox">
                            Pilih Semua
                        </label>
                    </div>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Tombol Hapus Massal -->
                <button type="button" id="btnBulkDelete" class="btn btn-sm btn-danger fw-semibold d-none" onclick="submitBulkDelete()">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                </button>

                <a href="{{ route('guru.questions.create', $exam->id) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                    <i class="fas fa-plus me-1"></i> Tambah Soal
                </a>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Form Pembungkus Hapus Massal -->
            <form id="bulkDeleteForm" action="{{ route('guru.questions.bulkDestroy') }}" method="POST">
                @csrf
                @method('DELETE')

                @forelse($exam->questions as $index =>$q)
                    <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <!-- Checkbox Soal -->
                                <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" class="form-check-input question-checkbox" onchange="updateSelectedCount()">
                                <span class="fw-bold text-primary">Soal #{{ $index + 1 }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-secondary-subtle text-secondary me-1">
                                    Tipe: {{ strtoupper($q->question_type ?? $q->type ?? '-') }}
                                </span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-2">
                                    Bobot: {{ $q->score_weight ?? $q->score ?? 1 }}
                                </span>

                                <!-- Tombol Copy -->
                                <button type="button" class="btn btn-sm btn-outline-info py-0 px-2 fw-semibold" title="Duplikat Soal" onclick="duplicateSingleQuestion('{{ $q->id }}')">
                                    <i class="fas fa-copy me-1"></i> Copy
                                </button>

                                <!-- Tombol Edit -->
                                <a href="{{ route('guru.questions.edit', $q->id) }}" class="btn btn-sm btn-outline-warning py-0 px-2 fw-semibold" title="Edit Soal">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>

                                <!-- Tombol Hapus Single -->
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-semibold" title="Hapus Soal" onclick="deleteSingleQuestion('{{ $q->id }}')">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </div>
                        </div>

                        <p class="fs-6 text-dark mb-3 me-2 ms-4">{!! nl2br(e($q->question_text)) !!}</p>

                        <!-- Parsing Opsi & Kunci Jawaban -->
                        @php
                            $optionsData = is_string($q->options) ? json_decode($q->options, true) :$q->options;
                            $correctAnswer = is_string($q->correct_answer) ? json_decode($q->correct_answer, true) :$q->correct_answer;
                            if (!is_array($correctAnswer)) {
                                $correctAnswer = explode(',', (string)$q->correct_answer);
                            }
                            $qType = strtolower($q->question_type ?? $q->type ?? '');
                        @endphp

                        @if(in_array($qType, ['single', 'multiple', 'pilihan_ganda', 'multiple_choice', 'pg', 'mc']) && !empty($optionsData))
                            <div class="row g-2 ms-3">
                                @foreach($optionsData as$item)
                                    @php
                                        $optionKey = is_array($item) ? ($item['key'] ?? '') : '';
                                        $optionText = is_array($item) ? ($item['text'] ?? '') :$item;
                                        $isCorrect = is_array($correctAnswer) && in_array($optionKey,$correctAnswer);
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="p-2 border rounded-3 text-dark {{ $isCorrect ? 'bg-success text-white fw-medium' : 'bg-white' }}">
                                            <strong>{{ $optionKey }}.</strong> {{$optionText }}
                                            @if($isCorrect)
                                                <span class="badge bg-light text-success float-end mt-1">Kunci Jawaban</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($qType === 'essay' && !empty($correctAnswer))
                            <div class="p-2 border rounded-3 bg-white text-dark ms-3">
                                <small class="text-muted d-block fw-bold mb-1">Pedoman / Kunci Jawaban Essay:</small>
                                <span>{{ is_array($correctAnswer) ? ($correctAnswer[0] ?? '-') :$correctAnswer }}</span>
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
            </form>
        </div>
    </div>
</div>

<!-- Hidden Form Aksi Tunggal (Copy & Hapus) -->
<form id="singleActionForm" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="_method" id="singleActionMethod" value="POST">
</form>

<script>
    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.question-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selectedCheckboxes = document.querySelectorAll('.question-checkbox:checked');
        const count = selectedCheckboxes.length;

        const btnBulk = document.getElementById('btnBulkDelete');
        const countSpan = document.getElementById('selectedCount');
        const selectAllCb = document.getElementById('selectAllCheckbox');
        const totalCheckboxes = document.querySelectorAll('.question-checkbox').length;

        if (countSpan) countSpan.innerText = count;

        if (btnBulk) {
            if (count > 0) {
                btnBulk.classList.remove('d-none');
            } else {
                btnBulk.classList.add('d-none');
            }
        }

        if (selectAllCb) {
            selectAllCb.checked = (count === totalCheckboxes && totalCheckboxes > 0);
        }
    }

    function submitBulkDelete() {
        const count = document.querySelectorAll('.question-checkbox:checked').length;
        if (count === 0) {
            alert('Pilih minimal 1 soal yang ingin dihapus.');
            return;
        }
        if (confirm(`Apakah Anda yakin ingin menghapus ${count} soal yang dipilih secara permanen?`)) {
            document.getElementById('bulkDeleteForm').submit();
        }
    }

    function duplicateSingleQuestion(questionId) {
        if (confirm('Apakah Anda yakin ingin menduplikasi soal ini?')) {
            const form = document.getElementById('singleActionForm');
            form.action = `/guru/questions/${questionId}/duplicate`;
            document.getElementById('singleActionMethod').value = 'POST';
            form.submit();
        }
    }

    function deleteSingleQuestion(questionId) {
        if (confirm('Apakah Anda yakin ingin menghapus soal ini secara permanen?')) {
            const form = document.getElementById('singleActionForm');
            form.action = `/guru/questions/${questionId}`;
            document.getElementById('singleActionMethod').value = 'DELETE';
            form.submit();
        }
    }
</script>
@endsection
