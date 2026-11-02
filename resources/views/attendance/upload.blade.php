@section('title', 'Rekap Foto Absensi OCR (Full AJAX)')

<x-app-layout>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        body { background-color: #f8f9fa; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .image-preview-container {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .image-preview-container:hover { border-color: #0d6efd; background-color: #f1f5f9; }
        #imagePreview { max-height: 250px; border-radius: 8px; display: none; margin: 0 auto; }
        #reviewSection { display: none; }
    </style>

<div class="container py-5">
    
    <!-- SECTION 1: FORM UPLOAD -->
    <div id="uploadSection" class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card card-custom p-4">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-primary"><i class="fa-solid fa-camera me-2"></i>Scan Rekap Absensi</h4>
                    <p class="text-muted small">Unggah foto lembar absensi untuk diproses oleh OCR & AI</p>
                </div>

                <form id="uploadAttendanceForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="scan_type" class="form-label fw-semibold">Tipe Absensi</label>
                        <select class="form-select" id="scan_type" name="scan_type" required>
                            <option value="" selected disabled>-- Pilih Tipe Absensi --</option>
                            <option value="daily">Absensi Gerbang / Harian</option>
                            <option value="learning">Absensi Pembelajaran</option>
                            <option value="prayer">Absensi Sholat</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="classroom_id" class="form-label fw-semibold">Pilih Kelas</label>
                        <select class="form-select select2" id="classroom_id" name="classroom_id" required>
                            <option value="" selected disabled>-- Pilih Kelas --</option>
                            @if(isset($classrooms) && count($classrooms) > 0)
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="scan_date" class="form-label fw-semibold">Tanggal Absensi</label>
                        <input type="date" class="form-control" id="scan_date" name="scan_date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Foto Lembar Absensi</label>
                        <div class="image-preview-container" onclick="$('#imageInput').click()">
                            <div id="previewPlaceholder">
                                <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary mb-2"></i>
                                <p class="mb-1 text-dark fw-medium">Klik atau Drag & Drop foto di sini</p>
                                <span class="text-muted small">Format: JPG, JPEG, PNG (Maks. 10MB)</span>
                            </div>
                            <img id="imagePreview" alt="Preview Gambar" class="img-fluid shadow-sm">
                        </div>
                        <input type="file" id="imageInput" name="image" accept="image/png, image/jpeg, image/jpg" class="d-none" required>
                    </div>

                    <button type="submit" id="btnUpload" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                        <i class="fa-solid fa-microchip me-2"></i>Unggah & Proses OCR
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- SECTION 2: REVIEW HASIL OCR (DYNAMICALLY RENDERED VIA AJAX) -->
    <div id="reviewSection" class="row">
        <div class="col-12">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-primary mb-0"><i class="fa-solid fa-list-check me-2"></i>Verifikasi Hasil Rekap Absensi</h4>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetForm()">
                        <i class="fa-solid fa-arrow-left me-1"></i> Scan Ulang / Kembali
                    </button>
                </div>

                <form id="commitAttendanceForm">
                    <input type="hidden" id="scan_id" name="scan_id">
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Teks Terbaca (OCR)</th>
                                    <th>Siswa Terdeteksi</th>
                                    <th>Skor Kemiripan</th>
                                    <th>Status Absen</th>
                                </tr>
                            </thead>
                            <tbody id="ocrResultTableBody">
                                <!-- Data diisi via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end">
                        <button type="submit" id="btnCommit" class="btn btn-success px-4 py-2 fw-semibold shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Simpan ke Database Utama
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@push('scripts')
<script>
$(document).ready(function() {
    // Inisialisasi Select2 pada Dropdown Kelas
    $('#classroom_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Kelas --',
        allowClear: true,
        width: '100%'
    });
});

// Prapinjau Foto Gambar
$('#imageInput').on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').attr('src', e.target.result).show();
            $('#previewPlaceholder').hide();
        }
        reader.readAsDataURL(file);
    }
});

// AJAX 1: Unggah & Proses OCR
$('#uploadAttendanceForm').on('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    let btnUpload = $('#btnUpload');

    Swal.fire({
        title: 'Memproses Gambar...',
        text: 'Mohon tunggu, AI/OCR sedang mengekstrak data absensi.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    btnUpload.prop('disabled', true);

    $.ajax({
        url: "{{ route('attendance.scans.upload') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            btnUpload.prop('disabled', false);

            if (response.success) {
                Swal.close();
                renderReviewTable(response.scan_id, response.data);
            }
        },
        error: function(xhr) {
            btnUpload.prop('disabled', false);
            let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengunggah foto.';
            Swal.fire({ icon: 'error', title: 'Gagal Upload!', html: msg, confirmButtonColor: '#dc3545' });
        }
    });
});

// Render Tabel Hasil Matching ke Layar secara Dinamis
function renderReviewTable(scanId, data) {
    $('#scan_id').val(scanId);
    let tbody = $('#ocrResultTableBody');
    tbody.empty();

    if (!data || data.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center text-muted">Tidak ada data siswa yang terdeteksi.</td></tr>');
    } else {
        $.each(data, function(index, item) {
            let row = `
                <tr>
                    <td>${index + 1}</td>
                    <td class="text-muted small">${item.raw_text}</td>
                    <td>
                        <input type="hidden" name="attendances[${index}][student_id]" value="${item.matched_student_id}">
                        <strong class="text-dark d-block">${item.matched_student_name}</strong>
                    </td>
                    <td>
                        <span class="badge ${item.similarity_score >= 70 ? 'bg-success' : 'bg-warning'}">
                            ${item.similarity_score}%
                        </span>
                    </td>
                    <td>
                        <select name="attendances[${index}][status]" class="form-select form-select-sm" style="width: 130px;">
                            <option value="hadir" ${item.status === 'hadir' || item.status === 'present' ? 'selected' : ''}>Hadir</option>
                            <option value="terlambat" ${item.status === 'terlambat' ? 'selected' : ''}>Terlambat</option>
                            <option value="izin" ${item.status === 'izin' ? 'selected' : ''}>Izin</option>
                            <option value="sakit" ${item.status === 'sakit' ? 'selected' : ''}>Sakit</option>
                            <option value="alpa" ${item.status === 'alpa' || item.status === 'absent' ? 'selected' : ''}>Alfa</option>
                        </select>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    $('#uploadSection').slideUp();
    $('#reviewSection').slideDown();
}

// AJAX 2: Simpan Hasil Review ke Database Utama
$('#commitAttendanceForm').on('submit', function(e) {
    e.preventDefault();

    let scanId = $('#scan_id').val();
    let btnCommit = $('#btnCommit');

    Swal.fire({
        title: 'Menyimpan Data...',
        text: 'Sedang menyimpan rekap absensi ke database.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    btnCommit.prop('disabled', true);

    $.ajax({
        url: "/attendance/commit/" + scanId,
        type: "POST",
        data: $(this).serialize(),
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            btnCommit.prop('disabled', false);
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: response.message,
                    confirmButtonColor: '#198754'
                }).then(() => {
                    resetForm();
                });
            }
        },
        error: function(xhr) {
            btnCommit.prop('disabled', false);
            let msg = xhr.responseJSON?.message || 'Gagal menyimpan ke database.';
            Swal.fire({ icon: 'error', title: 'Gagal!', html: msg, confirmButtonColor: '#dc3545' });
        }
    });
});

// Function Reset ke Form Upload Awal
function resetForm() {
    $('#uploadAttendanceForm')[0].reset();
    $('#classroom_id').val('').trigger('change'); // Reset UI Select2
    $('#imagePreview').hide().attr('src', '');
    $('#previewPlaceholder').show();
    $('#reviewSection').slideUp();
    $('#uploadSection').slideDown();
}
</script>
@endpush
</x-app-layout>