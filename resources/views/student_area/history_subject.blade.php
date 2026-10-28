@section('title', 'Riwayat Absensi Pelajaran')

<x-app-layout>
    <div class="page-content container-fluid px-3 px-md-4 py-3">

        <!-- Header Page & Switcher Tampilan -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="fw-bold text-primary mb-1">
                    <i class="fas fa-book-reader me-2"></i> Riwayat Absensi Pelajaran
                </h4>
                <p class="text-muted small mb-0">
                    Daftar riwayat presensi mata pelajaran yang telah Anda ikuti.
                </p>
            </div>

            <div class="btn-group bg-white p-1 rounded-3 border shadow-sm" role="group" aria-label="View Switcher">
                <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0 active" id="btn-view-card" onclick="switchView('card')">
                    <i class="fas fa-th-large me-1"></i> Card
                </button>
                <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0" id="btn-view-table" onclick="switchView('table')">
                    <i class="fas fa-list me-1"></i> Tabel
                </button>
            </div>
        </div>

        <!-- FORM FILTER OTOMATIS (INSTANT TRIGGER TANPA TOMBOL) -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form id="filterForm">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4 col-12">
                            <label class="form-label small fw-bold text-muted mb-1">Status Kehadiran</label>
                            <select name="status" id="filterStatus" class="form-select form-select-sm rounded-3">
                                <option value="">-- Semua Status --</option>
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="sakit">Sakit</option>
                                <option value="izin">Izin</option>
                                <option value="alpa">Alpa</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-bold text-muted mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" id="filterStartDate" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-4 col-5">
                            <label class="form-label small fw-bold text-muted mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" id="filterEndDate" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-1 col-1 d-flex align-items-end justify-content-end mt-md-4">
                            <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary rounded-3 w-100 fw-semibold" title="Reset Filter">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 1. TAMPILAN CARD CONTAINER -->
        <div id="view-card-container">
            @include('student_area.partials.subject_cards')
        </div>

        <!-- 2. TAMPILAN TABEL CONTAINER -->
        <div id="view-table-container" class="card border-0 shadow-sm rounded-3 d-none">
            <div class="card-body p-0" id="tableContent">
                @include('student_area.partials.subject_table')
            </div>
        </div>

        <!-- CONTAINER PAGINASI AJAX -->
        <div class="mt-4 d-flex justify-content-between align-items-center" id="paginationContainer">
            @if ($attendances->hasPages())
                <div>
                    <small class="text-muted">
                        Menampilkan {{ $attendances->firstItem() }} - {{ $attendances->lastItem() }} dari {{ $attendances->total() }} data
                    </small>
                </div>
                <div>
                    {{ $attendances->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="w-100 text-end">
                    <small class="text-muted">Total {{ $attendances->total() }} data presensi</small>
                </div>
            @endif
        </div>

    </div>

    <!-- MODAL DETAIL PRESENSI MAPEL -->
    <div class="modal fade" id="modalDetailAttendance" tabindex="-1" aria-labelledby="modalDetailAttendanceLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="modalDetailAttendanceLabel">
                        <i class="fas fa-file-invoice me-2"></i> Detail Presensi Pelajaran
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="list-group list-group-flush rounded-3 border">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-book me-2 text-primary"></i>Mata Pelajaran</span>
                            <strong id="detailSubject" class="text-dark"></strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-user-tie me-2 text-primary"></i>Guru Pengampu</span>
                            <span id="detailTeacher" class="fw-bold"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="far fa-calendar-alt me-2 text-primary"></i>Tanggal</span>
                            <span id="detailDate" class="fw-semibold"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="far fa-clock me-2 text-danger"></i>Jam Absen</span>
                            <span id="detailTime" class="fw-bold"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-info-circle me-2 text-info"></i>Status</span>
                            <span id="detailStatus" class="badge rounded-pill px-3 py-1"></span>
                        </div>
                        <div class="list-group-item">
                            <span class="text-muted d-block mb-1"><i class="fas fa-user-check me-2 text-secondary"></i>Pencatat:</span>
                            <small id="detailRecordedBy" class="text-dark fw-bold"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT JQUERY, AJAX, INSTANT FILTER & SWEETALERT2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Switcher Tampilan (Card vs Tabel)
        function switchView(mode) {
            const cardContainer = $('#view-card-container');
            const tableContainer = $('#view-table-container');
            const btnCard = $('#btn-view-card');
            const btnTable = $('#btn-view-table');

            if (mode === 'card') {
                cardContainer.removeClass('d-none');
                tableContainer.addClass('d-none');
                btnCard.addClass('active bg-primary text-white');
                btnTable.removeClass('active bg-primary text-white');
            } else {
                tableContainer.removeClass('d-none');
                cardContainer.addClass('d-none');
                btnTable.addClass('active bg-primary text-white');
                btnCard.removeClass('active bg-primary text-white');
            }
        }

        // FUNGSI UTAMA AJAX FETCH DATA (FILTER & PAGINATION)
        function fetchData(page = 1) {
            const status = $('#filterStatus').val();
            const startDate = $('#filterStartDate').val();
            const endDate = $('#filterEndDate').val();

            $.ajax({
                url: "{{ route('student.history.subject') }}?page=" + page,
                type: "GET",
                data: {
                    status: status,
                    start_date: startDate,
                    end_date: endDate
                },
                dataType: "json",
                beforeSend: function() {
                    $('#view-card-container, #tableContent').css('opacity', '0.5');
                },
                success: function(response) {
                    $('#view-card-container, #tableContent').css('opacity', '1');
                    if (response.status === 'success') {
                        $('#view-card-container').html(response.html_card);
                        $('#tableContent').html(response.html_table);
                        $('#paginationContainer').html(response.pagination);
                    }
                },
                error: function() {
                    $('#view-card-container, #tableContent').css('opacity', '1');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data presensi pelajaran.',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }

        // EVENT LISTENER READY
        $(document).ready(function() {
            // 1. Instant Trigger saat Input/Filter Diubah
            $('#filterStatus, #filterStartDate, #filterEndDate').on('change input', function() {
                fetchData(1);
            });

            // 2. Tombol Reset Filter
            $('#btnResetFilter').on('click', function() {
                $('#filterForm')[0].reset();
                fetchData(1);
            });

            // 3. AJAX Pagination Click Handler
            $(document).on('click', '#paginationContainer a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                if (url) {
                    let page = new URLSearchParams(url.split('?')[1]).get('page');
                    if (page) {
                        fetchData(page);
                    }
                }
            });
        });

        // FUNGSI MODAL DETAIL PRESENSI MAPEL
        function showDetailModal(id) {
            Swal.fire({
                title: 'Memuat Detail...',
                text: 'Silakan tunggu sebentar',
                position: 'center',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "/student/history-subject/" + id + "/detail",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    Swal.close();
                    if (response.status === 'success') {
                        const data = response.data;
                        $('#detailSubject').text(data.subject);
                        $('#detailTeacher').text(data.teacher);
                        $('#detailDate').text(data.date);
                        $('#detailTime').text(data.time);
                        $('#detailRecordedBy').text(data.recorded_by);

                        const statusBadge = $('#detailStatus');
                        statusBadge.text(data.status);
                        statusBadge.attr('class', 'badge rounded-pill px-3 py-1');

                        const st = data.status.toLowerCase();
                        if (st === 'hadir') statusBadge.addClass('bg-success');
                        else if (st === 'terlambat') statusBadge.addClass('bg-warning text-dark');
                        else if (st === 'sakit') statusBadge.addClass('bg-primary');
                        else if (st === 'izin') statusBadge.addClass('bg-info text-dark');
                        else statusBadge.addClass('bg-danger');

                        const modal = new bootstrap.Modal(document.getElementById('modalDetailAttendance'));
                        modal.show();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat Data',
                        text: 'Terjadi kesalahan saat mengambil detail presensi.',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }
    </script>
</x-app-layout>