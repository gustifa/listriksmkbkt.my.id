@section('title', 'Rekap Pembelajaran Terpadu (Presensi & Tugas)')
<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .table-rekap { border-collapse: separate; border-spacing: 0; font-size: 11px; width: max-content; min-width: 100%; }
        .table-rekap th, .table-rekap td { border-right: 1px solid #333 !important; border-bottom: 1px solid #333 !important; text-align: center; vertical-align: middle; padding: 4px; background-color: #fff; }
        .table-rekap thead tr:first-child th { border-top: 1px solid #333 !important; }
        .table-rekap tr th:first-child, .table-rekap tr td:first-child { border-left: 1px solid #333 !important; }

        .header-vertical { writing-mode: vertical-lr; transform: rotate(180deg); white-space: nowrap; height: 125px; padding: 5px 2px !important; }
        .cell-clickable { cursor: pointer; font-size: 12px; }
        .cell-clickable:hover { background-color: #d1e7dd !important; }
        .bg-warning-low { background-color: #f8d7da !important; color: #721c24 !important; }
        .bg-zebra td { background-color: #f8f9fa !important; }
        .text-left { text-align: left !important; padding-left: 6px !important; }
        .note-badge { font-size: 10px; cursor: pointer; display: inline-block; }

        @media (min-width: 769px) {
            .table-rekap th.sticky-col, .table-rekap td.sticky-col { position: sticky; z-index: 2; }
            .sticky-col-1 { left: 0; min-width: 35px; }
            .sticky-col-2 { left: 35px; min-width: 85px; }
            .sticky-col-3 { left: 120px; min-width: 175px; box-shadow: 3px 0 5px -2px rgba(0,0,0,0.15); }
            .table-rekap thead th { position: sticky; top: 0; background-color: #f8f9fa !important; z-index: 3; }
            .table-rekap thead th.sticky-col { z-index: 4; }
        }
        @media (max-width: 768px) {
            .table-rekap th.sticky-col, .table-rekap td.sticky-col { position: static !important; box-shadow: none !important; }
            .sticky-col-3 { min-width: 130px !important; }
        }
        @media print {
            .btn, form, modal, .modal, nav, header, footer, sidebar, .no-print, .nav-tabs { display: none !important; }
            body { background-color: #fff !important; padding: 0 !important; }
            @page { size: A4 landscape; margin: 10mm; }
            .tab-pane { display: block !important; opacity: 1 !important; visibility: visible !important; }
            [class*="col-date-"], [class*="col-task-"] { display: table-cell !important; }
        }
    </style>

<div class="container-fluid bg-white p-3 p-md-4 rounded shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold m-0">REKAPITULASI PEMBELAJARAN TERPADU</h5>
            @if($activeAcademicYear)
                <span class="badge bg-success fs-6 mt-1">Tahun Ajaran: {{ $activeAcademicYear->year }} ({{ $activeAcademicYear->semester }})</span>
            @endif
        </div>

        @if($selectedClassroom && $selectedSubject)
        <div class="d-flex flex-wrap gap-2 no-print">
            <button type="button" class="btn btn-warning btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBulkPresensi">
                ⚡ Presensi & Jurnal Massal
            </button>
            <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm">🖨️ Cetak Rekap</button>
        </div>
        @endif
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('rekap.pembelajaran.index') }}" class="row g-3 mb-4 no-print">
        <div class="col-12 col-md-5">
            <label class="form-label fw-bold">Pilih Kelas</label>
            <select name="classroom_id" id="classroom_id" class="form-select select2" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ $selectedClassroom == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-5">
            <label class="form-label fw-bold">Pilih Mata Pelajaran</label>
            <select name="subject_id" id="subject_id" class="form-select select2" onchange="this.form.submit()">
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ $selectedSubject == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @if($selectedClassroom && $selectedSubject)
    <!-- TAB NAVIGATION BAR -->
    <ul class="nav nav-tabs fw-bold mb-3 no-print" id="pembelajaranTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active text-primary" id="presensi-tab" data-bs-toggle="tab" data-bs-target="#presensi-pane" type="button" role="tab">
                📅 Rekap Presensi & Jurnal KBM
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-success" id="tugas-tab" data-bs-toggle="tab" data-bs-target="#tugas-pane" type="button" role="tab">
                📚 Rekap Tugas & Nilai
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-danger" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary-pane" type="button" role="tab">
                📊 Performa & Peringatan Siswa
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pembelajaranTabContent">
        <!-- ================= TAB 1: PRESENSI & JURNAL ================= -->
        <div class="tab-pane fade show active" id="presensi-pane" role="tabpanel">
            
            <!-- TOOLBAR DROPDOWN FILTER KOLOM TANGGAL PRESENSI -->
            @if($dates->count() > 0)
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2 gap-2 no-print">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle fw-semibold shadow-sm" type="button" id="btnFilterKolomDates" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        👁️ Filter Tampilan Tanggal
                    </button>
                    <ul class="dropdown-menu p-3 shadow" style="max-height: 300px; overflow-y: auto; width: 280px; z-index: 1050;">
                        <li class="fw-bold mb-2 text-primary border-bottom pb-1">Sembunyikan / Tampilkan:</li>
                        <li>
                            <div class="form-check fw-bold text-success mb-2">
                                <input class="form-check-input" type="checkbox" id="toggle-all-dates" checked>
                                <label class="form-check-label" for="toggle-all-dates">Pilih Semua / Reset</label>
                            </div>
                        </li>
                        @foreach($dates as $index => $d)
                        <li>
                            <div class="form-check py-1">
                                <input class="form-check-input toggle-date-checkbox" type="checkbox" data-column="{{ $index }}" id="col-check-date-{{ $index }}" checked>
                                <label class="form-check-label text-truncate d-block cursor-pointer" for="col-check-date-{{ $index }}">
                                    P-{{ $index + 1 }} ({{ \Carbon\Carbon::parse($d->date)->format('d/m/Y') }})
                                </label>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <small class="text-muted fst-italic">
                    📌 Keterangan Visual: <strong>📝</strong> Ada Catatan | <strong>📎</strong> Ada Surat | Latar Merah: Kehadiran <strong>< 75%</strong>
                </small>
            </div>
            @endif

            <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                <table class="table table-rekap">
                    <thead>
                        <tr>
                            <th rowspan="2" class="sticky-col sticky-col-1">NO</th>
                            <th rowspan="2" class="sticky-col sticky-col-2">INDUK</th>
                            <th rowspan="2" class="sticky-col sticky-col-3">NAMA SISWA</th>
                            
                            @forelse($dates as $index => $d)
                                @php $jurnal = $jurnalMap[$d->date] ?? null; @endphp
                                <th class="header-vertical col-date-{{ $index }}" title="{{ $jurnal ? 'Materi: '.$jurnal->topic : 'Pertemuan '.($index+1) }}">
                                    {{ \Carbon\Carbon::parse($d->date)->format('d/m/Y') }}
                                </th>
                            @empty
                                <th rowspan="2" class="text-muted fst-italic">Belum ada data presensi.</th>
                            @endforelse

                            <th colspan="4">REKAP</th>
                            <th rowspan="2" style="width: 35px;">NOTE</th>
                            <th rowspan="2" style="width: 45px;">% HADIR</th>
                        </tr>
                        <tr>
                            @foreach($dates as $index => $d)
                                <th style="font-size: 10px;" class="col-date-{{ $index }}">P-{{ $index + 1 }}</th>
                            @endforeach
                            <th style="width: 22px;" class="text-success">H</th>
                            <th style="width: 22px;" class="text-info">S</th>
                            <th style="width: 22px;" class="text-warning">I</th>
                            <th style="width: 22px;" class="text-danger">A</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapData as $index => $row)
                        <tr class="{{ $row['attendance_pct'] < 75 ? 'bg-warning-low' : ($index % 2 == 1 ? 'bg-zebra' : '') }}">
                            <td class="sticky-col sticky-col-1">{{ $index + 1 }}</td>
                            <td class="sticky-col sticky-col-2">{{ $row['nisn'] }}</td>
                            <td class="sticky-col sticky-col-3 text-left fw-bold">
                                {{ $row['nama'] }}
                                @if($row['phone'])
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $row['phone']) }}" target="_blank" class="no-print ms-1 text-success text-decoration-none">📱</a>
                                @endif
                            </td>

                            @foreach($dates as $dateIndex => $d)
                                @php 
                                    $st = $row['presensi'][$d->date] ?? 'A'; 
                                    $catatan = $row['catatan'][$d->date] ?? '';
                                    $attach  = $row['attachment'][$d->date] ?? '';
                                @endphp
                                <td class="cell-clickable col-date-{{ $dateIndex }}" 
                                    onclick="updatePresensi('{{ $row['student_id'] }}', '{{ addslashes($row['nama']) }}', '{{ $d->date }}', '{{ $st }}', '{{ addslashes($catatan) }}', '{{ $attach }}')">
                                    @if($st == 'H') <span class="text-success fw-bold">H</span>
                                    @elseif($st == 'S') <span class="text-info fw-bold">S</span>
                                    @elseif($st == 'I') <span class="text-warning fw-bold">I</span>
                                    @else <span class="text-danger fw-bold">A</span>
                                    @endif

                                    @if(!empty($catatan)) <span class="note-badge text-primary" title="Catatan: {{ $catatan }}">📝</span> @endif
                                    @if(!empty($attach)) <span class="note-badge text-danger" title="Ada Surat Lampiran">📎</span> @endif
                                </td>
                            @endforeach

                            <td class="fw-bold bg-light text-success">{{ $row['total_h'] }}</td>
                            <td class="fw-bold bg-light text-info">{{ $row['total_s'] }}</td>
                            <td class="fw-bold bg-light text-warning">{{ $row['total_i'] }}</td>
                            <td class="fw-bold bg-light text-danger">{{ $row['total_a'] }}</td>
                            <td class="fw-bold bg-light text-primary">{{ $row['total_catatan'] ?: '-' }}</td>
                            <td class="fw-bold {{ $row['attendance_pct'] < 75 ? 'text-danger' : 'text-primary' }}">{{ $row['attendance_pct'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= TAB 2: REKAP TUGAS & NILAI ================= -->
        <div class="tab-pane fade" id="tugas-pane" role="tabpanel">
            
            <!-- TOOLBAR DROPDOWN FILTER KOLOM TUGAS -->
            @if($assignments->count() > 0)
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2 gap-2 no-print">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle fw-semibold shadow-sm" type="button" id="btnFilterKolomTasks" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        👁️ Filter Tampilan Kolom Tugas
                    </button>
                    <ul class="dropdown-menu p-3 shadow" style="max-height: 300px; overflow-y: auto; width: 280px; z-index: 1050;">
                        <li class="fw-bold mb-2 text-primary border-bottom pb-1">Sembunyikan / Tampilkan:</li>
                        <li>
                            <div class="form-check fw-bold text-success mb-2">
                                <input class="form-check-input" type="checkbox" id="toggle-all-tasks" checked>
                                <label class="form-check-label" for="toggle-all-tasks">Pilih Semua / Reset</label>
                            </div>
                        </li>
                        @foreach($assignments as $index => $asg)
                        <li>
                            <div class="form-check py-1">
                                <input class="form-check-input toggle-task-checkbox" type="checkbox" data-column="{{ $index }}" id="col-check-task-{{ $index }}" checked>
                                <label class="form-check-label text-truncate d-block cursor-pointer" for="col-check-task-{{ $index }}" title="{{ $asg->title }}">
                                    ({{ $index + 1 }}) {{ $asg->title }}
                                </label>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                <table class="table table-rekap">
                    <thead>
                        <tr>
                            <th rowspan="2" class="sticky-col sticky-col-1">NO</th>
                            <th rowspan="2" class="sticky-col sticky-col-2">INDUK</th>
                            <th rowspan="2" class="sticky-col sticky-col-3">NAMA SISWA</th>
                            
                            @forelse($assignments as $asgIndex => $asg)
                                <th class="header-vertical col-task-{{ $asgIndex }}">
                                    {{ $asg->title }}
                                </th>
                            @empty
                                <th rowspan="2" class="text-muted fst-italic">Belum ada tugas dibuat.</th>
                            @endforelse

                            <th rowspan="2" style="width: 60px;">RATA-RATA</th>
                        </tr>
                        <tr>
                            @foreach($assignments as $asgIndex => $asg)
                                <th style="font-size: 10px;" class="col-task-{{ $asgIndex }}">T-{{ $asgIndex + 1 }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapData as $index => $row)
                        <tr class="{{ $index % 2 == 1 ? 'bg-zebra' : '' }}">
                            <td class="sticky-col sticky-col-1">{{ $index + 1 }}</td>
                            <td class="sticky-col sticky-col-2">{{ $row['nisn'] }}</td>
                            <td class="sticky-col sticky-col-3 text-left fw-bold">{{ $row['nama'] }}</td>

                            @foreach($assignments as $asgIndex => $asg)
                                @php $grade = $row['tugas'][$asg->id] ?? null; @endphp
                                <td class="fw-bold col-task-{{ $asgIndex }} {{ is_null($grade) ? 'text-muted' : ($grade < 70 ? 'text-danger' : 'text-dark') }}">
                                    {{ is_null($grade) ? '-' : $grade }}
                                </td>
                            @endforeach

                            <td class="fw-bold bg-light {{ $row['avg_grade'] < 70 ? 'text-danger' : 'text-success' }}">
                                {{ $row['avg_grade'] }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= TAB 3: ANALISIS PERFORMA TERPADU ================= -->
        <div class="tab-pane fade" id="summary-pane" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>NAMA SISWA</th>
                            <th style="width: 130px;">% KEHADIRAN</th>
                            <th style="width: 130px;">RATA-RATA TUGAS</th>
                            <th style="width: 160px;">STATUS PERFORMA</th>
                            <th style="width: 100px;">AKSI WA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapData as $index => $row)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $row['nama'] }}</td>
                            <td class="text-center fw-bold {{ $row['attendance_pct'] < 75 ? 'text-danger' : 'text-success' }}">
                                {{ $row['attendance_pct'] }}%
                            </td>
                            <td class="text-center fw-bold {{ $row['avg_grade'] < 70 ? 'text-danger' : 'text-success' }}">
                                {{ $row['avg_grade'] }}
                            </td>
                            <td class="text-center">
                                @if($row['need_attention'])
                                    <span class="badge bg-danger">⚠️ Perlu Pembinaan</span>
                                @else
                                    <span class="badge bg-success">✓ Baik / Normal</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row['phone'])
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $row['phone']) }}?text=Halo%20Orang%20Tua%20dari%20{{ urlencode($row['nama']) }},%20Laporan%20Performa:%20Presensi%20{{ $row['attendance_pct'] }}%,%20Rata%20Tugas%20{{ $row['avg_grade'] }}" 
                                       target="_blank" class="btn btn-sm btn-outline-success">📱 Chat OrtU</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- MODAL PRESENSI & JURNAL MASSAL (DENGAN CHECKBOX SISWA) -->
@if($selectedClassroom && $selectedSubject)
<div class="modal fade no-print" id="modalBulkPresensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold">⚡ Presensi Massal & Jurnal Pembelajaran Guru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formBulkPresensi">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Tanggal Pertemuan <span class="text-danger">*</span></label>
                            <input type="date" id="bulk_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Set Status Kehadiran Massal <span class="text-danger">*</span></label>
                            <select id="bulk_status" class="form-select" required>
                                <option value="H">✓ Hadir Semua Terpilih</option>
                                <option value="S">Sakit</option>
                                <option value="I">Izin</option>
                                <option value="A">Alpa / Belum Hadir</option>
                            </select>
                        </div>
                    </div>

                    <!-- DAFTAR SISWA CHECKBOX -->
                    <div class="border rounded p-3 bg-light mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <label class="form-label fw-bold text-primary m-0">Pilih Siswa Terlibat <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="check-all-pem-students" checked>
                                <label class="form-check-label fw-semibold text-dark cursor-pointer" for="check-all-pem-students">Pilih Semua Siswa</label>
                            </div>
                        </div>

                        <div class="row g-2" style="max-height: 220px; overflow-y: auto;">
                            @foreach($rekapData as $s)
                            <div class="col-12 col-md-6">
                                <div class="form-check bg-white p-2 border rounded shadow-sm">
                                    <input class="form-check-input pem-student-checkbox ms-1" type="checkbox" value="{{ $s['student_id'] }}" id="pem-student-{{ $s['student_id'] }}" checked>
                                    <label class="form-check-label fw-semibold text-truncate d-block ms-4 cursor-pointer" for="pem-student-{{ $s['student_id'] }}">
                                        {{ $s['nama'] }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary border-bottom pb-1 mb-3">📖 Jurnal Pembelajaran Guru (Hari Ini)</h6>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Materi / Kompetensi Dasar (KD)</label>
                        <input type="text" id="bulk_topic" class="form-control" placeholder="Contoh: Bab 3 Rangkaian Arus Searah">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kegiatan Pembelajaran</label>
                        <textarea id="bulk_activities" class="form-control" rows="2" placeholder="Contoh: Praktikum multimeter"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hambatan / Catatan Pelaksanaan KBM</label>
                        <textarea id="bulk_obstacles" class="form-control" rows="2" placeholder="Contoh: Unit multimeter terbatas"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold">⚡ Terapkan Presensi & Simpan Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

        // KEY DUA LOCALSTORAGE SPESIFIK AGAR TERISOLASI BERDASARKAN KELAS & MAPEL
        const dateStorageKey = 'vis_dates_' + '{{ $selectedClassroom }}' + '_' + '{{ $selectedSubject }}';
        const taskStorageKey = 'vis_tasks_' + '{{ $selectedClassroom }}' + '_' + '{{ $selectedSubject }}';

        // 1. MEMBACA PERSISTENSI VISIBILITAS UNTUK DATES & TASKS
        function restoreVisibility() {
            // Restore Dates
            const savedDates = localStorage.getItem(dateStorageKey);
            if (savedDates) {
                const hiddenDates = JSON.parse(savedDates);
                $('.toggle-date-checkbox').each(function() {
                    const colIdx = $(this).data('column');
                    if (hiddenDates.includes(colIdx)) {
                        $(this).prop('checked', false);
                        $(`.col-date-${colIdx}`).hide();
                    } else {
                        $(this).prop('checked', true);
                        $(`.col-date-${colIdx}`).show();
                    }
                });
                $('#toggle-all-dates').prop('checked', $('.toggle-date-checkbox:checked').length === $('.toggle-date-checkbox').length);
            }

            // Restore Tasks
            const savedTasks = localStorage.getItem(taskStorageKey);
            if (savedTasks) {
                const hiddenTasks = JSON.parse(savedTasks);
                $('.toggle-task-checkbox').each(function() {
                    const colIdx = $(this).data('column');
                    if (hiddenTasks.includes(colIdx)) {
                        $(this).prop('checked', false);
                        $(`.col-task-${colIdx}`).hide();
                    } else {
                        $(this).prop('checked', true);
                        $(`.col-task-${colIdx}`).show();
                    }
                });
                $('#toggle-all-tasks').prop('checked', $('.toggle-task-checkbox:checked').length === $('.toggle-task-checkbox').length);
            }
        }

        // 2. MENYIMPAN STATUS KE LOCALSTORAGE
        function saveVisibility(key, checkboxClass) {
            const hiddenCols = [];
            $(checkboxClass).each(function() {
                const colIdx = $(this).data('column');
                if (!$(this).is(':checked')) {
                    hiddenCols.push(colIdx);
                }
            });
            localStorage.setItem(key, JSON.stringify(hiddenCols));
        }

        // JALANKAN RESTORE SAAT HALAMAN DILOAD
        restoreVisibility();

        // 3. EVENT HANDLER TOGGLE DATE
        $(document).on('change', '.toggle-date-checkbox', function() {
            const colIdx = $(this).data('column');
            if ($(this).is(':checked')) { $(`.col-date-${colIdx}`).show(); } 
            else { $(`.col-date-${colIdx}`).hide(); }
            
            $('#toggle-all-dates').prop('checked', $('.toggle-date-checkbox:checked').length === $('.toggle-date-checkbox').length);
            saveVisibility(dateStorageKey, '.toggle-date-checkbox');
        });

        $(document).on('change', '#toggle-all-dates', function() {
            const isChecked = $(this).is(':checked');
            $('.toggle-date-checkbox').prop('checked', isChecked);
            if (isChecked) {
                $('[class*="col-date-"]').show();
                localStorage.removeItem(dateStorageKey);
            } else {
                $('[class*="col-date-"]').hide();
                saveVisibility(dateStorageKey, '.toggle-date-checkbox');
            }
        });

        // 4. EVENT HANDLER TOGGLE TASK
        $(document).on('change', '.toggle-task-checkbox', function() {
            const colIdx = $(this).data('column');
            if ($(this).is(':checked')) { $(`.col-task-${colIdx}`).show(); } 
            else { $(`.col-task-${colIdx}`).hide(); }

            $('#toggle-all-tasks').prop('checked', $('.toggle-task-checkbox:checked').length === $('.toggle-task-checkbox').length);
            saveVisibility(taskStorageKey, '.toggle-task-checkbox');
        });

        $(document).on('change', '#toggle-all-tasks', function() {
            const isChecked = $(this).is(':checked');
            $('.toggle-task-checkbox').prop('checked', isChecked);
            if (isChecked) {
                $('[class*="col-task-"]').show();
                localStorage.removeItem(taskStorageKey);
            } else {
                $('[class*="col-task-"]').hide();
                saveVisibility(taskStorageKey, '.toggle-task-checkbox');
            }
        });

        $('#check-all-pem-students').change(function() {
    $('.pem-student-checkbox').prop('checked', $(this).is(':checked'));
});

$('#formBulkPresensi').submit(function(e) {
    e.preventDefault();

    let selectedStudents = [];
    $('.pem-student-checkbox:checked').each(function() {
        selectedStudents.push($(this).val());
    });

    if (selectedStudents.length === 0) {
        Swal.fire('Peringatan', 'Silakan pilih setidaknya satu siswa!', 'warning');
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Presensi Massal',
        text: `Presensi dan Jurnal Pembelajaran akan disimpan untuk ${selectedStudents.length} siswa terpilih!`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan!'
    }).then((res) => {
        if (res.isConfirmed) {
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: '{{ route("rekap.pembelajaran.bulk") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    classroom_id: '{{ $selectedClassroom }}',
                    subject_id: '{{ $selectedSubject }}',
                    student_ids: selectedStudents,
                    date: $('#bulk_date').val(),
                    status: $('#bulk_status').val(),
                    topic: $('#bulk_topic').val(),
                    activities: $('#bulk_activities').val(),
                    obstacles: $('#bulk_obstacles').val()
                },
                success: function(resp) {
                    Swal.fire('Berhasil!', resp.message, 'success').then(() => location.reload());
                },
                error: function(err) {
                    Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        }
    });
});
    });

    // Update Presensi Individual
    function updatePresensi(studentId, studentName, date, currentStatus, currentNote, currentAttach) {
        let attachHTML = currentAttach 
            ? `<p class="mb-2"><a href="/storage/${currentAttach}" target="_blank" class="btn btn-sm btn-outline-info">📄 Lihat Surat Lampiran Saat Ini</a></p>` 
            : '';

        Swal.fire({
            title: `Update Presensi & Catatan`,
            html: `
                <div class="text-start mb-2">
                    <p class="mb-1">Siswa: <strong>${studentName}</strong></p>
                    <p class="mb-2">Tanggal: <strong>${date}</strong></p>
                    
                    <label class="form-label fw-bold mb-1">Status Kehadiran:</label>
                    <select id="swal_status" class="form-select mb-3">
                        <option value="H" ${currentStatus === 'H' ? 'selected' : ''}>Hadir (H)</option>
                        <option value="S" ${currentStatus === 'S' ? 'selected' : ''}>Sakit (S)</option>
                        <option value="I" ${currentStatus === 'I' ? 'selected' : ''}>Izin (I)</option>
                        <option value="A" ${currentStatus === 'A' ? 'selected' : ''}>Alpa / Tanpa Keterangan (A)</option>
                    </select>

                    <label class="form-label fw-bold mb-1">Catatan / Alasan Izin/Sakit:</label>
                    <textarea id="swal_note" class="form-control mb-3" rows="2" placeholder="Tuliskan catatan khusus siswa...">${currentNote || ''}</textarea>

                    ${attachHTML}

                    <label class="form-label fw-bold mb-1">Upload/Ganti Surat dokter / Izin (PDF/Gambar):</label>
                    <input type="file" id="swal_attachment" class="form-control" accept="image/*,.pdf">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            preConfirm: () => {
                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('student_id', studentId);
                formData.append('subject_id', '{{ $selectedSubject }}');
                formData.append('date', date);
                formData.append('status', $('#swal_status').val());
                formData.append('note', $('#swal_note').val());
                
                let fileInput = $('#swal_attachment')[0].files[0];
                if (fileInput) { formData.append('attachment', fileInput); }

                return $.ajax({
                    url: '{{ route("rekap.presensi.update") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                }).catch(err => {
                    Swal.showValidationMessage(err.responseJSON?.message || 'Gagal menyimpan');
                });
            }
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1200, showConfirmButton: false })
                    .then(() => location.reload());
            }
        });
    }
</script>
</x-app-layout>