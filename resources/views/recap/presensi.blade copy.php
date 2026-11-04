@section('title', 'Rekapitulasi Presensi & Jurnal Pembelajaran Siswa')
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
            <h5 class="fw-bold m-0">REKAPITULASI PRESENSI & JURNAL PEMBELAJARAN SISWA</h5>
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
    <form method="GET" action="{{ route('rekap.presensi.index') }}" class="row g-3 mb-4 no-print">
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

    <!-- Toolbar Filter Column & Legenda -->
    @if($selectedClassroom && $selectedSubject && $dates->count() > 0)
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
                            P-{{ $index + 1 }} ({{ \Carbon\Carbon::parse($d->date)->format('d/m/Y') }})
                        </label>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        <small class="text-muted fst-italic">
            📌 Keterangan Visual: <strong>📝</strong> Ada Catatan | <strong>📎</strong> Ada Surat/Lampiran | Latar Merah: Kehadiran <strong>< 75%</strong>
        </small>
    </div>
    @endif

    <!-- Tabel Matriks Utama -->
    @if($selectedClassroom && $selectedSubject)
    <div class="table-responsive" style="max-height: 650px; overflow: auto;">
        <table class="table table-rekap">
            <thead>
                <tr>
                    <th rowspan="2" class="sticky-col sticky-col-1">NO</th>
                    <th rowspan="2" class="sticky-col sticky-col-2">INDUK</th>
                    <th rowspan="2" class="sticky-col sticky-col-3">NAMA SISWA</th>
                    
                    @forelse($dates as $index => $d)
                        @php $jurnal = $jurnalMap[$d->date] ?? null; @endphp
                        <th class="header-vertical col-date-{{ $index }}" title="{{ $jurnal ? 'Materi: '.$jurnal->topic : 'Pertemuan ' . ($index+1) }}">
                            {{ \Carbon\Carbon::parse($d->date)->format('d/m/Y') }}
                        </th>
                    @empty
                        <th rowspan="2" class="text-muted fst-italic">Belum ada data presensi. Gunakan tombol "⚡ Presensi & Jurnal Massal".</th>
                    @endforelse

                    <th colspan="4">REKAP</th>
                    <th rowspan="2" style="width: 35px;" title="Total Catatan & Surat">NOTE</th>
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
                <tr class="{{ $row['percentage'] < 75 ? 'bg-warning-low' : ($index % 2 == 1 ? 'bg-zebra' : '') }}">
                    <td class="sticky-col sticky-col-1">{{ $index + 1 }}</td>
                    <td class="sticky-col sticky-col-2">{{ $row['nisn'] }}</td>
                    <td class="sticky-col sticky-col-3 text-left fw-bold">
                        {{ $row['nama'] }}
                        @if($row['phone'])
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $row['phone']) }}?text=Halo%20Orang%20Tua%20dari%20{{ urlencode($row['nama']) }},%20memberitahukan%20rekap%20kehadiran%20siswa..." 
                               target="_blank" class="no-print ms-1 text-success text-decoration-none" title="Kirim WA Orang Tua">📱</a>
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
                            @if(!empty($attach)) <span class="note-badge text-danger" title="Ada Lampiran Surat">📎</span> @endif
                        </td>
                    @endforeach

                    <td class="fw-bold bg-light text-success">{{ $row['total_h'] }}</td>
                    <td class="fw-bold bg-light text-info">{{ $row['total_s'] }}</td>
                    <td class="fw-bold bg-light text-warning">{{ $row['total_i'] }}</td>
                    <td class="fw-bold bg-light text-danger">{{ $row['total_a'] }}</td>
                    <td class="fw-bold bg-light text-primary">
                        @if($row['total_catatan'] > 0)
                            <span class="badge bg-primary" title="{{ $row['total_catatan'] }} Catatan/Surat tersimpan">{{ $row['total_catatan'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="fw-bold {{ $row['percentage'] < 75 ? 'text-danger' : 'text-primary' }}">{{ $row['percentage'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<!-- MODAL PRESENSI & JURNAL MASSAL -->
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
                                <option value="H">✓ Hadir Semua</option>
                                <option value="S">Sakit Semua</option>
                                <option value="I">Izin Semua</option>
                                <option value="A">Alpa / Belum Hadir</option>
                            </select>
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
                        <textarea id="bulk_obstacles" class="form-control" rows="2" placeholder="Contoh: 2 unit multimeter kurang presisi"></textarea>
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

        const storageKey = 'vis_dates_' + '{{ $selectedClassroom }}' + '_' + '{{ $selectedSubject }}';

        // RESTORE LOCALSTORAGE STATUS VISIBILITAS KOLOM TANGGAL
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

        // Submit Presensi Massal & Jurnal
        $('#formBulkPresensi').submit(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Presensi Massal',
                text: 'Presensi seluruh siswa dan Jurnal Pembelajaran akan disimpan!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan!'
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: '{{ route("rekap.presensi.bulk") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            classroom_id: '{{ $selectedClassroom }}',
                            subject_id: '{{ $selectedSubject }}',
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