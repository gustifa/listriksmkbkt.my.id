@section('title', 'Rekapitulasi Pengajuan Izin & Sakit Siswa')
<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .table-rekap { border-collapse: separate; border-spacing: 0; font-size: 11px; width: max-content; min-width: 100%; }
        .table-rekap th, .table-rekap td { border-right: 1px solid #333 !important; border-bottom: 1px solid #333 !important; text-align: center; vertical-align: middle; padding: 4px; background-color: #fff; }
        .table-rekap thead tr:first-child th { border-top: 1px solid #333 !important; }
        .table-rekap tr th:first-child, .table-rekap tr td:first-child { border-left: 1px solid #333 !important; }

        .cell-clickable { cursor: pointer; transition: background-color 0.2s; }
        .cell-clickable:hover { background-color: #d1e7dd !important; }
        .bg-zebra td { background-color: #f8f9fa !important; }
        .text-left { text-align: left !important; padding-left: 6px !important; }

        @media (min-width: 769px) {
            .table-rekap th.sticky-col, .table-rekap td.sticky-col { position: sticky; z-index: 2; }
            .sticky-col-1 { left: 0; min-width: 35px; }
            .sticky-col-2 { left: 35px; min-width: 85px; }
            .sticky-col-3 { left: 120px; min-width: 175px; box-shadow: 3px 0 5px -2px rgba(0,0,0,0.15); }
            .table-rekap thead th { position: sticky; top: 0; background-color: #f8f9fa !important; z-index: 3; }
            .table-rekap thead th.sticky-col { z-index: 4; }
        }
        @media print {
            .btn, form, modal, .modal, nav, header, footer, sidebar, .no-print, .dropdown { display: none !important; }
            body { background-color: #fff !important; padding: 0 !important; }
            @page { size: A4 landscape; margin: 10mm; }
            .table-rekap { width: 100% !important; font-size: 8pt !important; }
            [class*="col-date-"] { display: table-cell !important; }
        }
    </style>

<div class="container-fluid bg-white p-3 p-md-4 rounded shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold m-0">REKAPITULASI PENGAJUAN IZIN & SAKIT SISWA</h5>
            @if($activeAcademicYear)
                <span class="badge bg-success fs-6 mt-1">Tahun Ajaran: {{ $activeAcademicYear->year }} ({{ $activeAcademicYear->semester }})</span>
            @endif
        </div>
        @if($selectedClassroom)
        <div class="d-flex flex-wrap gap-2 no-print">
            <button type="button" class="btn btn-warning btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBulkIzin">
                ⚡ Catat Izin / Sakit Pilih Siswa
            </button>
            <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm">🖨️ Cetak Rekap</button>
        </div>
        @endif
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('rekap.izin.index') }}" class="row g-3 mb-4 no-print">
        <div class="col-12 col-md-6">
            <label class="form-label fw-bold">Pilih Kelas</label>
            <select name="classroom_id" id="classroom_id" class="form-select select2" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ $selectedClassroom == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <!-- Toolbar Filter Tanggal -->
    @if($selectedClassroom && $dates->count() > 0)
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2 gap-2 no-print">
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle fw-semibold shadow-sm" type="button" id="btnFilterKolom" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                👁️ Filter Tampilan Tanggal
            </button>
            <ul class="dropdown-menu p-3 shadow" style="max-height: 300px; overflow-y: auto; width: 280px; z-index: 1050;">
                <li class="fw-bold mb-2 text-primary border-bottom pb-1">Tampilkan Tanggal:</li>
                <li>
                    <div class="form-check fw-bold text-success mb-2">
                        <input class="form-check-input" type="checkbox" id="toggle-all-dates" checked>
                        <label class="form-check-label" for="toggle-all-dates">Pilih Semua / Reset</label>
                    </div>
                </li>
                @foreach($dates as $index => $d)
                <li>
                    <div class="form-check py-1">
                        <input class="form-check-input toggle-col-checkbox" type="checkbox" data-column="{{ $index }}" id="col-check-{{ $index }}" checked>
                        <label class="form-check-label text-truncate d-block cursor-pointer" for="col-check-{{ $index }}">
                            ({{ $index + 1 }}) {{ \Carbon\Carbon::parse($d->date)->format('d/m/Y') }}
                        </label>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        <small class="text-muted fst-italic">
            💡 Klik pada sel untuk melihat alasan, mengedit status/jenis izin, atau mengirim pesan WA.
        </small>
    </div>
    @endif

    <!-- Tabel Matriks Izin -->
    @if($selectedClassroom)
    <div class="table-responsive" style="max-height: 650px; overflow: auto;">
        <table class="table table-rekap">
            <thead>
                <tr>
                    <th rowspan="2" class="sticky-col sticky-col-1">NO</th>
                    <th rowspan="2" class="sticky-col sticky-col-2">INDUK</th>
                    <th rowspan="2" class="sticky-col sticky-col-3">NAMA SISWA</th>
                    
                    @forelse($dates as $index => $d)
                        <th class="px-2 col-date-{{ $index }}" title="{{ \Carbon\Carbon::parse($d->date)->isoFormat('D MMMM Y') }}">
                            {{ \Carbon\Carbon::parse($d->date)->format('d/m/Y') }}
                        </th>
                    @empty
                        <th rowspan="2" class="text-muted fst-italic">Belum ada catatan pengajuan izin di kelas ini.</th>
                    @endforelse

                    <th colspan="3">REKAP TOTAL</th>
                </tr>
                <tr>
                    @foreach($dates as $index => $d)
                        <th style="font-size: 10px;" class="col-date-{{ $index }}">Status / Alasan</th>
                    @endforeach
                    <th style="width: 30px;" class="text-info" title="Sakit">S</th>
                    <th style="width: 30px;" class="text-warning" title="Izin">I</th>
                    <th style="width: 35px;" class="text-primary" title="Total">JML</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapData as $index => $row)
                <tr class="{{ $index % 2 == 1 ? 'bg-zebra' : '' }}">
                    <td class="sticky-col sticky-col-1">{{ $index + 1 }}</td>
                    <td class="sticky-col sticky-col-2">{{ $row['nisn'] }}</td>
                    <td class="sticky-col sticky-col-3 text-left fw-bold">
                        {{ $row['nama'] }}
                        @if(!empty($row['phone']))
                            <a href="https://wa.me/{{ $row['phone'] }}?text=Halo%20Orang%20Tua%20dari%20{{ urlencode($row['nama']) }},%20memberitahukan%20rekap%20izin/sakit%20siswa..." 
                               target="_blank" class="no-print ms-1 text-success text-decoration-none" title="Chat OrtU WA">📱</a>
                        @endif
                    </td>

                    @foreach($dates as $dateIndex => $d)
                        @php $rec = $row['izin_list'][$d->date]; @endphp
                        <td class="cell-clickable col-date-{{ $dateIndex }}"
                            onclick="editIzin('{{ $row['student_id'] }}', '{{ addslashes($row['nama']) }}', '{{ $d->date }}', '{{ $rec['status'] }}', '{{ addslashes($rec['reason']) }}', '{{ $rec['wa'] ?: $row['phone'] }}')"
                            title="{{ $rec['reason'] ? 'Alasan: '.$rec['reason'] : 'Klik untuk edit' }}">
                            
                            @php 
                                $rLower = strtolower($rec['reason']); 
                                $stLower = strtolower($rec['status']);
                            @endphp

                            @if(str_contains($rLower, 'sakit') || $stLower == 'sakit')
                                <span class="badge bg-info" title="{{ $rec['reason'] }}">Sakit 📝</span>
                            @elseif($stLower == 'approved' || $stLower == 'disetujui' || $stLower == 'izin')
                                <span class="badge bg-warning text-dark" title="{{ $rec['reason'] }}">Izin 📝</span>
                            @elseif($stLower == 'pending')
                                <span class="badge bg-secondary" title="{{ $rec['reason'] }}">Pending</span>
                            @elseif(!empty($rec['status']) && $rec['status'] != '-')
                                <span class="badge bg-primary" title="{{ $rec['reason'] }}">{{ ucfirst($rec['status']) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endforeach

                    <td class="fw-bold text-info bg-light">{{ $row['total_sakit'] }}</td>
                    <td class="fw-bold text-warning bg-light">{{ $row['total_izin'] }}</td>
                    <td class="fw-bold text-primary bg-light">{{ $row['total_pengajuan'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<!-- MODAL CATAT IZIN / SAKIT DENGAN PILIH SISWA -->
@if($selectedClassroom)
<div class="modal fade no-print" id="modalBulkIzin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold">⚡ Pencatatan Izin / Sakit untuk Siswa Terpilih</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formBulkIzin">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" id="bulk_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">Status Pengajuan <span class="text-danger">*</span></label>
                            <!-- FIELD 1: STATUS PENGAJUAN (ENUM DATABASE) -->
                            <select id="bulk_status" class="form-select" required>
                                <option value="approved">Disetujui / Approved</option>
                                <option value="pending">Menunggu Konfirmasi (Pending)</option>
                                <option value="rejected">Ditolak (Rejected)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">Jenis Kehadiran <span class="text-danger">*</span></label>
                            <!-- FIELD 2: JENIS IZIN / SAKIT -->
                            <select id="bulk_jenis_izin" class="form-select" required>
                                <option value="izin">Izin (Dispen / Acara Keluarga)</option>
                                <option value="sakit">Sakit (Keterangan / Surat Dokter)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Alasan / Keterangan</label>
                        <textarea id="bulk_reason" class="form-control" rows="2" placeholder="Contoh: Demam tinggi, Dispen Lomba OSIS, Acara Keluarga"></textarea>
                    </div>

                    <!-- DAFTAR SISWA CHECKBOX -->
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <label class="form-label fw-bold text-primary m-0">Pilih Siswa Yang Izin / Sakit <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="check-all-students">
                                <label class="form-check-label fw-semibold text-dark cursor-pointer" for="check-all-students">Pilih Semua Siswa</label>
                            </div>
                        </div>

                        <div class="row g-2" style="max-height: 250px; overflow-y: auto;">
                            @foreach($rekapData as $s)
                            <div class="col-12 col-md-6">
                                <div class="form-check bg-white p-2 border rounded shadow-sm">
                                    <input class="form-check-input student-checkbox ms-1" type="checkbox" value="{{ $s['student_id'] }}" id="student-{{ $s['student_id'] }}">
                                    <label class="form-check-label fw-semibold text-truncate d-block ms-4 cursor-pointer" for="student-{{ $s['student_id'] }}">
                                        {{ $s['nama'] }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold">⚡ Simpan Data Terpilih</button>
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

        const storageKey = 'vis_izin_dates_' + '{{ $selectedClassroom }}';

        function restoreVisibility() {
            const savedState = localStorage.getItem(storageKey);
            if (savedState) {
                const hiddenCols = JSON.parse(savedState);
                $('.toggle-col-checkbox').each(function() {
                    const colIdx = $(this).data('column');
                    if (hiddenCols.includes(colIdx)) {
                        $(this).prop('checked', false);
                        $(`.col-date-${colIdx}`).hide();
                    } else {
                        $(this).prop('checked', true);
                        $(`.col-date-${colIdx}`).show();
                    }
                });
                $('#toggle-all-dates').prop('checked', $('.toggle-col-checkbox:checked').length === $('.toggle-col-checkbox').length);
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
            localStorage.setItem(storageKey, JSON.stringify(hiddenCols));
        }

        restoreVisibility();

        $(document).on('change', '.toggle-col-checkbox', function() {
            const colIndex = $(this).data('column');
            if ($(this).is(':checked')) { $(`.col-date-${colIndex}`).show(); } 
            else { $(`.col-date-${colIndex}`).hide(); }
            $('#toggle-all-dates').prop('checked', $('.toggle-col-checkbox:checked').length === $('.toggle-col-checkbox').length);
            saveVisibility();
        });

        $(document).on('change', '#toggle-all-dates', function() {
            const isChecked = $(this).is(':checked');
            $('.toggle-col-checkbox').prop('checked', isChecked);
            if (isChecked) {
                $('[class*="col-date-"]').show();
                localStorage.removeItem(storageKey);
            } else {
                $('[class*="col-date-"]').hide();
                saveVisibility();
            }
        });

        $('#check-all-students').change(function() {
            $('.student-checkbox').prop('checked', $(this).is(':checked'));
        });

        $('#formBulkIzin').submit(function(e) {
            e.preventDefault();

            let selectedStudents = [];
            $('.student-checkbox:checked').each(function() {
                selectedStudents.push($(this).val());
            });

            if (selectedStudents.length === 0) {
                Swal.fire('Peringatan', 'Silakan pilih setidaknya satu siswa!', 'warning');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Data',
                text: `Catatan izin/sakit akan disimpan dan disinkronkan ke presensi harian untuk ${selectedStudents.length} siswa terpilih!`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan!'
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: '{{ route("rekap.izin.bulk") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            classroom_id: '{{ $selectedClassroom }}',
                            student_ids: selectedStudents,
                            date: $('#bulk_date').val(),
                            status: $('#bulk_status').val(),
                            jenis_izin: $('#bulk_jenis_izin').val(),
                            reason: $('#bulk_reason').val()
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

    function editIzin(studentId, studentName, date, currentStatus, currentReason, currentWa) {
        let waBtn = currentWa 
            ? `<a href="https://wa.me/${currentWa}?text=Halo,%20memberitahukan%20status%20izin/sakit%20siswa%20${encodeURIComponent(studentName)}%20tanggal%20${date}:%20${encodeURIComponent(currentStatus)}" target="_blank" class="btn btn-sm btn-outline-success mb-3 w-100">📱 Kirim Pesan WA Ortu</a>`
            : '';

        let stLower = (currentStatus || '').toLowerCase();
        let rLower  = (currentReason || '').toLowerCase();
        let isSakit = stLower === 'sakit' || rLower.includes('sakit');

        Swal.fire({
            title: `Kelola Izin / Sakit Siswa`,
            html: `
                <div class="text-start mb-2 fs-6">
                    <p class="mb-1">Siswa: <strong>${studentName}</strong></p>
                    <p class="mb-3">Tanggal: <strong>${date}</strong></p>
                    
                    ${waBtn}

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold mb-1">Status Pengajuan:</label>
                            <select id="swal_status" class="form-select">
                                <option value="approved" ${stLower === 'approved' || stLower === 'disetujui' || stLower === 'izin' || stLower === 'sakit' ? 'selected' : ''}>Disetujui / Approved</option>
                                <option value="pending" ${stLower === 'pending' ? 'selected' : ''}>Menunggu (Pending)</option>
                                <option value="rejected" ${stLower === 'rejected' ? 'selected' : ''}>Ditolak (Rejected)</option>
                                <option value="Hapus" class="text-danger">❌ Hapus Catatan Ini</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold mb-1">Jenis Kehadiran:</label>
                            <select id="swal_jenis_izin" class="form-select">
                                <option value="izin" ${!isSakit ? 'selected' : ''}>Izin</option>
                                <option value="sakit" ${isSakit ? 'selected' : ''}>Sakit</option>
                            </select>
                        </div>
                    </div>

                    <label class="form-label fw-bold mb-1">Alasan / Keterangan:</label>
                    <textarea id="swal_reason" class="form-control mb-3" rows="2" placeholder="Tuliskan alasan...">${currentReason || ''}</textarea>

                    <label class="form-label fw-bold mb-1">Nomor WA Orang Tua/Siswa:</label>
                    <input type="text" id="swal_wa" class="form-control" placeholder="Contoh: 08123456789" value="${currentWa || ''}">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            preConfirm: () => {
                return $.ajax({
                    url: '{{ route("rekap.izin.update") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        student_id: studentId,
                        date: date,
                        status: $('#swal_status').val(),
                        jenis_izin: $('#swal_jenis_izin').val(),
                        reason: $('#swal_reason').val(),
                        wa_number: $('#swal_wa').val()
                    }
                }).catch(err => {
                    Swal.showValidationMessage(err.responseJSON?.message || 'Gagal menyimpan data');
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