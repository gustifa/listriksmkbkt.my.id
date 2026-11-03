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
        .note-badge { font-size: 10px; cursor: pointer; }

        @media (min-width: 769px) {
            .table-rekap th.sticky-col, .table-rekap td.sticky-col { position: sticky; z-index: 2; }
            .sticky-col-1 { left: 0; min-width: 35px; }
            .sticky-col-2 { left: 35px; min-width: 85px; }
            .sticky-col-3 { left: 120px; min-width: 175px; box-shadow: 3px 0 5px -2px rgba(0,0,0,0.15); }
            .table-rekap thead th { position: sticky; top: 0; background-color: #f8f9fa !important; z-index: 3; }
            .table-rekap thead th.sticky-col { z-index: 4; }
        }
        @media print {
            .btn, form, modal, .modal, nav, header, footer, sidebar, .no-print, .nav-tabs { display: none !important; }
            body { background-color: #fff !important; padding: 0 !important; }
            @page { size: A4 landscape; margin: 10mm; }
            .tab-pane { display: block !important; opacity: 1 !important; visibility: visible !important; }
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
            <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                <table class="table table-rekap">
                    <thead>
                        <tr>
                            <th rowspan="2" class="sticky-col sticky-col-1">NO</th>
                            <th rowspan="2" class="sticky-col sticky-col-2">INDUK</th>
                            <th rowspan="2" class="sticky-col sticky-col-3">NAMA SISWA</th>
                            
                            @forelse($dates as $index => $d)
                                <th class="header-vertical col-date-{{ $index }}">
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

                                    @if(!empty($catatan)) <span class="note-badge text-primary">📝</span> @endif
                                    @if(!empty($attach)) <span class="note-badge text-danger">📎</span> @endif
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
            <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                <table class="table table-rekap">
                    <thead>
                        <tr>
                            <th rowspan="2" class="sticky-col sticky-col-1">NO</th>
                            <th rowspan="2" class="sticky-col sticky-col-2">INDUK</th>
                            <th rowspan="2" class="sticky-col sticky-col-3">NAMA SISWA</th>
                            
                            @forelse($assignments as $asgIndex => $asg)
                                <th class="header-vertical">
                                    {{ $asg->title }}
                                </th>
                            @empty
                                <th rowspan="2" class="text-muted fst-italic">Belum ada tugas dibuat.</th>
                            @endforelse

                            <th rowspan="2" style="width: 60px;">RATA-RATA</th>
                        </tr>
                        <tr>
                            @foreach($assignments as $asgIndex => $asg)
                                <th style="font-size: 10px;">T-{{ $asgIndex + 1 }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapData as $index => $row)
                        <tr class="{{ $index % 2 == 1 ? 'bg-zebra' : '' }}">
                            <td class="sticky-col sticky-col-1">{{ $index + 1 }}</td>
                            <td class="sticky-col sticky-col-2">{{ $row['nisn'] }}</td>
                            <td class="sticky-col sticky-col-3 text-left fw-bold">{{ $row['nama'] }}</td>

                            @foreach($assignments as $asg)
                                @php $grade = $row['tugas'][$asg->id] ?? null; @endphp
                                <td class="fw-bold {{ is_null($grade) ? 'text-muted' : ($grade < 70 ? 'text-danger' : 'text-dark') }}">
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

<!-- Include Modal Bulk Presensi & Script JS (Sama seperti sebelumnya) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    });
</script>
</x-app-layout>