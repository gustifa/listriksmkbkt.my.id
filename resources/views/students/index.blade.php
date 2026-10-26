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

        .btn-view-mode {
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            transition: all 0.2s;
        }
        .btn-view-mode.active {
            background: #0d6efd !important;
            color: #ffffff !important;
            border-color: #0d6efd !important;
        }
    </style>

    <div class="student-page">
        <!-- Top Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-1">Data Murid & Registrasi Wajah</h4>
                <small class="text-muted">Kelola data seluruh siswa, kelas, dan status biometrik wajah</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- SWITCHER MODE CARD / TABEL -->
                <div class="btn-group shadow-sm rounded-3" role="group">
                    <button type="button" class="btn btn-view-mode" id="btn-mode-card" onclick="switchViewMode('card')">
                        <i class='bx bx-grid-alt me-1'></i> Card
                    </button>
                    <button type="button" class="btn btn-view-mode" id="btn-mode-table" onclick="switchViewMode('table')">
                        <i class='bx bx-list-ul me-1'></i> Tabel
                    </button>
                </div>

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

        <!-- Filter Form -->
        <div class="filter-card">
            <form id="filterForm" onsubmit="return false;">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-search text-muted'></i></span>
                            <input type="text" id="search" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Nama atau NIS..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <select id="classroom_id" name="classroom_id" class="form-select">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($classrooms as $class)
                                <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name ?? $class->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <select id="per_page" name="per_page" class="form-select">
                            <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 Data</option>
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 Data</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 Data</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Data</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Data</option>
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
            <div id="tableLoader" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none align-items-center justify-content-center" style="z-index: 10; border-radius: 14px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div id="tableContainer">
                @include('students.partials.student_table')
            </div>
        </div>
    </div>

    <!-- Script SweetAlert2 & AJAX -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let activeViewMode = window.innerWidth <= 768 ? 'card' : 'table';

        function switchViewMode(mode) {
            activeViewMode = mode;
            const cardView = document.getElementById('view-card-container');
            const tableView = document.getElementById('view-table-container');
            const btnCard = document.getElementById('btn-mode-card');
            const btnTable = document.getElementById('btn-mode-table');

            if (mode === 'card') {
                if (cardView) cardView.classList.remove('d-none');
                if (tableView) tableView.classList.add('d-none');
                if (btnCard) btnCard.classList.add('active');
                if (btnTable) btnTable.classList.remove('active');
            } else {
                if (tableView) tableView.classList.remove('d-none');
                if (cardView) cardView.classList.add('d-none');
                if (btnTable) btnTable.classList.add('active');
                if (btnCard) btnCard.classList.remove('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            switchViewMode(activeViewMode);

            const searchInput = document.getElementById('search');
            const classroomSelect = document.getElementById('classroom_id');
            const perPageSelect = document.getElementById('per_page');
            const resetBtn = document.getElementById('resetFilter');
            const tableContainer = document.getElementById('tableContainer');
            const loader = document.getElementById('tableLoader');

            let currentSortBy = "{{ request('sort_by', 'name') }}";
            let currentSortOrder = "{{ request('sort_order', 'asc') }}";
            let debounceTimer;

            function fetchStudents(url = "{{ route('students.index') }}") {
                if (loader) {
                    loader.classList.remove('d-none');
                    loader.classList.add('d-flex');
                }

                // Ambil nilai per_page secara langsung dari elemen DOM
                const perPageValue = perPageSelect ? perPageSelect.value : '10';
                const searchValue = searchInput ? searchInput.value : '';
                const classroomValue = classroomSelect ? classroomSelect.value : '';

                // Parsing URL jika merupakan link pagination
                let baseUrl = url;
                let urlObj;
                try {
                    urlObj = new URL(url, window.location.origin);
                    baseUrl = urlObj.origin + urlObj.pathname;
                } catch(e) {
                    baseUrl = url;
                }

                const params = new URLSearchParams(urlObj ? urlObj.search : '');
                params.set('search', searchValue);
                params.set('classroom_id', classroomValue);
                params.set('per_page', perPageValue);
                params.set('sort_by', currentSortBy);
                params.set('sort_order', currentSortOrder);

                const fetchUrl = `${baseUrl}?${params.toString()}`;

                fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Respon gagal');
                    return response.text();
                })
                .then(html => {
                    if (tableContainer) {
                        tableContainer.innerHTML = html;
                        switchViewMode(activeViewMode);
                    }
                })
                .catch(error => console.error('AJAX Error:', error))
                .finally(() => {
                    if (loader) {
                        loader.classList.remove('d-flex');
                        loader.classList.add('d-none');
                    }
                });
            }

            // Event Listeners
            if (perPageSelect) perPageSelect.addEventListener('change', () => fetchStudents());
            if (classroomSelect) classroomSelect.addEventListener('change', () => fetchStudents());

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => fetchStudents(), 300);
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    if (searchInput) searchInput.value = '';
                    if (classroomSelect) classroomSelect.value = '';
                    if (perPageSelect) perPageSelect.value = '10';
                    currentSortBy = 'name';
                    currentSortOrder = 'asc';
                    fetchStudents();
                });
            }

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

                const paginationLink = e.target.closest('#tableContainer .pagination a');
                if (paginationLink) {
                    e.preventDefault();
                    fetchStudents(paginationLink.href);
                }
            });
        });
    </script>
</x-app-layout>