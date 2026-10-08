<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pengelolaan Data Wajah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-users-cog me-2"></i> Pendaftaran & Kelola Wajah Siswa</h5>
            <div>
                <!-- TOMBOL KEMBALI KE DASHBOARD -->
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light text-success fw-bold me-2">
                    <i class="fas fa-tachometer-alt me-1"></i> DASHBOARD
                </a>
                <!-- TOMBOL BUKA MONITOR GERBANG -->
                <a href="{{ route('gate.monitor') }}" class="btn btn-sm btn-outline-light fw-bold">
                    <i class="fas fa-camera me-1"></i> MONITOR GERBANG
                </a>
            </div>
        </div>
        <div class="card-body">

            <!-- FORM FILTER AJAX -->
            <form id="filter-form" class="row g-3 mb-4" onsubmit="return false;">
                <div class="col-md-4">
                    <label class="form-label fw-bold"><i class="fas fa-school me-1"></i> Filter Kelas</label>
                    <select name="classroom_id" id="classroom_id" class="form-select shadow-none">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fas fa-search me-1"></i> Cari Nama / NIS</label>
                    <div class="input-group">
                        <input type="text" name="search" id="search-input" class="form-control shadow-none" placeholder="Ketik nama atau NIS siswa..." autocomplete="off">
                        <span class="input-group-text bg-white" id="search-loading" style="display: none;">
                            <span class="spinner-border spinner-border-sm text-primary"></span>
                        </span>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="btn-reset" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </button>
                </div>
            </form>

            <!-- WADAH TABEL HASIL AJAX -->
            <div id="table-container">
                @include('face_registration._table')
            </div>

        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    let searchTimer;

    function fetchStudents(page = 1) {
        $('#search-loading').show();
        const classroomId = $('#classroom_id').val();
        const search = $('#search-input').val();

        $.ajax({
            url: "{{ route('face.index') }}",
            type: "GET",
            data: {
                page: page,
                classroom_id: classroomId,
                search: search
            },
            success: function(response) {
                $('#table-container').html(response);
                $('#search-loading').hide();
            },
            error: function() {
                $('#search-loading').hide();
            }
        });
    }

    $('#search-input').on('keyup input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            fetchStudents(1);
        }, 300);
    });

    $('#classroom_id').on('change', function() {
        fetchStudents(1);
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetchStudents(page);
    });

    $('#btn-reset').on('click', function() {
        $('#classroom_id').val('');
        $('#search-input').val('');
        fetchStudents(1);
    });
});
</script>
</body>
</html>
