@section('title', 'Penempatan PKL Siswa')

<x-app-layout>
<div class="container-fluid py-4">

    <!-- HEADER / BREADCRUMB -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-file-excel text-success me-2"></i>Import Absensi Siswa PKL
            </h4>
            <p class="text-muted small mb-0">Unggah data kehadiran siswa berbasis file Excel (.xlsx / .csv)</p>
        </div>
        <a href="" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Data Absensi
        </a>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong class="d-block mb-1"><i class="fas fa-exclamation-circle me-1"></i> Terjadi Kesalahan Validasi:</strong>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">

        <!-- KOLOM KIRI: FORM UPLOAD FILE -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-cloud-upload-alt text-primary me-2"></i>Form Upload File
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- DRAG & DROP / FILE INPUT -->
                        <div class="mb-4 text-center p-4 border border-2 border-dashed rounded-3 bg-light" id="dropZone">
                            <i class="fas fa-file-excel text-success display-4 mb-3"></i>
                            <h6 class="fw-bold text-dark">Pilih atau Seret File Excel ke Sini</h6>
                            <p class="text-muted small mb-3">Mendukung format .xlsx, .xls, dan .csv (Maksimal 2MB)</p>

                            <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx, .xls, .csv" required onchange="updateFileName(this)">
                            <button type="button" class="btn btn-outline-primary btn-sm px-4 fw-bold" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-folder-open me-2"></i>Cari File
                            </button>

                            <div id="selectedFileName" class="mt-3 fw-bold text-success small" style="display: none;"></div>
                        </div>

                        <!-- BUTTON SUBMIT -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="" class="btn btn-link text-decoration-none text-muted small p-0">
                                <i class="fas fa-download me-1"></i> Download Format Template Excel
                            </a>
                            <button type="submit" class="btn btn-success fw-bold px-4">
                                <i class="fas fa-upload me-2"></i>Proses Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: PETUNJUK FORMAT EXCEL -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-info-circle text-info me-2"></i>Petunjuk & Ketentuan Import
                    </h6>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted mb-3">Pastikan header pada baris pertama file Excel Anda mengikuti aturan berikut:</p>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle small mb-3">
                            <thead class="bg-light">
                                <tr>
                                    <th>Header Kolom</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code class="fw-bold">nisn</code></td>
                                    <td>NISN siswa yang terdaftar aktif PKL.</td>
                                </tr>
                                <tr>
                                    <td><code class="fw-bold">tanggal</code></td>
                                    <td>Format: <code>YYYY-MM-DD</code> (contoh: 2026-09-29).</td>
                                </tr>
                                <tr>
                                    <td><code class="fw-bold">jam_masuk</code></td>
                                    <td>Format: <code>HH:MM</code> (contoh: 07:30).</td>
                                </tr>
                                <tr>
                                    <td><code class="fw-bold">jam_pulang</code></td>
                                    <td>Format: <code>HH:MM</code> (contoh: 16:00).</td>
                                </tr>
                                <tr>
                                    <td><code class="fw-bold">status</code></td>
                                    <td>Isi: <code>present</code>/<code>hadir</code>, <code>sick</code>/<code>sakit</code>, <code>permit</code>/<code>izin</code>.</td>
                                </tr>
                                <tr>
                                    <td><code class="fw-bold">jurnal</code></td>
                                    <td>Catatan/Ringkasan kegiatan harian.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning mb-0 border-0 p-3 small">
                        <i class="fas fa-lightbulb me-1"></i> <strong>Catatan Penting:</strong> Jika tanggal & NISN sudah pernah dimasukkan sebelumnya, data absensi tersebut akan otomatis diperbarui (update).
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</x-app-layout>
@push('scripts')
<script>
    function updateFileName(input) {
        const fileNameDiv = document.getElementById('selectedFileName');
        if (input.files && input.files[0]) {
            fileNameDiv.innerText = "File terpilih: " + input.files[0].name;
            fileNameDiv.style.display = "block";
        } else {
            fileNameDiv.style.display = "none";
        }
    }
</script>
@endpush
@endsection
