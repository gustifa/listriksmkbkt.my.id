@section('title', 'Data Seluruh Murid & Biometrik')

<x-app-layout>
    <div class="page-content">
        <div class="col-md-12">
            <!-- Breadcrumb -->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Maping</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}"><i class="fas fa-users me-2"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Murid & Registrasi Wajah</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('students.import') }}" class="btn btn-success me-2">
                        <i class="bx bx-import"></i> Import Excel
                    </a>

                    <a href="{{ route('students.export') }}" class="shadow-sm btn btn-warning">
                        <i class="bx bx-export"></i> Export
                    </a>
                </div>
            </div>

            <!-- Filter Card (AJAX) -->
            <div class="border-0 shadow card mb-4">
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" id="search" name="search" class="form-control" placeholder="Cari Nama atau NIS..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select id="classroom_id" name="classroom_id" class="form-select">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($classrooms as $class)
                                        <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="resetFilter" class="btn btn-secondary w-100"><i class="bx bx-refresh"></i> Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card shadow border-0">
                <div class="card-body position-relative">
                    
                    <!-- Loading Spinner AJAX -->
                    <div id="tableLoader" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none align-items-center justify-content-center" style="z-index: 10;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div class="table-responsive" id="tableContainer">
                        @include('students.partials.student_table')
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script SweetAlert2 & AJAX Filter -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search');
            const classroomSelect = document.getElementById('classroom_id');
            const resetBtn = document.getElementById('resetFilter');
            const tableContainer = document.getElementById('tableContainer');
            const loader = document.getElementById('tableLoader');

            let debounceTimer;

            // --- 1. Fungsi Utama Fetch Data AJAX ---
            function fetchStudents(url = "{{ route('students.index') }}") {
                loader.classList.remove('d-none');
                loader.classList.add('d-flex');

                const search = searchInput.value;
                const classroomId = classroomSelect.value;

                const params = new URLSearchParams({
                    search: search,
                    classroom_id: classroomId
                });

                const fetchUrl = url.includes('?') ? `${url}&${params.toString()}` : `${url}?${params.toString()}`;

                fetch(fetchUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    bindDeleteEvents(); // Re-bind SweetAlert2 ke tombol hapus baru
                })
                .catch(error => {
                    console.error('Error AJAX:', error);
                })
                .finally(() => {
                    loader.classList.remove('d-flex');
                    loader.classList.add('d-none');
                });
            }

            // --- 2. Live Search dengan Debounce (Menunggu Selesai Ketik 300ms) ---
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchStudents();
                }, 300);
            });

            // --- 3. Filter Otomatis Saat Pilih Kelas ---
            classroomSelect.addEventListener('change', function () {
                fetchStudents();
            });

            // --- 4. Tombol Reset Filter ---
            resetBtn.addEventListener('click', function () {
                searchInput.value = '';
                classroomSelect.value = '';
                fetchStudents();
            });

            // --- 5. Pagination AJAX (Mencegah Page Reload Saat Klik Halaman) ---
            document.addEventListener('click', function (e) {
                const paginationLink = e.target.closest('#tableContainer .pagination a');
                if (paginationLink) {
                    e.preventDefault();
                    fetchStudents(paginationLink.href);
                }
            });

            // --- 6. SweetAlert2 Konfirmasi Hapus Data ---
            function bindDeleteEvents() {
                const deleteButtons = document.querySelectorAll('.btn-delete');

                deleteButtons.forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        const form = this.closest('.form-delete');

                        Swal.fire({
                            title: 'Apakah Anda yakin?',
                            text: "Data siswa yang dihapus tidak dapat dikembalikan!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            }

            // Inisialisasi binding awal
            bindDeleteEvents();

            // --- 7. Flash Message SweetAlert2 dari Session ---
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    timer: 2500,
                    showConfirmButton: false
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    timer: 3000,
                    showConfirmButton: true
                });
            @endif
        });
    </script>
</x-app-layout>