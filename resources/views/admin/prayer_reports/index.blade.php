@extends('layouts.app')

@section('title', 'Rekap Absensi Sholat Siswa')

@section('content')
<div class="container-fluid py-3">
    <!-- Header Page & Action Buttons -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 d-print-none">
        <div>
            <h3 class="fw-bold text-dark mb-0">Rekap Absensi Sholat Siswa</h3>
            <p class="text-muted small mb-0">Laporan kebiasaan ibadah sholat fardhu dan sunnah harian siswa.</p>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <!-- Dropdown Menu Export -->
            <div class="dropdown">
                <button class="btn btn-success fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-export me-1"></i> Export Data
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li>
                        <a class="dropdown-item py-2" id="btn-export-excel" href="#">
                            <i class="fas fa-file-excel text-success me-2"></i> Export Terpilih ke Excel (.xlsx)
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.print()">
                            <i class="fas fa-print text-danger me-2"></i> Cetak / Simpan PDF
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filter Form (AJAX Mode) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 d-print-none">
        <div class="card-body p-3">
            <form id="filter-form" class="row g-3 align-items-end" onsubmit="return false;">
                <!-- Filter Nama Siswa -->
                <div class="col-md-3 col-6">
                    <label class="form-label fw-semibold text-secondary small">Cari Nama Siswa</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="search_name" id="filter-search_name" class="form-control border-start-0" placeholder="Ketik nama siswa..." value="{{ $searchName }}">
                    </div>
                </div>

                <!-- Filter Kelas -->
                <div class="col-md-2 col-6">
                    <label class="form-label fw-semibold text-secondary small">Filter Kelas</label>
                    <select name="classroom_id" id="filter-classroom_id" class="form-select">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>
                                {{ $cls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Waktu Sholat -->
                <div class="col-md-2 col-6">
                    <label class="form-label fw-semibold text-secondary small">Waktu Sholat</label>
                    <select name="prayer_name" id="filter-prayer_name" class="form-select">
                        <option value="">-- Semua Sholat --</option>
                        <option value="subuh" {{ $prayerName == 'subuh' ? 'selected' : '' }}>Subuh</option>
                        <option value="dzuhur" {{ $prayerName == 'dzuhur' ? 'selected' : '' }}>Dzuhur</option>
                        <option value="ashar" {{ $prayerName == 'ashar' ? 'selected' : '' }}>Ashar</option>
                        <option value="maghrib" {{ $prayerName == 'maghrib' ? 'selected' : '' }}>Maghrib</option>
                        <option value="isya" {{ $prayerName == 'isya' ? 'selected' : '' }}>Isya</option>
                        <option value="tarawih" {{ $prayerName == 'tarawih' ? 'selected' : '' }}>Tarawih</option>
                    </select>
                </div>

                <!-- Filter Tanggal -->
                <div class="col-md-2 col-6">
                    <label class="form-label fw-semibold text-secondary small">Dari Tanggal</label>
                    <input type="date" name="start_date" id="filter-start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label fw-semibold text-secondary small">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="filter-end_date" class="form-control" value="{{ $endDate }}">
                </div>

                <!-- Reset -->
                <div class="col-md-1 col-12 d-grid">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" onclick="resetFilter()" title="Reset Filter">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Header Laporan Saat Cetak/PDF -->
    <div class="d-none d-print-block text-center mb-4">
        <h4 class="fw-bold mb-1">REKAPITULASI ABSENSI IBADAH SHOLAT SISWA</h4>
        <p class="mb-0">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
        <hr class="my-3">
    </div>

    <!-- Tabel Matrix Rekap Absensi -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive" id="recap-table-container">
                @include('admin.prayer_reports.table_partial')
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body { background-color: #fff !important; }
    .sidebar, .navbar, .btn, .d-print-none { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .table-responsive { overflow: visible !important; }
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    let filterTimer;

    $(document).ready(function() {
        // Event Listener Live Search Debounce
        $('#filter-search_name').on('keyup input', function() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(fetchRecapData, 300);
        });

        // Event Listener Dropdown & Input Tanggal
        $('#filter-classroom_id, #filter-prayer_name, #filter-start_date, #filter-end_date').on('change', function() {
            fetchRecapData();
        });

        updateExportLink();
    });

    function fetchRecapData() {
        const searchName  = $('#filter-search_name').val();
        const classId     = $('#filter-classroom_id').val();
        const prayerName  = $('#filter-prayer_name').val();
        const startDate   = $('#filter-start_date').val();
        const endDate     = $('#filter-end_date').val();

        $('#recap-table-container').css('opacity', '0.5');

        $.ajax({
            url: "{{ route('admin.prayer_reports.index') }}",
            type: "GET",
            data: {
                search_name: searchName,
                classroom_id: classId,
                prayer_name: prayerName,
                start_date: startDate,
                end_date: endDate
            },
            success: function(htmlPartial) {
                $('#recap-table-container').html(htmlPartial).css('opacity', '1');
                updateExportLink();
            },
            error: function() {
                $('#recap-table-container').css('opacity', '1');
            }
        });
    }

    function toggleSelectAll(source) {
        $('.student-checkbox').prop('checked', source.checked);
        updateExportLink();
    }

    function updateExportLink() {
        const selectedIds = [];

        $('.student-checkbox').each(function() {
            const row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                selectedIds.push($(this).val());
                row.removeClass('d-print-none');
            } else {
                row.addClass('d-print-none');
            }
        });

        const searchName = $('#filter-search_name').val();
        const classId    = $('#filter-classroom_id').val();
        const prayerName = $('#filter-prayer_name').val();
        const startDate  = $('#filter-start_date').val();
        const endDate    = $('#filter-end_date').val();

        const excelUrl = "{{ route('admin.prayer_reports.excel') }}?" + $.param({
            search_name: searchName,
            classroom_id: classId,
            prayer_name: prayerName,
            start_date: startDate,
            end_date: endDate,
            student_ids: selectedIds.join(',')
        });

        $('#btn-export-excel').attr('href', excelUrl);
    }

    function resetFilter() {
        $('#filter-search_name').val('');
        $('#filter-classroom_id').val('');
        $('#filter-prayer_name').val('');
        $('#filter-start_date').val('{{ \Carbon\Carbon::now()->startOfMonth()->toDateString() }}');
        $('#filter-end_date').val('{{ \Carbon\Carbon::now()->toDateString() }}');
        fetchRecapData();
    }
</script>
@endsection