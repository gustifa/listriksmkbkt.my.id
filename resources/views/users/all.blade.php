@section('title', 'Data User & Hak Akses')

<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .filter-card { background: #ffffff; border-radius: 12px; padding: 16px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); }
        .table-custom { border-collapse: separate; border-spacing: 0; width: 100%; }
        .table-custom thead th { background-color: #f1f5f9 !important; color: #475569; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 16px; border: none; }
        .table-custom tbody tr { border-bottom: 1px solid #f1f5f9; transition: background-color 0.2s ease; }
        .table-custom tbody tr:hover { background-color: #f8fafc; }
        .table-custom tbody td { padding: 14px 16px; vertical-align: middle; font-size: 13px; color: #334155; }
        .badge-nis { background-color: #f1f5f9; color: #475569; font-weight: 600; padding: 5px 10px; border-radius: 6px; font-size: 12px; }
        .badge-kelas { background-color: #f8fafc; color: #334155; border: 1px solid #cbd5e1; font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: 11px; letter-spacing: 0.5px; }
        .badge-role-admin { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; }
        .badge-role-guru { background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; }
        .badge-role-siswa { background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; }
        .badge-role-default { background-color: #f8fafc; color: #475569; border: 1px solid #e2e8f0; padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; }
        .btn-aksi { background-color: #f8fafc; border: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 12px; padding: 5px 14px; border-radius: 6px; }
        .large-checkbox { transform: scale(1.2); cursor: pointer; }
        .table-responsive, .card-body.p-0 { overflow: visible !important; }
        .dropdown-menu { z-index: 1050 !important; }
        /* Styling Indikator Status Online / Offline */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
            box-shadow: 0 0 0 2px #fff;
        }

        .status-indicator.bg-success {
            background-color: #10b981 !important;
            box-shadow: 0 0 0 2px #fff, 0 0 6px #10b981;
        }

        .status-indicator.bg-secondary {
            background-color: #94a3b8 !important;
        }
    </style>

    <div class="page-content">
        <!-- Filter Card Atas -->
        <div class="filter-card mb-4">
            <form id="filterForm" class="row g-2 align-items-center" onsubmit="return false;">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bx bx-search"></i></span>
                        <input type="text" id="search-input" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Nama, NIP/NIS, atau Email..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-3">
                    <select id="role-select" name="role" class="form-select form-select-sm">
                        <option value="">-- Semua Hak Akses --</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="piket" {{ request('role') == 'piket' ? 'selected' : '' }}>Piket</option>
                        <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="per-page-select" name="per_page" class="form-select form-select-sm">
                        <option value="10" {{ request('per_page', '10') == '10' ? 'selected' : '' }}>10 Data</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 Data</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Data</option>
                    </select>
                </div>

                <div class="col-md-3 text-end d-flex gap-2">
                    <button type="button" id="btn-reset" class="btn btn-sm btn-secondary w-50 shadow-sm fw-bold">
                        <i class="bx bx-refresh me-1"></i> Reset
                    </button>
                    <a href="{{ url('user/add') }}" class="btn btn-sm btn-primary w-50 shadow-sm fw-bold">
                        <i class="bx bx-plus me-1"></i> User
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Container (Dirennder AJAX) -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0" id="user-table-container">
                @include('users.partials.user_table')
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let searchTimer;

            // Fungsi Utama Fetch Data via AJAX
            function fetchUsers(url = "{{ route('all.user') }}") {
                let search = $('#search-input').val();
                let role = $('#role-select').val();
                let per_page = $('#per-page-select').val();

                $.ajax({
                    url: url,
                    type: "GET",
                    data: {
                        search: search,
                        role: role,
                        per_page: per_page
                    },
                    beforeSend: function() {
                        $('#user-table-container').css('opacity', '0.5');
                    },
                    success: function(response) {
                        $('#user-table-container').html(response).css('opacity', '1');
                        window.history.pushState(null, '', url + '?' + $.param({ search, role, per_page }));
                    },
                    error: function() {
                        $('#user-table-container').css('opacity', '1');
                    }
                });
            }

            // Real-time Live Search dengan Debounce (300ms)
            $('#search-input').on('keyup input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    fetchUsers();
                }, 300);
            });

            // Filter Dropdown Change Event
            $('#role-select, #per-page-select').on('change', function() {
                fetchUsers();
            });

            // Handle Klik Pagination Link
            $(document).on('click', '#pagination-links a', function(e) {
                e.preventDefault();
                let pageUrl = $(this).attr('href');
                if (pageUrl) {
                    fetchUsers(pageUrl);
                }
            });

            // Tombol Reset Filter
            $('#btn-reset').on('click', function() {
                $('#search-input').val('');
                $('#role-select').val('');
                $('#per-page-select').val('10');
                fetchUsers();
            });

            // Toast Config
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            // Toggle Status User via AJAX & SweetAlert2
            $(document).on('change', '.status-toggle', function() {
                var $checkbox = $(this);
                var userId = $checkbox.data('user');
                var isChecked = $checkbox.is(':checked');

                Swal.fire({
                    title: 'Ubah Status User?',
                    text: isChecked ? "Akun ini akan diaktifkan kembali." : "Akun ini akan dinonaktifkan dari sistem.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isChecked ? '#198754' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: isChecked ? 'Ya, Aktifkan!' : 'Ya, Nonaktifkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('update.status.user') }}",
                            method: "POST",
                            data: {
                                user: userId,
                                is_checked: isChecked ? 1 : 0,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Toast.fire({
                                    icon: response.icon || 'success',
                                    title: response.message
                                });
                            },
                            error: function(xhr) {
                                $checkbox.prop('checked', !isChecked);
                                var errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    html: errorMsg,
                                    confirmButtonColor: '#4e73df'
                                });
                            }
                        });
                    } else {
                        $checkbox.prop('checked', !isChecked);
                    }
                });
            });

            // AJAX Generate Password
            $(document).on('click', '.btn-generate-password', function() {
                var userId = $(this).data('id');
                var userName = $(this).data('name');

                Swal.fire({
                    title: 'Reset Password?',
                    html: `Apakah Anda yakin ingin me-reset password untuk <b>${userName}</b>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Reset Password!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/user/generate-password/" + userId,
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: response.title,
                                    html: response.message,
                                    confirmButtonColor: '#4e73df'
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Terjadi kesalahan saat memproses password.',
                                    confirmButtonColor: '#4e73df'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
</x-app-layout>