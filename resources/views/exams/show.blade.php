@extends('layouts.app')

@section('title', 'Detail Ujian - ' . $exam->title)

@section('content')
<div class="container-fluid py-3 px-2 px-md-3">
    <!-- Navigation Header & Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Daftar
            </a>
            <div class="d-flex align-items-center gap-2">
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-md-3">{{ $exam->title }}</h3>
                @if($exam->is_team_teaching || (isset($exam->collaborators) && $exam->collaborators->count() > 0))
                    <span class="badge bg-info text-dark fw-bold">
                        <i class="fas fa-users me-1"></i> Team Teaching
                    </span>
                @endif
                <!-- Badge Status Anti-Kecurangan -->
                @if($exam->enable_anti_cheat ?? true)
                    <span class="badge bg-danger text-white fw-bold">
                        <i class="fas fa-shield-alt me-1"></i> Anti-Cheat Aktif
                    </span>
                @else
                    <span class="badge bg-secondary text-white fw-bold">
                        <i class="fas fa-shield-virus me-1"></i> Anti-Cheat Nonaktif
                    </span>
                @endif
            </div>
            <!-- Badge Status Visibilitas Nilai (Dapat diklik) -->

        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
            <!-- Tombol Pengaturan Visibilitas Nilai -->
            <button type="button" class="btn btn-outline-primary fw-semibold btn-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modalToggleScore">
                <i class="fas fa-sliders-h me-1"></i> Pengaturan Nilai
            </button>
            <a href="{{ route('guru.exams.report', $exam->id) }}" class="btn btn-info text-white fw-semibold btn-sm flex-fill flex-md-grow-0">
                <i class="fas fa-chart-bar me-1"></i> Rekap Nilai
            </a>
            <a href="{{ route('guru.exams.unsubmitted', $exam->id) }}" class="btn btn-warning text-dark fw-semibold btn-sm flex-fill flex-md-grow-0">
                <i class="fas fa-user-clock me-1"></i> Belum Ujian
            </a>
            <a href="{{ route('guru.questions.bank', $exam->id) }}" class="btn btn-warning text-dark fw-semibold btn-sm flex-fill flex-md-grow-0">
                <i class="fas fa-database me-1"></i> Bank Soal
            </a>
            <a href="{{ route('guru.questions.create', $exam->id) }}" class="btn btn-primary fw-semibold btn-sm flex-fill flex-md-grow-0">
                <i class="fas fa-plus me-1"></i> Tambah Soal
            </a>
            <a href="{{ route('guru.questions.import.form', $exam->id) }}" class="btn btn-success fw-semibold btn-sm flex-fill flex-md-grow-0">
                <i class="fas fa-file-excel me-1"></i> Import
            </a>
            <a href="{{ route('guru.questions.export', $exam->id) }}" class="btn btn-outline-success fw-semibold btn-sm flex-fill flex-md-grow-0">
                <i class="fas fa-file-download me-1"></i> Export
            </a>
            
        </div>
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

    <!-- CARD KELOLA TEAM TEACHING -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-users text-primary me-2"></i>Team Teaching / Guru Kolaborator
            </h6>
            @if(Auth::user()->hasRole('admin') || $exam->teacher_id === optional(Auth::user()->teacher)->id)
                <button class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalTeamTeaching">
                    <i class="fas fa-user-plus me-1"></i> Kelola Team Teaching
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2">
                
                <span class="badge bg-primary px-3 py-2">
                    <i class="fas fa-crown me-1 text-warning"></i> {{ $exam->teacher->name ?? 'Admin' }} (Pembuat Utama)
                </span>

                @forelse($exam->collaborators ?? [] as $collaborator)
                    <span class="badge bg-secondary px-3 py-2">
                        <i class="fas fa-user-check me-1"></i> {{ $collaborator->name }}
                    </span>
                @empty
                    <small class="text-muted ms-2">Belum ada guru kolaborator yang ditambahkan.</small>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Ringkasan & Informasi Ujian -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark fs-6 fs-md-5">Informasi Ujian</h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="p-3 bg-light border rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                        <div>
                            <small class="text-muted d-block fw-semibold">Token Ujian Saat Ini:</small>
                            <span class="fs-4 fw-bold text-primary tracking-wider">
                                {{ $exam->token ?? 'BELUM SET' }}
                            </span>
                        </div>
                        <form action="{{ route('guru.exams.generate-token', $exam->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning fw-bold text-dark btn-sm w-100 w-sm-auto">
                                <i class="fas fa-sync-alt me-1"></i> Generate Token
                            </button>
                        </form>
                    </div>

                    <div class="row g-3">
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block mb-1">Mata Pelajaran</small>
                            <span class="fw-semibold text-dark fs-6">{{ $exam->subject->name ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block mb-1">Guru Pengampu Utama</small>
                            <span class="fw-semibold text-dark fs-6">{{ $exam->teacher->name ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block mb-1">Tipe Ujian</small>
                            <span class="badge bg-info-subtle text-info border border-info-subtle text-uppercase">
                                {{ str_replace('_', ' ', $exam->type ?? '-') }}
                            </span>
                        </div>
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block mb-1">Durasi</small>
                            <span class="fw-semibold text-dark fs-6">{{ $exam->duration_minutes ?? 0 }} Menit</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block mb-1">Tampilkan Nilai Akhir</small>
                            <span class="fw-semibold {{ ($exam->show_score ?? true) ? 'text-success' : 'text-danger' }}">
                                <i class="fas {{ ($exam->show_score ?? true) ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i>
                                {{ ($exam->show_score ?? true) ? 'Ditampilkan' : 'Disembunyikan' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark fs-6 fs-md-5">Ringkasan Soal</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBulkWeight">
                        <i class="fas fa-balance-scale me-1"></i> Atur Bobot
                    </button>
                </div>
                <div class="card-body d-flex flex-row flex-lg-column justify-content-around justify-content-lg-center align-items-center py-3 py-lg-4">
                    <div class="text-center">
                        <h2 class="fw-bold text-primary mb-0 display-6 display-lg-4">{{ $totalQuestions ?? ($exam->questions ? $exam->questions->count() : 0) }}</h2>
                        <span class="text-muted small fw-medium">Total Soal</span>
                    </div>
                    <div class="text-center mt-lg-3">
                        <h2 class="fw-bold text-success mb-0 fs-3 fs-lg-2">{{ $totalScoreWeight ?? ($exam->questions ? $exam->questions->sum('score_weight') : 0) }}</h2>
                        <span class="text-muted small fw-medium">Total Bobot</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Soal Ujian -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark fs-6 fs-md-5">Daftar Soal</h5>
                @if($exam->questions &&$exam->questions->count() > 0)
                    <div class="form-check mb-0 ms-2">
                        <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        <label class="form-check-label fw-semibold text-secondary small" for="selectAllCheckbox">
                            Pilih Semua
                        </label>
                    </div>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" id="btnBulkDelete" class="btn btn-sm btn-danger fw-semibold d-none" onclick="submitBulkDelete()">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                </button>
                <a href="{{ route('guru.questions.create', $exam->id) }}" class="btn btn-sm btn-primary fw-semibold">
                    <i class="fas fa-plus me-1"></i> Tambah Soal
                </a>
            </div>
        </div>

        <div class="card-body p-2 p-md-4">
            <form id="bulkDeleteForm" action="{{ route('guru.questions.bulkDestroy') }}" method="POST">
                @csrf
                @method('DELETE')

                @forelse($exam->questions as $index =>$q)
                    <div class="border rounded-3 p-2 p-md-3 mb-3 bg-light-subtle shadow-sm overflow-hidden">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" class="form-check-input question-checkbox" onchange="updateSelectedCount()">
                                <span class="fw-bold text-primary fs-6">Soal #{{ $index + 1 }}</span>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    {{ strtoupper($q->question_type ?? $q->type ?? '-') }}
                                </span>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-1 w-100 w-sm-auto justify-content-start justify-content-sm-end">
                                <form action="{{ route('guru.questions.update-weight', $q->id) }}" method="POST" class="d-inline-flex align-items-center gap-1 me-1">
                                    @csrf
                                    @method('PATCH')
                                    <span class="badge bg-primary-subtle text-primary border">Bobot:</span>
                                    <input type="number" step="0.1" name="score_weight" value="{{ $q->score_weight ?? $q->score ?? 1 }}" class="form-control form-control-sm text-center fw-bold px-1 py-0" style="width: 50px; height: 26px;" onchange="this.form.submit()">
                                </form>

                                <button type="button" class="btn btn-sm btn-outline-info py-0 px-2 fw-semibold" title="Duplikat" onclick="duplicateSingleQuestion('{{ $q->id }}')">
                                    <i class="fas fa-copy"></i> <span class="d-none d-md-inline">Copy</span>
                                </button>
                                <a href="{{ route('guru.questions.edit', $q->id) }}" class="btn btn-sm btn-outline-warning py-0 px-2 fw-semibold" title="Edit">
                                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-semibold" title="Hapus" onclick="deleteSingleQuestion('{{ $q->id }}')">
                                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Hapus</span>
                                </button>
                            </div>
                        </div>

                        <div class="text-dark mb-3 px-1" style="word-break: break-word;">
                            {!! nl2br(e($q->question_text)) !!}
                        </div>

                        @php
                            $optionsData = is_string($q->options) ? json_decode($q->options, true) :$q->options;
                            $correctAnswer = is_string($q->correct_answer) ? json_decode($q->correct_answer, true) :$q->correct_answer;
                            if (!is_array($correctAnswer)) {
                                $correctAnswer = explode(',', (string)$q->correct_answer);
                            }
                            $qType = strtolower($q->question_type ?? $q->type ?? '');
                            $optionKeys = ['A', 'B', 'C', 'D', 'E'];
                        @endphp

                        @if(in_array($qType, ['single', 'multiple', 'pilihan_ganda', 'multiple_choice', 'pg', 'mc']) && !empty($optionsData))
                            <div class="row g-2 mb-3">
                                @foreach($optionsData as $item)
                                    @php
                                        $optionKey = is_array($item) ? ($item['key'] ?? '') : '';
                                        $optionText = is_array($item) ? ($item['text'] ?? '') :$item;
                                        $isCorrect = is_array($correctAnswer) && in_array($optionKey,$correctAnswer);
                                    @endphp
                                    <div class="col-12 col-md-6">
                                        <div class="p-2 border rounded-3 text-dark small {{ $isCorrect ? 'bg-success text-white fw-medium' : 'bg-white' }}" style="word-break: break-word;">
                                            <strong>{{ $optionKey }}.</strong> {{$optionText }}
                                            @if($isCorrect)
                                                <span class="badge bg-light text-success float-end mt-1">Kunci</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="border-top pt-2 mt-2">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-2">
                                <small class="fw-bold text-dark mb-0">
                                    <i class="fas fa-chart-pie me-1 text-primary"></i> Rekap Jawaban
                                </small>
                                @php
                                    $totalAns =$q->recap['total_answered'] ?? 0;
                                    $correctAns =$q->recap['correct_count'] ?? 0;
                                    $percentCorrect =$totalAns > 0 ? round(($correctAns / $totalAns) * 100, 1) : 0;
                                @endphp
                                <span class="badge {{ $percentCorrect >= 70 ? 'bg-success' : ($percentCorrect >= 40 ? 'bg-warning text-dark' : 'bg-danger') }}" style="font-size: 11px;">
                                    Benar: {{ $percentCorrect }}% ({{ $correctAns }}/{{$totalAns }})
                                </span>
                            </div>

                            <div class="row g-1 text-center">
                                @foreach($optionKeys as $optKey)
                                    @php
                                        $isKey = is_array($correctAnswer) && in_array($optKey, $correctAnswer);$countChosen = $q->recap[$optKey] ?? 0;
                                    @endphp
                                    <div class="col">
                                        <div class="p-1 rounded border option-box {{ $isKey ? 'border-success bg-success-subtle fw-bold' : 'bg-white' }}"
                                             style="cursor: pointer;"
                                             onclick="showStudentList('{{ $q->id }}', '{{$optKey }}')">
                                            <small class="text-muted d-block fw-semibold" style="font-size: 11px;">{{ $optKey }}</small>
                                            <span class="fw-bold text-dark small">{{ $countChosen }}</span>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="col">
                                    <div class="p-1 rounded border bg-white option-box"
                                         style="cursor: pointer;"
                                         onclick="showStudentList('{{ $q->id }}', 'KOSONG')">
                                        <small class="text-muted d-block fw-semibold" style="font-size: 11px;">N/A</small>
                                        <span class="fw-bold text-danger small">{{ $q->recap['kosong'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <p class="mb-3">Belum ada soal yang ditambahkan pada ujian ini.</p>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="{{ route('guru.questions.bank', $exam->id) }}" class="btn btn-sm btn-warning text-dark fw-semibold">
                                <i class="fas fa-database me-1"></i> Bank Soal
                            </a>
                            <a href="{{ route('guru.questions.create', $exam->id) }}" class="btn btn-sm btn-primary fw-semibold">
                                <i class="fas fa-plus me-1"></i> Tambah Soal
                            </a>
                        </div>
                    </div>
                @endforelse
            </form>
        </div>
    </div>
</div>

<!-- MODAL TAMPILKAN / SEMBUNYIKAN NILAI -->
<div class="modal fade" id="modalToggleScore" tabindex="-1" aria-labelledby="modalToggleScoreLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold" id="modalToggleScoreLabel">
                    <i class="fas fa-eye me-2"></i> Pengaturan Visibilitas Nilai Akhir
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- FORM MENGGUNAKAN ROUTE KHUSUS TOGGLE SCORE -->
            <form action="{{ route('guru.exams.toggle-score', $exam->id) }}" method="POST">
                @csrf
                <div class="modal-body p-3 p-md-4">
                    <p class="small text-muted mb-3">
                        Pilih apakah siswa dapat melihat nilai akhir mereka secara langsung setelah menyelesaikan ujian.
                    </p>

                    <div class="p-3 border rounded-3 bg-light mb-3">
                        <div class="form-check form-switch d-flex align-items-center gap-2 ps-0">
                            <input class="form-check-input ms-0 me-2" style="width: 2.8em; height: 1.5em;" type="checkbox" role="switch" id="modal_show_score" name="show_score" value="1" {{ ($exam->show_score ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark cursor-pointer mb-0 fs-6" for="modal_show_score">
                                Tampilkan Nilai Akhir ke Siswa
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Jika dinonaktifkan, siswa hanya akan melihat pesan "Ujian Telah Berhasil Diselesaikan" tanpa menampilkan angka nilai.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL KELOLA TEAM TEACHING (SAFE BLADE SYNTAX) -->
@if(Auth::user()->hasRole('admin') || $exam->teacher_id === optional(Auth::user()->teacher)->id)
<div class="modal fade" id="modalTeamTeaching" tabindex="-1" aria-labelledby="modalTeamTeachingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold" id="modalTeamTeachingLabel">
                    <i class="fas fa-users-cog me-2"></i> Pilih Guru Kolaborator (Team Teaching)
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('guru.exams.team_teaching.update', $exam->id) }}" method="POST">
                @csrf
                <div class="modal-body p-3 p-md-4">
                    <p class="small text-muted mb-3">
                        Guru yang dipilih sebagai <strong>Team Teaching</strong> dapat membantu menambah, mengedit, mengimpor soal, serta melihat rekapitulasi nilai ujian ini.
                    </p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Pilih Guru Kolaborator:</label>
                        <div class="border rounded p-3 bg-light" style="max-height: 220px; overflow-y: auto;">
                            @forelse($availableTeachers ?? [] as $teacher)
                                @php
                                    $isChecked = false;
                                    if (isset($exam->collaborators) && is_iterable($exam->collaborators)) {$isChecked = collect($exam->collaborators)->contains('id',$teacher->id);
                                    }
                                @endphp
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="collaborator_ids[]" 
                                           value="{{ $teacher->id }}" 
                                           id="teacher_{{ $teacher->id }}"
                                           @checked($isChecked)>
                                    <label class="form-check-label small fw-semibold" for="teacher_{{ $teacher->id }}">
                                        {{ $teacher->name }} <span class="text-muted">({{ $teacher->nip ?? 'Guru' }})</span>
                                    </label>
                                </div>
                            @empty
                                <small class="text-muted d-block text-center py-2">Tidak ada data guru lain tersedia.</small>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Atur Bobot Massal -->
<div class="modal fade" id="modalBulkWeight" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('guru.exams.update-bulk-weight', $exam->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark fs-6">Atur Bobot Soal Massal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div class="card p-3 mb-3 border bg-light-subtle">
                        <label class="form-label fw-bold text-dark mb-1">Target Total Nilai</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="target_total_score" class="form-control" placeholder="Contoh: 100">
                            <span class="input-group-text">Poin</span>
                        </div>
                    </div>
                    <div class="text-center my-2 text-muted fw-bold">--- ATAU ---</div>
                    <div class="card p-3 border bg-light-subtle">
                        <label class="form-label fw-bold text-dark mb-1">Bobot Rata Per Soal</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="weight_per_question" class="form-control" placeholder="Contoh: 10">
                            <span class="input-group-text">Poin/Soal</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Popup Daftar Siswa -->
<div class="modal fade" id="modalStudentAnswers" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2">
                <h6 class="modal-title fw-bold text-dark" id="modalStudentAnswersLabel">
                    <i class="fas fa-users text-primary me-2"></i>Siswa - <span id="modalOptionTitle" class="text-primary">Opsi</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="loadingStudentList" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <p class="small text-muted mt-2 mb-0">Memuat data...</p>
                </div>
                <ul class="list-group list-group-flush d-none" id="studentListContainer"></ul>
                <div id="emptyStudentList" class="text-center py-4 d-none">
                    <p class="text-muted small mb-0">Tidak ada siswa memilih opsi ini.</p>
                </div>
            </div>
            <div class="modal-footer bg-light py-1">
                <button type="button" class="btn btn-secondary btn-sm fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<form id="singleActionForm" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="_method" id="singleActionMethod" value="POST">
</form>

<script>
    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.question-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selectedCheckboxes = document.querySelectorAll('.question-checkbox:checked');
        const count = selectedCheckboxes.length;
        const btnBulk = document.getElementById('btnBulkDelete');
        const countSpan = document.getElementById('selectedCount');
        if (countSpan) countSpan.innerText = count;
        if (btnBulk) {
            if (count > 0) btnBulk.classList.remove('d-none');
            else btnBulk.classList.add('d-none');
        }
    }

    function submitBulkDelete() {
        const count = document.querySelectorAll('.question-checkbox:checked').length;
        if (count === 0) return alert('Pilih minimal 1 soal.');
        if (confirm(`Hapus ${count} soal terpilih?`)) {
            document.getElementById('bulkDeleteForm').submit();
        }
    }

    function duplicateSingleQuestion(questionId) {
        if (confirm('Duplikasi soal ini?')) {
            const form = document.getElementById('singleActionForm');
            form.action = `/questions/${questionId}/duplicate`;
            document.getElementById('singleActionMethod').value = 'POST';
            form.submit();
        }
    }

    function deleteSingleQuestion(questionId) {
        if (confirm('Hapus soal ini?')) {
            const form = document.getElementById('singleActionForm');
            form.action = `/questions/${questionId}`;
            document.getElementById('singleActionMethod').value = 'DELETE';
            form.submit();
        }
    }

    function showStudentList(questionId, optionKey) {
        const modalElement = new bootstrap.Modal(document.getElementById('modalStudentAnswers'));
        document.getElementById('modalOptionTitle').innerText = optionKey === 'KOSONG' ? 'N/A (Kosong)' : `Opsi ${optionKey}`;

        const loading = document.getElementById('loadingStudentList');
        const container = document.getElementById('studentListContainer');
        const emptyState = document.getElementById('emptyStudentList');

        loading.classList.remove('d-none');
        container.classList.add('d-none');
        emptyState.classList.add('d-none');
        container.innerHTML = '';

        modalElement.show();

        fetch(`/questions/${questionId}/students?option=${optionKey}`)
            .then(res => res.json())
            .then(data => {
                loading.classList.add('d-none');
                if (data.students && data.students.length > 0) {
                    data.students.forEach((student, idx) => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3 small';
                        li.innerHTML = `<span>${idx + 1}. ${student.name}</span><span class="badge bg-secondary-subtle text-secondary border">${student.class}</span>`;
                        container.appendChild(li);
                    });
                    container.classList.remove('d-none');
                } else {
                    emptyState.classList.remove('d-none');
                }
            })
            .catch(() => {
                loading.classList.add('d-none');
                emptyState.classList.remove('d-none');
            });
    }
</script>

<style>
    .option-box { transition: all 0.2s ease-in-out; }
    .option-box:hover { transform: translateY(-2px); border-color: #0d6efd !important; }
</style>
@endsection