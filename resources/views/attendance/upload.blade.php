<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload Rekap Foto Absensi</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        body { background-color: #f8f9fa; }
        .upload-card { border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
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
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card upload-card p-4">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-primary"><i class="fa-solid fa-camera me-2"></i>Scan Rekap Absensi</h4>
                    <p class="text-muted small">Unggah foto lembar absensi untuk diproses oleh OCR & AI</p>
                </div>

                <form id="uploadAttendanceForm" enctype="multipart/form-data">
                    <!-- Tipe Absensi -->
                    <div class="mb-3">
                        <label for="scan_type" class="form-label fw-semibold">Tipe Absensi</label>
                        <select class="form-select" id="scan_type" name="scan_type" required>
                            <option value="" selected disabled>-- Pilih Tipe Absensi --</option>
                            <option value="daily">Absensi Gerbang / Harian</option>
                            <option value="learning">Absensi Pembelajaran</option>
                            <option value="prayer">Absensi Sholat</option>
                        </select>
                    </div>

                    <!-- Tanggal Absensi -->
                    <div class="mb-3">
                        <label for="scan_date" class="form-label fw-semibold">Tanggal Absensi</label>
                        <input type="date" class="form-control" id="scan_date" name="scan_date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <!-- Upload Foto Area -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Foto Lembar Absensi</label>
                        <div class="image-preview-container" onclick="document.getElementById('imageInput').click()">
                            <div id="previewPlaceholder">
                                <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary mb-2"></i>
                                <p class="mb-1 text-dark fw-medium">Klik atau Drag & Drop foto di sini</p>
                                <span class="text-muted small">Format: JPG, JPEG, PNG (Maks. 10MB)</span>
                            </div>
                            <img id="imagePreview" alt="Preview Gambar" class="img-fluid shadow-sm">
                        </div>
                        <input type="file" id="imageInput" name="image" accept="image/png, image/jpeg, image/jpg" class="d-none" required>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="btnSubmit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                        <i class="fa-solid fa-microchip me-2"></i>Unggah & Proses OCR
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document.getElementById('imageInput')).on('change', function(e) {
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

$('#uploadAttendanceForm').on('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    let btnSubmit = $('#btnSubmit');

    // Tampilkan Loading SweetAlert2
    Swal.fire({
        title: 'Memproses Gambar...',
        text: 'Mohon tunggu, AI/OCR sedang mengekstrak data absensi.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    btnSubmit.prop('disabled', true);

    $.ajax({
        url: "{{ route('attendance.scans.upload') }}",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            btnSubmit.prop('disabled', false);

            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Diproses!',
                    text: response.message,
                    confirmButtonText: 'Lihat Hasil Verifikasi',
                    confirmButtonColor: '#0d6efd'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect ke halaman verifikasi/review
                        window.location.href = response.redirect_url;
                    }
                });
            }
        },
        error: function(xhr) {
            btnSubmit.prop('disabled', false);
            
            let errorMessage = 'Terjadi kesalahan saat mengunggah foto.';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errors = xhr.responseJSON.errors;
                errorMessage = Object.values(errors).map(err => err.join('<br>')).join('<br>');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Gagal Upload!',
                html: errorMessage,
                confirmButtonColor: '#dc3545'
            });
        }
    });
});
</script>
</body>
</html>