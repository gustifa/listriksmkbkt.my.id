@section('title', 'Import Absensi PKL')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary">
                        <h4 class="mb-0"><i class="fas fa-file-import me-2"></i> Import Rekap Absensi PKL</h4>
                    </div>
                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="alert alert-info border-left-primary">
                            <strong><i class="fas fa-info-circle"></i> Catatan Import Rekap PKL:</strong> <br>
                            Sistem secara otomatis membaca sheet absensi (Mendukung file ekspor sistem PKL).<br>
                            - <b>NIS / Username Siswa</b> digunakan untuk mencocokkan siswa dengan database.<br>
                            - Jika data absensi pada tanggal tersebut sudah ada, sistem akan otomatis memperbarui (upsert) nilainya.
                        </div>

                        <form action="{{ route('attendances.import.store') }}" method="POST" enctype="multipart/form-data" id="form-import-absensi">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">Pilih File Excel Rekap Absensi (.xlsx / .xls)</label>
                                <input type="file" id="file-input" name="file" class="form-control" required accept=".xlsx, .xls, .csv">
                                <small class="text-muted">*File akan otomatis diproses setelah Anda memilih file.</small>
                            </div>

                            <div class="d-flex justify-content-start">
                                <a href="{{ url('/dashboard') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-4 shadow-sm card">
                    <div class="card-body">
                        <h6 class="fw-bold text-secondary">Format Header Excel yang Dikenali:</h6>
                        <div class="table-responsive">
                            <table class="table mt-2 table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Username Siswa / NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Status</th>
                                        <th>Catatan / Jurnal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>2026-06-11 19:36:36</td>
                                        <td>35357</td>
                                        <td>ADITIAWARMAN</td>
                                        <td>Masuk</td>
                                        <td>Perkenalan dan informasi dari perusahaan</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pustaka SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 1. Auto-Submit Form saat file dipilih
        document.getElementById('file-input').addEventListener('change', function() {
            if (this.files.length > 0) {
                Swal.fire({
                    title: 'Sedang Memproses Absensi...',
                    text: 'Mohon tunggu, ribuan data absensi sedang divalidasi dan diimport ke database.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                document.getElementById('form-import-absensi').submit();
            }
        });
    </script>

    <!-- 2. Penangkap Notifikasi Session SweetAlert (Aman dari Syntax Error) -->
    @if(session('swal'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const swalData = @json(session('swal'));
                
                Swal.fire({
                    icon: swalData.icon || 'info',
                    title: swalData.title || 'Informasi',
                    html: swalData.html || null,
                    text: swalData.text || null,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
            });
        </script>
    @endif

</x-app-layout>