@section('title', 'Rekapitulasi Penyerahan Tugas Siswa')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<x-app-layout>
    <style>
        .table-rekap {
            border-collapse: collapse;
            font-size: 11px;
        }
        .table-rekap th, .table-rekap td {
            border: 1px solid #333 !important;
            text-align: center;
            vertical-align: middle;
            padding: 4px;
        }
        .header-vertical {
            writing-mode: vertical-lr;
            transform: rotate(180deg);
            white-space: nowrap;
            height: 140px;
            padding: 5px 2px !important;
            cursor: pointer;
        }
        .header-vertical:hover { background-color: #e9ecef; }
        .cell-clickable { cursor: pointer; font-size: 12px; }
        .cell-clickable:hover { background-color: #d1e7dd !important; }
        .bg-zebra { background-color: #f8f9fa; }
        .text-left { text-align: left !important; padding-left: 6px !important; }
        .tp-badge {
            font-size: 9px;
            background-color: #0d6efd;
            color: #fff;
            padding: 2px 4px;
            border-radius: 3px;
            display: block;
            margin-bottom: 2px;
        }

        /* CSS KHUSUS MEDIA PRINT / CETAK PDF */
        @media print {
            .btn, form, modal, .modal, nav, header, footer, sidebar, .no-print {
                display: none !important;
            }
            body {
                background-color: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .container-fluid {
                box-shadow: none !important;
                padding: 0 !important;
            }
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            .table-rekap {
                width: 100% !important;
                font-size: 9pt !important;
            }
            .header-vertical {
                writing-mode: vertical-lr;
                transform: rotate(180deg);
                height: 120px !important;
            }
        }
    </style>
</head>
<body class="p-2 p-md-4 bg-light">

<div class="container-fluid bg-white p-3 p-md-4 rounded shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold m-0">REKAPITULASI PENYERAHAN TUGAS SISWA</h5>
            @if($activeAcademicYear)
                <span class="badge bg-success fs-6 mt-1">Tahun Ajaran: {{ $activeAcademicYear->year }} ({{ $activeAcademicYear->semester }})</span>
            @endif
        </div>

        <!-- Tombol Cetak & Export Excel di Pojok Kanan Header (Tanpa Merusak Filter Form) -->
        @if($selectedClassroom && $selectedSubject)
        <div class="d-flex gap-2 no-print">
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

    <!-- Filter Form (Tampilan Asli Dipertahankan) -->
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

    <!-- Tabel Rekapitulasi Grid Matriks -->
    @if($selectedClassroom && $selectedSubject)
    <div class="table-responsive">
        <table class="table table-rekap">
            <thead class="bg-light">
                <tr>
                    <th rowspan="2" style="width: 30px;">NO</th>
                    <th rowspan="2" style="width: 90px;">INDUK SISWA</th>
                    <th rowspan="2" style="min-width: 200px;">NAMA PESERTA DIDIK</th>
                    
                    @forelse($assignments as $assignment)
                        <th class="header-vertical" onclick="showAssignmentInfo('{{ $assignment->id }}')" title="Klik untuk lihat detail tugas">
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
                        <th style="font-size: 10px;">{{ $index + 1 }}</th>
                    @endforeach
                    <th style="width: 25px;" class="text-success">H</th>
                    <th style="width: 25px;" class="text-warning">T</th>
                    <th style="width: 25px;" class="text-danger">A</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapData as $index => $row)
                <tr class="{{ $index % 2 == 1 ? 'bg-zebra' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['nisn'] }}</td>
                    <td class="text-left fw-bold">{{ $row['nama'] }}</td>

                    @foreach($assignments as $assignment)
                        @php $status = $row['tugas'][$assignment->id] ?? 'A'; @endphp
                        <td class="cell-clickable" 
                            onclick="updateStatus('{{ $assignment->id }}', '{{ $row['student_id'] }}', '{{ addslashes($row['nama']) }}', '{{ $status }}')">
                            @if($status == 'H')
                                <span class="text-success fw-bold">✓</span>
                            @elseif($status == 'T')
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

<!-- ================= MODAL 1: TAMBAH TP ================= -->
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
                        <textarea id="tp_description" class="form-control" rows="3" placeholder="Contoh: Menganalisis rangkaian listrik arus searah..." required></textarea>
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

<!-- ================= MODAL 2: TAMBAH TUGAS ================= -->
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

<!-- JS Script Dependencies -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
        
        // Select2 di dalam Modal Bootstrap
        $('.select2-modal').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalTugas'),
            width: '100%'
        });

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // 1. Submit Form TP via AJAX
        $('#formTambahTP').submit(function(e) {
            e.preventDefault();
            const subjectId = $('#subject_id').val();

            $.ajax({
                url: '{{ route("rekap.tp.store") }}',
                type: 'POST',
                data: {
                    subject_id: subjectId,
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

        // 2. Submit Form Tugas via AJAX
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
    });

    // 3. Modal Detail Informasi Tugas
    // Info Detail Tugas & TP (SweetAlert2)
    function showAssignmentInfo(assignmentId) {
        Swal.fire({ 
            title: 'Memuat data...', 
            allowOutsideClick: false, 
            didOpen: () => { Swal.showLoading(); } 
        });

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
            error: function() {
                Swal.fire('Error', 'Gagal memuat detail data tugas.', 'error');
            }
        });
    }

    // 4. Update Status Ceklis via SweetAlert2
    function updateStatus(assignmentId, studentId, studentName, currentStatus) {
        Swal.fire({
            title: `Update Status Tugas`,
            html: `Status tugas siswa: <strong>${studentName}</strong>`,
            input: 'select',
            inputOptions: {
                'H': '✓ Hadir (Tepat Waktu)',
                'T': '✓ (T) Terlambat',
                'A': 'X Alpa / Belum Mengumpulkan'
            },
            inputValue: currentStatus,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            preConfirm: (newStatus) => {
                return $.ajax({
                    url: '{{ route("rekap.tugas.toggle") }}',
                    type: 'POST',
                    data: { assignment_id: assignmentId, student_id: studentId, status: newStatus }
                }).catch(error => {
                    Swal.showValidationMessage(`Gagal memperbarui status`);
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        });
    }
</script>

</x-app-layout>