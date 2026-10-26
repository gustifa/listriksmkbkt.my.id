<x-app-layout>
    @section('title', 'Data Seluruh Murid & Biometrik')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        .student-page {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            padding: 24px;
        }

        .filter-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 18px 20px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }

        .table-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            position: relative;
            overflow: visible !important;
            min-height: 300px;
        }

        .btn-action-dropdown {
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>

    <div class="student-page">
        <!-- Breadcrumb & Top Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Data Murid & Registrasi Wajah</h4>
                <small class="text-muted">Kelola data seluruh siswa, kelas, dan status biometrik wajah</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                @hasanyrole('admin|super-admin')
                <a href="{{ route('students.import') }}" class="btn btn-success btn-sm fw-semibold rounded-pill px-3">
                    <i class="bx bx-import me-1"></i> Import Excel
                </a>
                @endhasanyrole
                <a href="{{ route('students.export') }}" class="btn btn-warning btn-sm text-white fw-semibold rounded-pill px-3">
                    <i class="bx bx-export me-1"></i> Export
                </a>
            </div>
        </div>

        <!-- Filter Form (AJAX) -->
        <div class="filter-card">
            <form id="filterForm" onsubmit="return false;">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-search text-muted'></i></span>
                            <input type="text" id="search" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Nama atau NIS..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <select id="classroom_id" name="classroom_id" class="form-select">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($classrooms as $class)
                                <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name ?? $class->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="button" id="resetFilter" class="btn btn-secondary w-100 fw-semibold">
                            <i class="bx bx-refresh me-1"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card & Loader -->
        <div class="table-card">
            <!-- Loading Overlay -->
            <div id="tableLoader" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none align-items-center justify-content-center" style="z-index: 10; border-radius: 14px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <!-- Partial Table Container -->
            <div id="tableContainer">
                @include('students.partials.student_table')
            </div>
        </div>
    </div>

    <!-- Script SweetAlert2 & AJAX -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search');
            const classroomSelect = document.getElementById('classroom_id');
            const resetBtn = document.getElementById('resetFilter');
            const tableContainer = document.getElementById('tableContainer');
            const loader = document.getElementById('tableLoader');

            let currentSortBy = "{{ request('sort_by', 'name') }}";
            let currentSortOrder = "{{ request('sort_order', 'asc') }}";
            let debounceTimer;

            // 1. Fetch Data Siswa via AJAX
            function fetchStudents(url = "{{ route('students.index') }}") {
                loader.classList.remove('d-none');
                loader.classList.add('d-flex');

                const params = new URLSearchParams({
                    search: searchInput.value,
                    classroom_id: classroomSelect.value,
                    sort_by: currentSortBy,
                    sort_order: currentSortOrder
                });

                const fetchUrl = url.includes('?') ? `${url}&${params.toString()}` : `${url}?${params.toString()}`;

                fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                })
                .finally(() => {
                    loader.classList.remove('d-flex');
                    loader.classList.add('d-none');
                });
            }

            // 2. Sorting Handler saat Header Tabel Diklik
            document.addEventListener('click', function (e) {
                const sortHeader = e.target.closest('.btn-sort');
                if (sortHeader) {
                    const column = sortHeader.getAttribute('data-column');
                    
                    if (currentSortBy === column) {
                        currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSortBy = column;
                        currentSortOrder = 'asc';
                    }
                    
                    fetchStudents();
                }
            });

            // 3. Live Search
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchStudents();
                }, 300);
            });

            // 4. Filter Kelas
            classroomSelect.addEventListener('change', function () {
                fetchStudents();
            });

            // 5. Reset Filter
            resetBtn.addEventListener('click', function () {
                searchInput.value = '';
                classroomSelect.value = '';
                currentSortBy = 'name';
                currentSortOrder = 'asc';
                fetchStudents();
            });

            // 6. Pagination Intercept
            document.addEventListener('click', function (e) {
                const paginationLink = e.target.closest('#tableContainer .pagination a');
                if (paginationLink) {
                    e.preventDefault();
                    fetchStudents(paginationLink.href);
                }
            });

            // 7. Event Delegation Hapus Data dengan SweetAlert2
            document.addEventListener('click', function (e) {
                const deleteBtn = e.target.closest('.btn-delete-swal');
                if (deleteBtn) {
                    e.preventDefault();
                    const studentId = deleteBtn.getAttribute('data-id');
                    const studentName = deleteBtn.getAttribute('data-name');
                    const deleteUrl = "{{ url('students') }}/" + studentId;

                    Swal.fire({
                        title: 'Hapus Data Siswa?',
                        text: `Siswa "${studentName}" beserta akun terhubung akan dihapus secara permanen!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            loader.classList.remove('d-none');
                            loader.classList.add('d-flex');

                            fetch(deleteUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-HTTP-Method-Override': 'DELETE',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: data.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    fetchStudents();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: data.message || 'Terjadi kesalahan saat menghapus data.'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Gagal terhubung ke server.'
                                });
                            })
                            .finally(() => {
                                loader.classList.remove('d-flex');
                                loader.classList.add('d-none');
                            });
                        }
                    });
                }
            });

            @if(session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 2500, showConfirmButton: false });
            @endif
            @if(session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}", timer: 3000 });
            @endif
        });
    </script>
</x-app-layout>