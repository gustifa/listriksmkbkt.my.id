@section('title', 'Rekapitulasi Penyerahan Tugas Siswa')
<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .table-rekap { border-collapse: separate; border-spacing: 0; font-size: 11px; width: max-content; min-width: 100%; }
        .table-rekap th, .table-rekap td { border-right: 1px solid #333 !important; border-bottom: 1px solid #333 !important; text-align: center; vertical-align: middle; padding: 4px; background-color: #fff; }
        .table-rekap thead tr:first-child th { border-top: 1px solid #333 !important; }
        .table-rekap tr th:first-child, .table-rekap tr td:first-child { border-left: 1px solid #333 !important; }

        .header-vertical { writing-mode: vertical-lr; transform: rotate(180deg); white-space: nowrap; height: 140px; padding: 5px 2px !important; cursor: pointer; }
        .header-vertical:hover { background-color: #e9ecef !important; }
        .cell-clickable { cursor: pointer; font-size: 12px; }
        .cell-clickable:hover { background-color: #d1e7dd !important; }
        .bg-zebra td { background-color: #f8f9fa !important; }
        .text-left { text-align: left !important; padding-left: 6px !important; }
        .cursor-pointer { cursor: pointer; }
        .tp-badge { font-size: 9px; background-color: #0d6efd; color: #fff; padding: 2px 4px; border-radius: 3px; display: block; margin-bottom: 2px; }
        .weight-badge { font-size: 9px; background-color: #6c757d; color: #fff; padding: 1px 4px; border-radius: 3px; display: inline-block; margin-top: 2px; }

        @media (min-width: 769px) {
            .table-rekap th.sticky-col, .table-rekap td.sticky-col { position: sticky; z-index: 2; }
            .sticky-col-1 { left: 0; min-width: 35px; }
            .sticky-col-2 { left: 35px; min-width: 90px; }
            .sticky-col-3 { left: 125px; min-width: 180px; box-shadow: 3px 0 5px -2px rgba(0,0,0,0.15); }
            .table-rekap thead th { position: sticky; top: 0; background-color: #f8f9fa !important; z-index: 3; }
            .table-rekap thead th.sticky-col { z-index: 4; }
        }
        @media (max-width: 768px) {
            .table-rekap { font-size: 10px; }
            .table-rekap th.sticky-col, .table-rekap td.sticky-col { position: static !important; box-shadow: none !important; }
            .sticky-col-3 { min-width: 130px !important; max-width: 150px !important; white-space: normal !important; word-break: break-word !important; }
            .header-vertical { height: 110px !important; }
        }
        @media print {
            .btn, form, modal, .modal, nav, header, footer, sidebar, .no-print, .dropdown { display: none !important; }
            body { background-color: #fff !important; padding: 0 !important; margin: 0 !important; }
            .container-fluid { box-shadow: none !important; padding: 0 !important; }
            @page { size: A4 landscape; margin: 10mm; }
            .table-rekap { width: 100% !important; font-size: 9pt !important; }
            .table-rekap th.sticky-col, .table-rekap td.sticky-col { position: static !important; box-shadow: none !important; }
            [class*="col-task-"] { display: table-cell !important; }
        }
    </style>

<div class="container-fluid bg-white p-3 p-md-4 rounded shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold m-0">REKAPITULASI PENYERAHAN TUGAS SISWA</h5>
            @if($activeAcademicYear)
                <span class="badge bg-success fs-6 mt-1">Tahun Ajaran: {{ $activeAcademicYear->year }} ({{ $activeAcademicYear->semester }})</span>
            @endif
        </div>

        @if($selectedClassroom && $selectedSubject)
        <div class="d-flex flex-wrap gap-2 no-print">
            <button type="button" class="btn btn-outline-dark btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBobotTugas" title="Pengaturan Bobot Tugas">
                ⚖️ Atur Bobot Tugas
            </button>
            <button type="button" class="btn btn-warning btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBulkRekap" title="Pengisian Massal">
                ⚡ Rekap Massal
            </button>
            <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm" title="Cetak Rekap">
                🖨️ Cetak
            </button>
            <a href="{{ route('rekap.tugas.export', ['classroom_id' => $selectedClassroom, 'subject_id' => $selectedSubject]) }}" 
               class="btn btn-outline-success btn-sm" title="Export Excel">
                📊 Export Excel
            </a>
        </div>
        @endif
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('rekap.tugas') }}" class="row g-3 mb-4 no-print">
        <div class="col-12 col-md-4">
            <label class="form-label fw-bold">Pilih Kelas</label>
            <select name="classroom_id" id="classroom_id" class="form-select select2" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ $selectedClassroom == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label fw-bold">Pilih Mata Pelajaran</label>
            <select name="subject_id" id="subject_id" class="form-select select2" onchange="this.form.submit()">
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ $selectedSubject == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4 d-flex align-items-end gap-2">
            @if($selectedSubject)
                <button type="button" class="btn btn-outline-primary w-50" data-bs-toggle="modal" data-bs-target="#modalTP">
                    + Tambah TP
                </button>
            @endif
            @if($selectedClassroom && $selectedSubject)
                <button type="button" class="btn btn-success w-50" data-bs-toggle="modal" data-bs-target="#modalTugas">
                    + Tambah Tugas
                </button>
            @endif
        </div>
    </form>

    <!-- TOOLBAR FILTER HIDE/SHOW KOLOM TUGAS -->
    @if($selectedClassroom && $selectedSubject && $assignments->count() > 0)
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2 gap-2 no-print">
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle fw-semibold shadow-sm" type="button" id="btnFilterKolom" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                👁️ Filter Tampilan Kolom Tugas
            </button>
            <ul class="dropdown-menu p-3 shadow" style="max-height: 320px; overflow-y: auto; width: 290px; z-index: 1050;">
                <li class="fw-bold mb-2 text-primary border-bottom pb-1">Tampilkan / Sembunyikan Tugas:</li>
                <li>
                    <div class="form-check fw-bold text-success mb-2">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="toggle-all-tasks" checked>
                        <label class="form-check-label cursor-pointer" for="toggle-all-tasks">Pilih Semua / Reset</label>
                    </div>
                </li>
                @foreach($assignments as $index => $assignment)
                <li>
                    <div class="form-check py-1">
                        <input class="form-check-input toggle-col-checkbox cursor-pointer" type="checkbox" data-column="{{ $index }}" id="col-check-{{ $index }}" checked>
                        <label class="form-check-label text-truncate d-block cursor-pointer" for="col-check-{{ $index }}" title="{{ $assignment->title }}">
                            ({{ $index + 1 }}) {{ $assignment->title }} {{ ($assignment->is_ignored ?? false) ? '(Diabaikan)' : '' }}
                        </label>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        <small class="text-muted fst-italic">
            💡 Tips: Pilihan tampilan kolom tugas otomatis tersimpan saat halaman di-reload.
        </small>
    </div>
    @endif

    <!-- Tabel Rekapitulasi Grid Matriks -->
    @if($selectedClassroom && $selectedSubject)
    <div class="table-responsive" style="max-height: 650px; overflow: auto;">
        <table class="table table-rekap">
            <thead>
                <tr>
                    <th rowspan="2" class="sticky-col sticky-col-1">NO</th>
                    <th rowspan="2" class="sticky-col sticky-col-2">INDUK SISWA</th>
                    <th rowspan="2" class="sticky-col sticky-col-3">NAMA PESERTA DIDIK</th>
                    
                    @forelse($assignments as $index => $assignment)
                        <th class="header-vertical col-task-{{ $index }}" onclick="showAssignmentInfo('{{ $assignment->id }}')" title="Klik untuk lihat detail tugas">
                            <span class="tp-badge">{{ $assignment->learningObjective->code ?? 'TP' }}</span>
                            {{ $assignment->title }} ({{ $assignment->due_date ? $assignment->due_date->format('d/m') : '-' }})
                            @if($assignment->is_ignored ?? false)
                                <span class="badge bg-secondary d-block mt-1">Diabaikan</span>
                            @else
                                <span class="weight-badge">B: {{ $assignment->weight ?? 1 }}</span>
                            @endif
                        </th>
                    @empty
                        <th rowspan="2" class="text-muted fst-italic">Belum ada tugas pada semester ini. Klik tombol "+ Tambah Tugas" di atas.</th>
                    @endforelse

                    <th colspan="3">JML TDK KUMPUL / STATUS</th>
                    <th rowspan="2" style="width: 65px;" class="bg-light">RATA-RATA</th>
                </tr>
                <tr>
                    @foreach($assignments as $index => $assignment)
                        <th style="font-size: 10px;" class="col-task-{{ $index }}">{{ $index + 1 }}</th>
                    @endforeach
                    <th style="width: 25px;" class="text-success">H</th>
                    <th style="width: 25px;" class="text-warning">T</th>
                    <th style="width: 25px;" class="text-danger">A</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapData as $index => $row)
                <tr id="row-student-{{ $row['student_id'] }}" class="{{ $index % 2 == 1 ? 'bg-zebra' : '' }}">
                    <td class="sticky-col sticky-col-1">{{ $index + 1 }}</td>
                    <td class="sticky-col sticky-col-2">{{ $row['nisn'] }}</td>
                    <td class="sticky-col sticky-col-3 text-left fw-bold">{{ $row['nama'] }}</td>

                    @foreach($assignments as $taskIndex => $assignment)
                        @php 
                            $status               = $row['tugas'][$assignment->id] ?? 'A'; 
                            $score                = $row['nilai'][$assignment->id] ?? ''; 
                            $submittedAtFormatted = $row['tanggal_kumpul'][$assignment->id] ?? ''; 
                            $feedback             = $row['feedback'][$assignment->id] ?? '';
                            
                            $sub = $assignment->submissions->where('student_id', $row['student_id'])->first();
                            $submittedAtRaw = $sub && $sub->submitted_at ? $sub->submitted_at->format('Y-m-d\TH:i') : '';
                        @endphp
                        
                        <!-- PENGGUNAAN DATASET UNTUK MENCEGAH JAVASCRIPT SYNTAX ERROR -->
                        <td class="cell-clickable cell-task col-task-{{ $taskIndex }}" 
                            id="cell-{{ $row['student_id'] }}-{{ $assignment->id }}"
                            data-assignment-id="{{ $assignment->id }}"
                            data-student-id="{{ $row['student_id'] }}"
                            data-student-name="{{ $row['nama'] }}"
                            data-status="{{ $status }}"
                            data-score="{{ $score }}"
                            data-submitted-raw="{{ $submittedAtRaw }}"
                            data-feedback="{{ $feedback }}">
                            
                            <div class="cell-content">
                                @if($status == 'H' || $status == 'submitted')
                                    <span class="text-success fw-bold">✓</span>
                                    @if(!is_null($score) && $score !== '')
                                        <small class="text-muted d-block" style="font-size: 9px;">({{ $score }})</small>
                                    @endif
                                @elseif($status == 'T' || $status == 'late')
                                    <span class="text-warning fw-bold">✓(T)</span>
                                    @if(!is_null($score) && $score !== '')
                                        <small class="text-muted d-block" style="font-size: 9px;">({{ $score }})</small>
                                    @endif
                                @else
                                    <span class="text-danger fw-bold">X</span>
                                @endif

                                @if(!empty($feedback))
                                    <span class="badge bg-info text-dark d-block badge-feedback" style="font-size: 8px; margin-top: 2px;" title="{{ $feedback }}">💬 Kelompok</span>
                                @endif
                            </div>
                        </td>
                    @endforeach

                    <td class="fw-bold bg-light text-success cell-total-h">{{ $row['total_h'] }}</td>
                    <td class="fw-bold bg-light text-warning cell-total-t">{{ $row['total_t'] }}</td>
                    <td class="fw-bold bg-light text-danger cell-total-a">{{ $row['total_a'] }}</td>
                    <td class="fw-bold bg-light cell-rata-rata {{ ($row['rata_rata'] ?? 0) < 70 ? 'text-danger' : 'text-primary' }}">
                        {{ $row['rata_rata'] ?? 0 }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<!-- MODAL 1: TAMBAH TP -->
<div class="modal fade no-print" id="modalTP" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold">➕ Tambah Tujuan Pembelajaran (TP)</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambahTP">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode / Nomor TP</label>
                        <input type="text" id="tp_code" class="form-control" placeholder="Contoh: TP 1.1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi TP</label>
                        <textarea id="tp_description" class="form-control" rows="3" placeholder="Contoh: Menganalisis rangkaian listrik..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan TP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: TAMBAH TUGAS -->
<div class="modal fade no-print" id="modalTugas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title fw-bold">➕ Tambah Tugas Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambahTugas">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Tujuan Pembelajaran (TP)</label>
                        <select id="learning_objective_id" class="form-select select2-modal" required>
                            <option value="">-- Pilih TP --</option>
                            @foreach($learningObjectives as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->code }} - {{ Str::limit($tp->description, 40) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama / Judul Tugas</label>
                        <input type="text" id="assignment_title" class="form-control" placeholder="Contoh: Jobsheet 1 Praktikum" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Batas Waktu (Due Date)</label>
                        <input type="datetime-local" id="assignment_due_date" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Petunjuk Pengerjaan (Opsional)</label>
                        <textarea id="assignment_instructions" class="form-control" rows="2" placeholder="Petunjuk tugas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: REKAP MASSAL -->
@if($selectedClassroom && $selectedSubject)
<div class="modal fade no-print" id="modalBulkRekap" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold">⚡ Pengisian Rekap Tugas Massal</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formBulkRekap">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="bulk_assignment_id" class="form-label fw-bold text-dark">Pilih Kolom Tugas <span class="text-danger">*</span></label>
                        <select id="bulk_assignment_id" class="form-select select2-modal-bulk" required>
                            <option value="">-- Pilih Tugas --</option>
                            @foreach($assignments as $assignment)
                                <option value="{{ $assignment->id }}">{{ $assignment->title }} ({{ $assignment->due_date ? $assignment->due_date->format('d/m') : '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="border rounded p-3 bg-light mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <label class="form-label fw-bold text-primary m-0">Pilih Siswa Terlibat <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="check-all-task-students" checked>
                                <label class="form-check-label fw-semibold text-dark cursor-pointer" for="check-all-task-students">Pilih Semua Siswa</label>
                            </div>
                        </div>

                        <div class="row g-2" style="max-height: 200px; overflow-y: auto;">
                            @foreach($rekapData as $s)
                            <div class="col-12 col-md-6">
                                <div class="form-check bg-white p-2 border rounded shadow-sm">
                                    <input class="form-check-input task-student-checkbox ms-1" type="checkbox" value="{{ $s['student_id'] }}" id="task-student-{{ $s['student_id'] }}" checked>
                                    <label class="form-check-label fw-semibold text-truncate d-block ms-4 cursor-pointer" for="task-student-{{ $s['student_id'] }}">
                                        {{ $s['nama'] }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_status" class="form-label fw-bold text-dark">Status Pengumpulan <span class="text-danger">*</span></label>
                        <select id="bulk_status" class="form-select" required>
                            <option value="H">✓ Hadir (Tepat Waktu)</option>
                            <option value="T">✓(T) Terlambat</option>
                            <option value="A">X Alpa / Belum Mengumpulkan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_submitted_at" class="form-label fw-bold text-dark">Waktu Pengumpulan (Opsional)</label>
                        <input type="datetime-local" id="bulk_submitted_at" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3">
                        <label for="bulk_score" class="form-label fw-bold text-dark">Nilai Tugas Massal (Opsional)</label>
                        <input type="number" id="bulk_score" class="form-control" placeholder="Contoh: 85" min="0" max="100" step="0.1">
                    </div>
                    <div class="mb-3">
                        <label for="bulk_feedback" class="form-label fw-bold text-dark">Catatan / Feedback Guru Massal (Opsional)</label>
                        <textarea id="bulk_feedback" class="form-control" rows="2" placeholder="Catatan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-semibold">⚡ Terapkan Massal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- MODAL 4: ATUR BOBOT & ABAIKAN TUGAS DINAMIS -->
@if($selectedClassroom && $selectedSubject)
<div class="modal fade no-print" id="modalBobotTugas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold">⚖️ Pengaturan Bobot & Status Abaikan Tugas</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUpdateBobot">
                <div class="modal-body p-3 p-md-4">
                    <div class="card border-primary mb-3 bg-light">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-primary mb-2">📌 Informasi & Panduan Pengisian Bobot:</h6>
                            <ul class="mb-0 ps-3 small text-secondary">
                                <li><strong>Bobot Standar (1):</strong> Tugas harian / kuis biasa memiliki bobot <code>1</code>.</li>
                                <li><strong>Bobot Tinggi (>1):</strong> Untuk tugas skala besar/Praktikum/Proyek, berikan bobot misal <code>2</code> atau <code>3</code> (dampaknya lebih besar pada rata-rata).</li>
                                <li><strong>Fitur "Abaikan":</strong> Jika dicentang, tugas otomatis berbobot <code>0</code> dan <strong>TIDAK dihitung</strong> dalam rata-rata nilai maupun persentase alpa siswa.</li>
                                <li><strong>Rumus Rata-Rata Terbobot:</strong> <br>
                                    <code>Rata-Rata = Σ(Nilai × Bobot) ÷ Σ(Total Bobot Tugas Aktif)</code>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" class="border rounded">
                        <table class="table table-bordered align-middle m-0" style="width: 100%; table-layout: fixed;">
                            <thead class="table-light text-center sticky-top">
                                <tr>
                                    <th style="width: 45px;">No</th>
                                    <th>Judul Tugas</th>
                                    <th style="width: 130px;">Bobot Nilai</th>
                                    <th style="width: 120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignments as $index => $asg)
                                <tr>
                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                    <td class="text-wrap">
                                        <div class="fw-bold text-dark text-break">{{ $asg->title }}</div>
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                            Deadline: {{ $asg->due_date ? $asg->due_date->format('d/m/Y H:i') : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" 
                                                   name="weights[{{ $asg->id }}]" 
                                                   class="form-control text-center input-weight fw-bold" 
                                                   value="{{ $asg->weight ?? 1 }}" 
                                                   min="0" max="100" step="0.5"
                                                   {{ ($asg->is_ignored ?? false) ? 'disabled' : '' }}>
                                            <span class="input-group-text">x</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input type="hidden" name="ignored[{{ $asg->id }}]" value="0">
                                            <input class="form-check-input check-ignore cursor-pointer" 
                                                   type="checkbox" 
                                                   name="ignored[{{ $asg->id }}]" 
                                                   value="1" 
                                                   id="ignore-{{ $asg->id }}"
                                                   {{ ($asg->is_ignored ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold text-danger cursor-pointer" for="ignore-{{ $asg->id }}">Abaikan</label>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark fw-bold">💾 Simpan Pengaturan Bobot</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Inisialisasi daftar siswa dengan aman via JSON
    const studentsInClass = @json($rekapData->map(function($item) {
        return ['id' => $item['student_id'], 'name' => $item['nama']];
    }));

    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
        if ($('#modalTugas').length) {
            $('.select2-modal').select2({ theme: 'bootstrap-5', dropdownParent: $('#modalTugas'), width: '100%' });
        }
        if ($('#modalBulkRekap').length) {
            $('.select2-modal-bulk').select2({ theme: 'bootstrap-5', dropdownParent: $('#modalBulkRekap'), width: '100%' });
        }

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // EVENT CLICK SEL TABEL UNTUK MEMBUKA MODAL EDIT
        $(document).on('click', '.cell-task', function() {
            const cell = $(this);
            openUpdateModal(cell);
        });

        const taskStorageKey = 'vis_tasks_' + '{{ $selectedClassroom }}' + '_' + '{{ $selectedSubject }}';

        function restoreVisibility() {
            const savedState = localStorage.getItem(taskStorageKey);
            if (savedState) {
                const hiddenCols = JSON.parse(savedState);
                $('.toggle-col-checkbox').each(function() {
                    const colIdx = $(this).data('column');
                    if (hiddenCols.includes(colIdx)) {
                        $(this).prop('checked', false);
                        $(`.col-task-${colIdx}`).hide();
                    } else {
                        $(this).prop('checked', true);
                        $(`.col-task-${colIdx}`).show();
                    }
                });
                $('#toggle-all-tasks').prop('checked', $('.toggle-col-checkbox:checked').length === $('.toggle-col-checkbox').length);
            }
        }

        function saveVisibility() {
            const hiddenCols = [];
            $('.toggle-col-checkbox').each(function() {
                const colIdx = $(this).data('column');
                if (!$(this).is(':checked')) {
                    hiddenCols.push(colIdx);
                }
            });
            localStorage.setItem(taskStorageKey, JSON.stringify(hiddenCols));
        }

        restoreVisibility();

        $(document).on('change', '.toggle-col-checkbox', function() {
            const colIndex = $(this).data('column');
            if ($(this).is(':checked')) { 
                $(`.col-task-${colIndex}`).show(); 
            } else { 
                $(`.col-task-${colIndex}`).hide(); 
            }
            $('#toggle-all-tasks').prop('checked', $('.toggle-col-checkbox:checked').length === $('.toggle-col-checkbox').length);
            saveVisibility();
        });

        $(document).on('change', '#toggle-all-tasks', function() {
            const isChecked = $(this).is(':checked');
            $('.toggle-col-checkbox').prop('checked', isChecked);
            if (isChecked) {
                $('[class*="col-task-"]').show();
                localStorage.removeItem(taskStorageKey);
            } else {
                $('[class*="col-task-"]').hide();
                saveVisibility();
            }
        });

        $('#formTambahTP').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route("rekap.tp.store") }}',
                type: 'POST',
                data: {
                    subject_id: $('#subject_id').val(),
                    code: $('#tp_code').val(),
                    description: $('#tp_description').val()
                },
                success: function(res) {
                    $('#modalTP').modal('hide');
                    Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                },
                error: function(err) {
                    Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        });

        $('#formTambahTugas').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route("rekap.tugas.store_assignment") }}',
                type: 'POST',
                data: {
                    classroom_id: $('#classroom_id').val(),
                    subject_id: $('#subject_id').val(),
                    learning_objective_id: $('#learning_objective_id').val(),
                    title: $('#assignment_title').val(),
                    due_date: $('#assignment_due_date').val(),
                    instructions: $('#assignment_instructions').val()
                },
                success: function(res) {
                    $('#modalTugas').modal('hide');
                    Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                },
                error: function(err) {
                    Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        });

        $('#check-all-task-students').change(function() {
            $('.task-student-checkbox').prop('checked', $(this).is(':checked'));
        });

        $('#formBulkRekap').submit(function(e) {
            e.preventDefault();
            const assignmentId = $('#bulk_assignment_id').val();
            const classroomId  = $('#classroom_id').val();

            let selectedStudents = [];
            $('.task-student-checkbox:checked').each(function() {
                selectedStudents.push($(this).val());
            });

            if (!assignmentId || !classroomId) {
                Swal.fire('Peringatan', 'Silakan pilih tugas dan kelas terlebih dahulu.', 'warning');
                return;
            }

            if (selectedStudents.length === 0) {
                Swal.fire('Peringatan', 'Silakan pilih setidaknya satu siswa!', 'warning');
                return;
            }

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: `Rekap tugas akan diperbarui secara massal untuk ${selectedStudents.length} siswa terpilih!`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Ya, Terapkan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                    $.ajax({
                        url: '{{ route("rekap.tugas.bulk_update") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            assignment_id: assignmentId,
                            classroom_id: classroomId,
                            student_ids: selectedStudents,
                            status: $('#bulk_status').val(),
                            submitted_at: $('#bulk_submitted_at').val(),
                            score: $('#bulk_score').val(),
                            feedback: $('#bulk_feedback').val()
                        },
                        success: function(res) {
                            $('#modalBulkRekap').modal('hide');
                            Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                        },
                        error: function(err) {
                            Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal memperbarui data', 'error');
                        }
                    });
                }
            });
        });

        $(document).on('change', '.check-ignore', function() {
            let isChecked = $(this).is(':checked');
            let weightInput = $(this).closest('tr').find('.input-weight');
            if (isChecked) {
                weightInput.prop('disabled', true).val(0);
            } else {
                weightInput.prop('disabled', false).val(1);
            }
        });

        $('#formUpdateBobot').submit(function(e) {
            e.preventDefault();
            
            let disabledInputs = $(this).find(':input:disabled').prop('disabled', false);
            let formData = $(this).serialize();
            disabledInputs.prop('disabled', true);
            
            Swal.fire({ title: 'Menyimpan Bobot...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            
            $.ajax({
                url: '{{ route("rekap.tugas.update_weights") }}',
                type: 'POST',
                data: formData + '&_token={{ csrf_token() }}',
                success: function(resp) {
                    $('#modalBobotTugas').modal('hide');
                    Swal.fire('Berhasil!', resp.message, 'success').then(() => location.reload());
                },
                error: function(err) {
                    Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        });
    });

    function showAssignmentInfo(assignmentId) {
        Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.ajax({
            url: `/rekap-tugas/detail/${assignmentId}`,
            type: 'GET',
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        title: `📌 ${res.title}`,
                        html: `
                            <div class="text-start fs-6">
                                <p class="mb-1"><strong>Kode TP:</strong> <span class="badge bg-primary">${res.tp_code}</span></p>
                                <p class="mb-1"><strong>Deskripsi TP:</strong> ${res.tp_desc}</p>
                                <p class="mb-1"><strong>Mata Pelajaran:</strong> ${res.subject}</p>
                                <p class="mb-1"><strong>Deadline:</strong> <span class="badge bg-danger">${res.due_date}</span></p>
                                <hr class="my-2">
                                <p class="mb-1"><strong>Petunjuk Tugas:</strong></p>
                                <div class="p-2 bg-light rounded border">${res.instructions}</div>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonText: 'Tutup'
                    });
                }
            },
            error: function() { Swal.fire('Error', 'Gagal memuat detail data tugas.', 'error'); }
        });
    }

    // FUNGSI MEMBUKA MODAL & UPDATE AJAX REKAP SEL TANPA RELOAD
    function openUpdateModal(cellElement) {
        const assignmentId = cellElement.data('assignment-id');
        const studentId    = cellElement.data('student-id');
        const studentName  = cellElement.data('student-name');
        const currentStatus= cellElement.data('status');
        const currentScore = cellElement.data('score');
        const submittedRaw = cellElement.data('submitted-raw');
        const currentFb    = cellElement.data('feedback');

        const activeStatus = (currentStatus === 'submitted') ? 'H' : ((currentStatus === 'late') ? 'T' : currentStatus);

        let groupOptionsHtml = '';
        if (Array.isArray(studentsInClass)) {
            studentsInClass.forEach(s => {
                if (s.id !== studentId) {
                    groupOptionsHtml += `
                        <div class="col-6">
                            <div class="form-check text-start">
                                <input class="form-check-input group-student-checkbox" type="checkbox" value="${s.id}" id="group_st_${s.id}">
                                <label class="form-check-label small text-truncate d-block cursor-pointer" for="group_st_${s.id}">
                                    ${s.name}
                                </label>
                            </div>
                        </div>
                    `;
                }
            });
        }

        Swal.fire({
            title: `Update Rekap & Feedback`,
            html: `
                <div class="text-start mb-2">
                    <p class="mb-2">Siswa Utama: <strong>${studentName}</strong></p>
                    
                    <label class="form-label fw-bold mb-1">Status Kehadiran / Pengumpulan:</label>
                    <select id="swal_status" class="form-select mb-3">
                        <option value="H" ${activeStatus === 'H' ? 'selected' : ''}>✓ Hadir (Tepat Waktu)</option>
                        <option value="T" ${activeStatus === 'T' ? 'selected' : ''}>✓(T) Terlambat</option>
                        <option value="A" ${activeStatus === 'A' ? 'selected' : ''}>X Alpa / Belum Mengumpulkan</option>
                    </select>

                    <label class="form-label fw-bold mb-1">Waktu Pengumpulan (Opsional):</label>
                    <input type="datetime-local" id="swal_submitted_at" class="form-control mb-3" value="${submittedRaw || ''}">

                    <label class="form-label fw-bold mb-1">Input Nilai Tugas (0-100):</label>
                    <input type="number" id="swal_score" class="form-control mb-3" placeholder="Contoh: 85" min="0" max="100" step="0.1" value="${currentScore !== undefined && currentScore !== null ? currentScore : ''}">

                    <label class="form-label fw-bold mb-1">Catatan / Feedback Guru:</label>
                    <textarea id="swal_feedback" class="form-control mb-3" rows="2" placeholder="Tulis feedback tugas di sini...">${currentFb || ''}</textarea>

                    <div class="border rounded p-2 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                            <label class="form-label fw-bold text-primary m-0 small">👥 Salin Nilai & Feedback ke Anggota Kelompok:</label>
                            <small class="text-muted" style="font-size: 10px;">(Opsional)</small>
                        </div>
                        <div class="row g-1" style="max-height: 120px; overflow-y: auto;">
                            ${groupOptionsHtml}
                        </div>
                    </div>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: '💾 Simpan',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                let selectedGroupMembers = [];
                $('.group-student-checkbox:checked').each(function() {
                    selectedGroupMembers.push($(this).val());
                });

                return $.ajax({
                    url: '{{ route("rekap.tugas.toggle") }}',
                    type: 'POST',
                    data: { 
                        assignment_id: assignmentId, 
                        student_id: studentId, 
                        status: $('#swal_status').val(),
                        score: $('#swal_score').val(),
                        submitted_at: $('#swal_submitted_at').val(),
                        feedback: $('#swal_feedback').val(),
                        group_student_ids: selectedGroupMembers
                    }
                }).then(response => {
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(error.responseJSON?.message || 'Gagal memperbarui data.');
                });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value && result.value.success) {
                const resData = result.value.updated_data || [];
                
                // UPDATE SEL DOM SECARA REALTME TANPA RELOAD HALAMAN
                resData.forEach(item => {
                    const targetCell = $(`#cell-${item.student_id}-${item.assignment_id}`);
                    if (targetCell.length) {
                        // Update Data Attributes
                        targetCell.data('status', item.status);
                        targetCell.data('score', item.score);
                        targetCell.data('feedback', item.feedback);

                        // Render Ulang Isi Sel HTML
                        let cellHtml = '';
                        if (item.status === 'H' || item.status === 'submitted') {
                            cellHtml += '<span class="text-success fw-bold">✓</span>';
                            if (item.score !== null && item.score !== '') {
                                cellHtml += `<small class="text-muted d-block" style="font-size: 9px;">(${item.score})</small>`;
                            }
                        } else if (item.status === 'T' || item.status === 'late') {
                            cellHtml += '<span class="text-warning fw-bold">✓(T)</span>';
                            if (item.score !== null && item.score !== '') {
                                cellHtml += `<small class="text-muted d-block" style="font-size: 9px;">(${item.score})</small>`;
                            }
                        } else {
                            cellHtml += '<span class="text-danger fw-bold">X</span>';
                        }

                        if (item.feedback) {
                            cellHtml += `<span class="badge bg-info text-dark d-block badge-feedback" style="font-size: 8px; margin-top: 2px;" title="${item.feedback}">💬 Kelompok</span>`;
                        }

                        targetCell.find('.cell-content').html(cellHtml);

                        // Update Ringkasan Total & Rata-rata pada baris siswa
                        const row = $(`#row-student-${item.student_id}`);
                        if (row.length && item.summary) {
                            row.find('.cell-total-h').text(item.summary.total_h);
                            row.find('.cell-total-t').text(item.summary.total_t);
                            row.find('.cell-total-a').text(item.summary.total_a);
                            
                            const rataRataCell = row.find('.cell-rata-rata');
                            rataRataCell.text(item.summary.rata_rata);
                            if (item.summary.rata_rata < 70) {
                                rataRataCell.removeClass('text-primary').addClass('text-danger');
                            } else {
                                rataRataCell.removeClass('text-danger').addClass('text-primary');
                            }
                        }
                    }
                });

                Swal.fire({ icon: 'success', title: 'Berhasil Diperbarui!', timer: 1000, showConfirmButton: false });
            }
        });
    }
</script>
</x-app-layout>