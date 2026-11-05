@section('title', 'Rekapitulasi Presensi Datang & Pulang Siswa')
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
        .time-box { font-size: 9px; display: block; line-height: 1.1; }
        .time-in { color: #198754; font-weight: bold; }
        .time-out { color: #dc3545; font-weight: bold; }
        .bg-warning-low { background-color: #f8d7da !important; color: #721c24 !important; }
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
            <h5 class="fw-bold m-0">REKAPITULASI PRESENSI HARIAN (DATANG & PULANG)</h5>
            @if($activeAcademicYear)
                <span class="badge bg-success fs-6 mt-1">Tahun Ajaran: {{ $activeAcademicYear->year }} ({{ $activeAcademicYear->semester }})</span>
            @endif
        </div>
        @if($selectedClassroom)
        <div class="d-flex flex-wrap gap-2 no-print">
            <button type="button" class="btn btn-warning btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBulkHarian">
                ⚡ Presensi Massal
            </button>
            <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm">🖨️ Cetak Rekap</button>
        </div>
        @endif
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('rekap.harian.index') }}" class="row g-3 mb-4 no-print">
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

    <!-- Toolbar Sembunyikan / Tampilkan Kolom Tanggal -->
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
            📌 Latar Merah: Kehadiran <strong>< 75%</strong> | Klik sel jam untuk mengedit individual.
        </small>
    </div>
    @endif

    <!-- Tabel Matriks Harian -->
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
                        <th rowspan="2" class="text-muted fst-italic">Belum ada data presensi harian pada kelas ini.</th>
                    @endforelse

                    <th colspan="5">REKAP TOTAL</th>
                    <th rowspan="2" style="width: 45px;">% HADIR</th>
                </tr>
                <tr>
                    @foreach($dates as $index => $d)
                        <th style="font-size: 10px;" class="col-date-{{ $index }}">In / Out</th>
                    @endforeach
                    <th style="width: 25px;" class="text-success" title="Hadir Tepat Waktu">H</th>
                    <th style="width: 25px;" class="text-warning" title="Terlambat">T</th>
                    <th style="width: 25px;" class="text-info" title="Sakit">S</th>
                    <th style="width: 25px;" class="text-secondary" title="Izin">I</th>
                    <th style="width: 25px;" class="text-danger" title="Alpa">A</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapData as $index => $row)
                <tr class="{{ $row['percentage'] < 75 ? 'bg-warning-low' : ($index % 2 == 1 ? 'bg-zebra' : '') }}">
                    <td class="sticky-col sticky-col-1">{{ $index + 1 }}</td>
                    <td class="sticky-col sticky-col-2">{{ $row['nisn'] }}</td>
                    <td class="sticky-col sticky-col-3 text-left fw-bold">
                        {{ $row['nama'] }}
                        @if(!empty($row['phone']))
                            <a href="https://wa.me/{{ $row['phone'] }}?text=Halo%20Orang%20Tua%20dari%20{{ urlencode($row['nama']) }},%20memberitahukan%20rekap%20presensi%20harian%20siswa..." 
                               target="_blank" class="no-print ms-1 text-success text-decoration-none" title="Chat OrtU WA">📱</a>
                        @endif
                    </td>

                    @foreach($dates as $dateIndex => $d)
                        @php $rec = $row['daily'][$d->date]; @endphp
                        <td class="cell-clickable col-date-{{ $dateIndex }}"
                            onclick="editHarian('{{ $row['student_id'] }}', '{{ addslashes($row['nama']) }}', '{{ $d->date }}', '{{ $rec['status'] }}', '{{ $rec['raw_arrival'] }}', '{{ $rec['raw_departure'] }}')"
                            title="Klik untuk edit presensi tanggal {{ $d->date }}">
                            
                            @if($rec['status'] == 'H' || $rec['status'] == 'T')
                                <span class="time-box time-in">In: {{ $rec['in'] }}</span>
                                <span class="time-box time-out">Out: {{ $rec['out'] }}</span>
                                @if($rec['status'] == 'T') <span class="badge bg-warning text-dark p-0 px-1" style="font-size:8px;">Terlambat</span> @endif
                            @elseif($rec['status'] == 'S')
                                <span class="badge bg-info">Sakit</span>
                            @elseif($rec['status'] == 'I')
                                <span class="badge bg-secondary">Izin</span>
                            @else
                                <span class="text-danger fw-bold">A</span>
                            @endif
                        </td>
                    @endforeach

                    <td class="fw-bold text-success bg-light">{{ $row['total_h'] }}</td>
                    <td class="fw-bold text-warning bg-light">{{ $row['total_t'] }}</td>
                    <td class="fw-bold text-info bg-light">{{ $row['total_s'] }}</td>
                    <td class="fw-bold text-secondary bg-light">{{ $row['total_i'] }}</td>
                    <td class="fw-bold text-danger bg-light">{{ $row['total_a'] }}</td>
                    <td class="fw-bold {{ $row['percentage'] < 75 ? 'text-danger' : 'text-primary' }} bg-light">{{ $row['percentage'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<!-- MODAL PRESENSI HARIAN MASSAL -->
@if($selectedClassroom)
<div class="modal fade no-print" id="modalBulkHarian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold">⚡ Presensi Harian Massal (Datang & Pulang)</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formBulkHarian">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" id="bulk_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status Kehadiran Massal <span class="text-danger">*</span></label>
                        <select id="bulk_status" class="form-select" required>
                            <option value="H">✓ Hadir Tepat Waktu Semua</option>
                            <option value="T">Terlambat Semua</option>
                            <option value="S">Sakit Semua</option>
                            <option value="I">Izin Semua</option>
                            <option value="A">Alpa / Tanpa Keterangan</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Jam Datang (In)</label>
                            <input type="time" id="bulk_arrival" class="form-control" value="07:00">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Jam Pulang (Out)</label>
                            <input type="time" id="bulk_departure" class="form-control" value="15:00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold">⚡ Terapkan Massal</button>
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

        const storageKey = 'vis_harian_dates_' + '{{ $selectedClassroom }}';

        // RESTORE LOCALSTORAGE VISIBILITAS KOLOM TANGGAL
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

        // TOGGLE FILTER TANGGAL
        $(document).on('change', '.toggle-col-checkbox', function() {
            const colIndex = $(this).data('column');
            if ($(this).is(':checked')) { 
                $(`.col-date-${colIndex}`).show(); 
            } else { 
                $(`.col-date-${colIndex}`).hide(); 
            }
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

        // SUBMIT PRESENSI HARIAN MASSAL
        $('#formBulkHarian').submit(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Presensi Massal',
                text: 'Presensi harian seluruh siswa akan disimpan!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan!'
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: '{{ route("rekap.harian.bulk") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            classroom_id: '{{ $selectedClassroom }}',
                            date: $('#bulk_date').val(),
                            status: $('#bulk_status').val(),
                            arrival_time: $('#bulk_arrival').val(),
                            departure_time: $('#bulk_departure').val()
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

    // EDIT ABSENSI HARIAN INDIVIDUAL VIA SWEETALERT2
    function editHarian(studentId, studentName, date, currentStatus, currentIn, currentOut) {
        Swal.fire({
            title: `Edit Presensi Harian`,
            html: `
                <div class="text-start mb-2 fs-6">
                    <p class="mb-1">Siswa: <strong>${studentName}</strong></p>
                    <p class="mb-3">Tanggal: <strong>${date}</strong></p>
                    
                    <label class="form-label fw-bold mb-1">Status Kehadiran:</label>
                    <select id="swal_status" class="form-select mb-3">
                        <option value="H" ${currentStatus === 'H' ? 'selected' : ''}>Hadir Tepat Waktu (H)</option>
                        <option value="T" ${currentStatus === 'T' ? 'selected' : ''}>Terlambat (T)</option>
                        <option value="S" ${currentStatus === 'S' ? 'selected' : ''}>Sakit (S)</option>
                        <option value="I" ${currentStatus === 'I' ? 'selected' : ''}>Izin (I)</option>
                        <option value="A" ${currentStatus === 'A' ? 'selected' : ''}>Alpa (A)</option>
                    </select>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold mb-1">Jam Datang (In):</label>
                            <input type="time" id="swal_arrival" class="form-control" value="${currentIn || ''}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold mb-1">Jam Pulang (Out):</label>
                            <input type="time" id="swal_departure" class="form-control" value="${currentOut || ''}">
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            preConfirm: () => {
                return $.ajax({
                    url: '{{ route("rekap.harian.update") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        student_id: studentId,
                        date: date,
                        status: $('#swal_status').val(),
                        arrival_time: $('#swal_arrival').val(),
                        departure_time: $('#swal_departure').val()
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