@section('title', 'Riwayat Datang & Pulang')

<x-app-layout>
    <div class="page-content container-fluid px-3 px-md-4 py-3">

        <!-- Header Page & Switcher Tampilan -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="fw-bold text-primary mb-1">
                    <i class="fas fa-door-open me-2"></i> Riwayat Datang & Pulang
                </h4>
                <p class="text-muted small mb-0">
                    Daftar riwayat presensi harian gerbang kedatangan dan kepulangan Anda.
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

        <!-- FORM FILTER OTOMATIS (INSTANT TRIGGER TANPA TOMBOL FILTER) -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <form id="filterForm">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4 col-12">
                            <label class="form-label small fw-bold text-muted mb-1">Status Kehadiran</label>
                            <select name="status" id="filterStatus" class="form-select form-select-sm rounded-3">
                                <option value="">-- Semua Status --</option>
                                <option value="hadir">Hadir (Tepat Waktu)</option>
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
            @include('student_area.partials.daily_cards')
        </div>

        <!-- 2. TAMPILAN TABEL CONTAINER -->
        <div id="view-table-container" class="card border-0 shadow-sm rounded-3 d-none">
            <div class="card-body p-0" id="tableContent">
                @include('student_area.partials.daily_table')
            </div>
        </div>

        <!-- CONTAINER PAGINASI AJAX -->
        <div class="mt-4 d-flex justify-content-end" id="paginationContainer">
            {{ $dailies->links('pagination::bootstrap-5') }}
        </div>

    </div>

    <!-- MODAL DETAIL PRESENSI HARIAN -->
    <div class="modal fade" id="modalDailyDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-door-open me-2"></i> Detail Presensi Gerbang
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Foto Bukti Datang/Pulang -->
                    <div id="containerPhotos" class="row g-2 mb-3 d-none">
                        <div class="col-6 text-center" id="boxPhotoIn">
                            <small class="text-muted d-block mb-1 fw-bold">Foto Masuk</small>
                            <img id="imgPhotoIn" src="" class="img-fluid rounded-3 border shadow-sm" style="max-height: 140px; object-fit: cover; width: 100%;">
                        </div>
                        <div class="col-6 text-center" id="boxPhotoOut">
                            <small class="text-muted d-block mb-1 fw-bold">Foto Pulang</small>
                            <img id="imgPhotoOut" src="" class="img-fluid rounded-3 border shadow-sm" style="max-height: 140px; object-fit: cover; width: 100%;">
                        </div>
                    </div>

                    <div class="list-group list-group-flush rounded-3 border">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="far fa-calendar-alt me-2 text-primary"></i>Tanggal</span>
                            <strong id="dailyDate" class="text-dark"></strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-sign-in-alt me-2 text-success"></i>Jam Datang</span>
                            <span id="dailyArrival" class="fw-bold text-dark"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-sign-out-alt me-2 text-danger"></i>Jam Pulang</span>
                            <span id="dailyDeparture" class="fw-bold text-dark"></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-info-circle me-2 text-info"></i>Status</span>
                            <span id="dailyStatus" class="badge rounded-pill px-3 py-1"></span>
                        </div>
                        <div class="list-group-item">
                            <span class="text-muted d-block mb-1"><i class="fas fa-comment-alt me-2 text-secondary"></i>Catatan:</span>
                            <small id="dailyNotes" class="text-dark fw-normal"></small>
                        </div>
                        <div class="list-group-item">
                            <span class="text-muted d-block mb-1"><i class="fas fa-user-check me-2 text-secondary"></i>Pencatat:</span>
                            <small id="dailyRecordedBy" class="text-dark fw-bold"></small>
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
        // Switch Tampilan (Card vs Tabel)
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
                url: "{{ route('student.history.daily') }}?page=" + page,
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
                        text: 'Gagal memuat data presensi.',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }

        // EVENT LISTENER READY
        $(document).ready(function() {
            // 1. Instant Trigger saat Opsi Filter Diubah
            $('#filterStatus, #filterStartDate, #filterEndDate').on('change input', function() {
                fetchData(1);
            });

            // 2. Tombol Reset Filter
            $('#btnResetFilter').on('click', function() {
                $('#filterForm')[0].reset();
                fetchData(1);
            });

            // 3. AJAX Pagination Handler (Halaman Berpindah Tanpa Reload Page)
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

        // FUNGSI MODAL DETAIL PRESENSI HARIAN
        function showDailyDetailModal(id) {
            Swal.fire({
                title: 'Memuat Detail...',
                position: 'center',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "/student/history-daily/" + id + "/detail",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    Swal.close();
                    if (response.status === 'success') {
                        const data = response.data;
                        $('#dailyDate').text(data.date);
                        $('#dailyArrival').text(data.arrival);
                        $('#dailyDeparture').text(data.departure);
                        $('#dailyNotes').text(data.notes);
                        $('#dailyRecordedBy').text(data.recorded_by);

                        // Dynamic Status Badge Color
                        const statusBadge = $('#dailyStatus');
                        statusBadge.text(data.status);
                        statusBadge.attr('class', 'badge rounded-pill px-3 py-1');

                        const st = data.status.toLowerCase();
                        if (st === 'hadir') statusBadge.addClass('bg-success');
                        else if (st === 'terlambat') statusBadge.addClass('bg-warning text-dark');
                        else if (st === 'sakit' || st === 'izin') statusBadge.addClass('bg-info text-dark');
                        else statusBadge.addClass('bg-danger');

                        // Check photo status
                        const containerPhotos = $('#containerPhotos');
                        const boxIn = $('#boxPhotoIn');
                        const boxOut = $('#boxPhotoOut');

                        let hasPhoto = false;

                        if (data.photo_in) {
                            $('#imgPhotoIn').attr('src', data.photo_in);
                            boxIn.removeClass('d-none');
                            hasPhoto = true;
                        } else {
                            boxIn.addClass('d-none');
                        }

                        if (data.photo_out) {
                            $('#imgPhotoOut').attr('src', data.photo_out);
                            boxOut.removeClass('d-none');
                            hasPhoto = true;
                        } else {
                            boxOut.addClass('d-none');
                        }

                        if (hasPhoto) {
                            containerPhotos.removeClass('d-none');
                        } else {
                            containerPhotos.addClass('d-none');
                        }

                        const modal = new bootstrap.Modal(document.getElementById('modalDailyDetail'));
                        modal.show();
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat Data',
                        text: 'Terjadi kesalahan saat mengambil detail presensi harian.',
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }
    </script>
</x-app-layout>