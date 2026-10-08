@extends('layouts.app')

@section('title', 'Pendaftaran & Kelola Wajah')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow border-0">
        <!-- HEADER HIJAU -->
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-users-cog me-2"></i> Pendaftaran & Kelola Wajah
            </h5>
            <div class="d-flex gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light fw-bold text-success">
                    <i class="fas fa-tachometer-alt me-1"></i> DASHBOARD
                </a>
                <a href="{{ route('gate.monitor') }}" class="btn btn-sm btn-outline-light fw-bold">
                    <i class="fas fa-door-open me-1"></i> MONITOR GERBANG
                </a>
            </div>
        </div>

        <div class="card-body p-3 p-md-4">
            <!-- FILTER KELAS & SEARCH -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fas fa-filter me-1"></i> Filter Kelas
                    </label>
                    <select id="filter-classroom" class="form-select shadow-none">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($classrooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-secondary">
                        <i class="fas fa-search me-1"></i> Cari Nama / NIS
                    </label>
                    <input type="text" id="search-student" class="form-control shadow-none" placeholder="Ketik nama atau NIS siswa...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="btn-reset" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                </div>
            </div>

            <!-- TABEL DAFTAR SISWA -->
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="10%">NIS</th>
                            <th width="25%">Nama Siswa</th>
                            <th width="15%">Kelas</th>
                            <th width="15%">Jumlah Sampel</th>
                            <th width="15%">Status</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="student-table-body">
                        <!-- Data siswa akan di-render via AJAX / Loop Blade -->
                        @include('face_registration._table')
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const classroomSelect = document.getElementById('filter-classroom');
    const searchInput = document.getElementById('search-student');
    const btnReset = document.getElementById('btn-reset');
    const tableBody = document.getElementById('student-table-body');

    let debounceTimer;

    function fetchStudents() {
        const classroomId = classroomSelect.value;
        const search = searchInput.value;

        // Ambil URL saat ini atau sesuaikan dengan route index Anda
        const url = new URL(window.location.href);
        if (classroomId) url.searchParams.set('classroom_id', classroomId);
        else url.searchParams.delete('classroom_id');

        if (search) url.searchParams.set('search', search);
        else url.searchParams.delete('search');

        // Tampilkan indikator loading ringan
        tableBody.style.opacity = '0.5';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            tableBody.innerHTML = html;
            tableBody.style.opacity = '1';
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            tableBody.style.opacity = '1';
        });
    }

    // Event Filter Kelas
    classroomSelect.addEventListener('change', fetchStudents);

    // Event Pencarian (Debounce 300ms agar tidak spam request)
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchStudents, 300);
    });

    // Event Reset
    btnReset.addEventListener('click', function () {
        classroomSelect.value = '';
        searchInput.value = '';
        fetchStudents();
    });
});
</script>
@endpush