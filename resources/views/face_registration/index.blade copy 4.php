@extends('layouts.app')

@section('title', 'Pendaftaran & Kelola Wajah')

@section('content')
<style>
    body {
        background-color: #f8f9fa;
    }

    /* Handling safe-area iOS */
    @supports (padding: max(0px)) {
        body {
            padding-left: min(0px, env(safe-area-inset-left));
            padding-right: min(0px, env(safe-area-inset-right));
            padding-bottom: min(0px, env(safe-area-inset-bottom));
        }
    }

    @media (max-width: 575.98px) {
        .container {
            padding-left: 10px;
            padding-right: 10px;
        }

        .card-header-responsive {
            flex-direction: column;
            gap: 12px;
            text-align: center;
        }

        .header-buttons {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .header-buttons .btn {
            width: 100%;
        }
    }
</style>

<div class="container py-3 py-md-4">
    <div class="card shadow-sm border-0 rounded-3">
        
        <div class="card-header bg-success text-white card-header-responsive d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold fs-6 fs-md-5">
                <i class="fas fa-users-cog me-2"></i> Pendaftaran & Kelola Wajah
            </h5>
            <div class="header-buttons d-flex gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light text-success fw-bold">
                    <i class="fas fa-tachometer-alt me-1"></i> DASHBOARD
                </a>
                <a href="{{ route('gate.monitor') }}" class="btn btn-sm btn-outline-light fw-bold">
                    <i class="fas fa-camera me-1"></i> MONITOR GERBANG
                </a>
            </div>
        </div>

        <div class="card-body bg-light border-bottom p-3 p-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <form id="filter-form" class="row g-2 g-md-3" onsubmit="return false;">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-secondary">
                                <i class="fas fa-school me-1"></i> Filter Kelas
                            </label>
                            <select name="classroom_id" id="classroom_id" class="form-select shadow-none">
                                <option value="">-- Semua Kelas --</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-secondary">
                                <i class="fas fa-search me-1"></i> Cari Nama / NIS
                            </label>
                            <div class="input-group">
                                <input type="text" name="search" id="search-input" class="form-control shadow-none" placeholder="Ketik nama atau NIS siswa..." autocomplete="off">
                                <span class="input-group-text bg-white" id="search-loading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm text-primary"></span>
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-md-2 d-flex align-items-end">
                            <button type="button" id="btn-reset" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo me-1"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div id="table-container">
                @include('face_registration._table')
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
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

    // Debounce Live Search Nama/NIS (300ms)
    $('#search-input').on('keyup input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            fetchStudents(1);
        }, 300);
    });

    // Event Change Dropdown Kelas
    $('#classroom_id').on('change', function() {
        fetchStudents(1);
    });

    // Pagination Click Handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetchStudents(page);
    });

    // Reset Filter Button
    $('#btn-reset').on('click', function() {
        $('#classroom_id').val('');
        $('#search-input').val('');
        fetchStudents(1);
    });
});
</script>
@endpush