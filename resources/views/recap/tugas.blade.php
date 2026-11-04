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
                            ({{ $index + 1 }}) {{ $assignment->title }}
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
                        </th>
                    @empty
                        <th rowspan="2" class="text-muted fst-italic">Belum ada tugas pada semester ini. Klik tombol "+ Tambah Tugas" di atas.</th>
                    @endforelse

                    <th colspan="3">JML TDK KUMPUL / STATUS</th>
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
                <tr class="{{ $index % 2 == 1 ? 'bg-zebra' : '' }}">
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
                        
                        <td class="cell-clickable col-task-{{ $taskIndex }}" 
                            onclick="updateStatus('{{ $assignment->id }}', '{{ $row['student_id'] }}', '{{ addslashes($row['nama']) }}', '{{ $status }}', '{{ $score }}', '{{ $submittedAtFormatted }}', '{{ $submittedAtRaw }}', '{{ addslashes($feedback) }}')">
                            @if($status == 'H' || $status == 'submitted')
                                <span class="text-success fw-bold">✓</span>
                            @elseif($status == 'T' || $status == 'late')
                                <span class="text-warning fw-bold">✓(T)</span>
                            @else
                                <span class="text-danger fw-bold">X</span>
                            @endif
                        </td>
                    @endforeach

                    <td class="fw-bold bg-light text-success">{{ $row['total_h'] }}</td>
                    <td class="fw-bold bg-light text-warning">{{ $row['total_t'] }}</td>
                    <td class="fw-bold bg-light text-danger">{{ $row['total_a'] }}</td>
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
    <div class="modal-dialog modal-dialog-centered">
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

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
        
        if ($('#modalTugas').length) {
            $('.select2-modal').select2({ theme: 'bootstrap-5', dropdownParent: $('#modalTugas'), width: '100%' });
        }
        if ($('#modalBulkRekap').length) {
            $('.select2-modal-bulk').select2({ theme: 'bootstrap-5', dropdownParent: $('#modalBulkRekap'), width: '100%' });
        }

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const taskStorageKey = 'vis_tasks_' + '{{ $selectedClassroom }}' + '_' + '{{ $selectedSubject }}';

        // RESTORE LOCALSTORAGE STATUS VISIBILITAS KOLOM TUGAS
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

        // TOGGLE FILTER TUGAS
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

        // Submit Form TP
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

        // Submit Form Tugas
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

        // Submit Form Rekap Massal
        $('#formBulkRekap').submit(function(e) {
            e.preventDefault();
            const assignmentId = $('#bulk_assignment_id').val();
            const classroomId  = $('#classroom_id').val();

            if (!assignmentId || !classroomId) {
                Swal.fire('Peringatan', 'Silakan pilih tugas dan kelas terlebih dahulu.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Rekap tugas seluruh siswa akan diperbarui secara massal!",
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

    function updateStatus(assignmentId, studentId, studentName, currentStatus, currentScore, submittedAtFormatted, submittedAtRaw, currentFeedback) {
        const activeStatus = (currentStatus === 'submitted') ? 'H' : ((currentStatus === 'late') ? 'T' : currentStatus);

        Swal.fire({
            title: `Update Rekap, Nilai & Feedback`,
            html: `
                <div class="text-start mb-2">
                    <p class="mb-2">Siswa: <strong>${studentName}</strong></p>
                    
                    <label class="form-label fw-bold mb-1">Status Kehadiran / Pengumpulan:</label>
                    <select id="swal_status" class="form-select mb-3">
                        <option value="H" ${activeStatus === 'H' ? 'selected' : ''}>✓ Hadir (Tepat Waktu)</option>
                        <option value="T" ${activeStatus === 'T' ? 'selected' : ''}>✓(T) Terlambat</option>
                        <option value="A" ${activeStatus === 'A' ? 'selected' : ''}>X Alpa / Belum Mengumpulkan</option>
                    </select>

                    <label class="form-label fw-bold mb-1">Waktu Pengumpulan (Opsional):</label>
                    <input type="datetime-local" id="swal_submitted_at" class="form-control mb-3" value="${submittedAtRaw || ''}">

                    <label class="form-label fw-bold mb-1">Input Nilai Tugas (0-100):</label>
                    <input type="number" id="swal_score" class="form-control mb-3" placeholder="Contoh: 85" min="0" max="100" step="0.1" value="${currentScore !== undefined && currentScore !== null ? currentScore : ''}">

                    <label class="form-label fw-bold mb-1">Catatan / Feedback Guru (Opsional):</label>
                    <textarea id="swal_feedback" class="form-control" rows="2" placeholder="Catatan...">${currentFeedback || ''}</textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan Data',
            preConfirm: () => {
                return $.ajax({
                    url: '{{ route("rekap.tugas.toggle") }}',
                    type: 'POST',
                    data: { 
                        assignment_id: assignmentId, 
                        student_id: studentId, 
                        status: $('#swal_status').val(),
                        score: $('#swal_score').val(),
                        submitted_at: $('#swal_submitted_at').val(),
                        feedback: $('#swal_feedback').val()
                    }
                }).catch(error => {
                    Swal.showValidationMessage(error.responseJSON?.message || 'Gagal memperbarui data.');
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1200, showConfirmButton: false }).then(() => location.reload());
            }
        });
    }
</script>
</x-app-layout>