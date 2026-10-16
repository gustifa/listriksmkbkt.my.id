@extends('layouts.app')

@section('title', 'Kelola Kalender Hari Libur')

@section('content')
<div class="page-content container-fluid px-3">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-primary mb-1">
                <i class="fas fa-calendar-alt me-2"></i> Pengaturan Kalender Merah & Libur
            </h4>
            <p class="text-muted mb-0">Klik tanggal untuk menambah libur baru, atau klik event yang ada untuk mengubah / menghapus data.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <div id="calendar"></div>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT HARI LIBUR -->
<div class="modal fade" id="modalAddHoliday" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div id="modalHeader" class="modal-header bg-danger text-white rounded-top-4 p-3">
                <h6 class="modal-title fw-bold" id="modalLabel">
                    <i class="fas fa-calendar-plus me-2"></i> <span id="modalTitleText">Tambah Pengaturan Hari Libur</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formHoliday">
                @csrf
                <input type="hidden" id="holiday_id" name="holiday_id">
                <input type="hidden" id="_method" name="_method" value="POST">

                <div class="modal-body p-4">
                    <!-- Judul/Nama Libur -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Libur / Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="holiday_title" class="form-control rounded-3" placeholder="Contoh: Libur Semester Genap" required>
                    </div>

                    <!-- Tanggal Mulai & Selesai -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control rounded-3" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control rounded-3" required>
                        </div>
                    </div>

                    <!-- Tipe Libur & Status Aktif -->
                    <div class="row g-2 mb-3 align-items-center">
                        <div class="col-7">
                            <label class="form-label fw-bold small">Tipe Libur <span class="text-danger">*</span></label>
                            <select name="type" id="holiday_type" class="form-select rounded-3" required>
                                <option value="nasional">Libur Nasional / Tanggal Merah</option>
                                <option value="sekolah">Libur Semester / Sekolah</option>
                                <option value="khusus">Kegiatan Khusus / Non-Efektif</option>
                            </select>
                        </div>
                        <div class="col-5 pt-3">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                <label class="form-check-input-label fw-bold small text-dark" for="is_active">Aktifkan Aturan</label>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan / Deskripsi -->
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Catatan Tambahan</label>
                        <textarea name="description" id="description" class="form-control rounded-3" rows="2" placeholder="Catatan/deskripsi jika ada..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 p-2 d-flex justify-content-between">
                    <div>
                        <button type="button" id="btnDeleteHoliday" class="btn btn-outline-danger btn-sm rounded-pill px-3 d-none">
                            <i class="fas fa-trash me-1"></i> Hapus
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btnSubmitHoliday" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold">
                            <i class="fas fa-save me-1"></i> Simpan Libur
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FullCalendar 6 & SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const modalAdd = new bootstrap.Modal(document.getElementById('modalAddHoliday'));

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            events: "{{ route('admin.holidays.events') }}",
            selectable: true,
            
            // 1. TAMBAH LIBUR BARU (Saat Klik Tanggal Kosong)
            select: function (info) {
                $('#formHoliday')[0].reset();
                $('#holiday_id').val('');
                $('#_method').val('POST');
                
                $('#modalTitleText').text('Tambah Pengaturan Hari Libur');
                $('#btnSubmitHoliday').html('<i class="fas fa-save me-1"></i> Simpan Libur');
                $('#btnDeleteHoliday').addClass('d-none'); // Sembunyikan tombol hapus

                $('#start_date').val(info.startStr);
                
                let endDate = new Date(info.endStr);
                endDate.setDate(endDate.getDate() - 1);
                $('#end_date').val(endDate.toISOString().split('T')[0]);

                modalAdd.show();
            },

            // 2. EDIT / DETAIL LIBUR (Saat Klik Event yang Ada)
            eventClick: function (info) {
                const holidayId = info.event.id; // UUID

                $.ajax({
                    url: "/admin/holidays/" + holidayId,
                    type: "GET",
                    success: function (res) {
                        const data = res.data;
                        
                        $('#holiday_id').val(data.id);
                        $('#_method').val('PUT'); // Ubah method AJAX ke PUT
                        
                        $('#modalTitleText').text('Edit Pengaturan Hari Libur');
                        $('#btnSubmitHoliday').html('<i class="fas fa-sync me-1"></i> Perbarui Libur');
                        $('#btnDeleteHoliday').removeClass('d-none'); // Tampilkan tombol hapus

                        // Isi form dengan data dari server
                        $('#holiday_title').val(data.title);
                        $('#start_date').val(data.start_date.split('T')[0]);
                        $('#end_date').val(data.end_date.split('T')[0]);
                        $('#holiday_type').val(data.type);
                        $('#is_active').prop('checked', data.is_active);
                        $('#description').val(data.description);

                        modalAdd.show();
                    },
                    error: function () {
                        Swal.fire('Gagal!', 'Gagal mengambil data detail hari libur.', 'error');
                    }
                });
            }
        });

        calendar.render();

        // 3. SUBMIT FORM (Simpan Baru / Update)
        $('#formHoliday').on('submit', function (e) {
            e.preventDefault();

            const holidayId = $('#holiday_id').val();
            const isEdit = holidayId !== '';
            const targetUrl = isEdit ? "/admin/holidays/" + holidayId : "{{ route('admin.holidays.store') }}";

            $.ajax({
                url: targetUrl,
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    modalAdd.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    calendar.refetchEvents();
                },
                error: function (xhr) {
                    let errorMsg = 'Terjadi kesalahan saat menyimpan.';
                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                        errorMsg = xhr.responseJSON.errors[firstKey][0];
                    }
                    Swal.fire('Gagal!', errorMsg, 'error');
                }
            });
        });

        // 4. PROSES HAPUS LIBUR (Dari Tombol Modal)
        $('#btnDeleteHoliday').on('click', function () {
            const holidayId = $('#holiday_id').val();

            Swal.fire({
                title: 'Hapus Libur Ini?',
                text: "Data hari libur yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/admin/holidays/" + holidayId,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (res) {
                            modalAdd.hide();
                            Swal.fire('Terhapus!', res.message, 'success');
                            calendar.refetchEvents();
                        },
                        error: function () {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection