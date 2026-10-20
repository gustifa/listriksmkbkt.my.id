@section('title', 'Import Data Siswa')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary">
                        <h4 class="mb-0"><i class="fas fa-file-import me-2"></i> Import Data Siswa (Dapodik)</h4>
                    </div>
                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="alert alert-info border-left-primary">
                            <strong><i class="fas fa-info-circle"></i> Informasi Format Import:</strong> <br>
                            Sistem secara otomatis membaca format Excel ekspor Dapodik (Baris header ke-4 & 5).<br>
                            - Jika <b>NIS belum ada</b> di database: Otomatis ditambahkan sebagai siswa baru.<br>
                            - Jika <b>NIS sudah ada</b>: Otomatis memperbarui (update) data siswa tersebut.
                        </div>

                        <form action="{{ route('students.import.store') }}" method="POST" enctype="multipart/form-data" id="form-import">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">Pilih File Excel (.xlsx / .xls)</label>
                                <input type="file" id="file-input" name="file" class="form-control" required accept=".xlsx, .xls, .csv">
                                <small class="text-muted">*File akan otomatis diproses setelah dipilih.</small>
                            </div>

                            <div class="d-flex justify-content-start">
                                <a href="{{ route('students.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-4 shadow-sm card">
                    <div class="card-body">
                        <h6 class="fw-bold text-secondary">Contoh Format Excel Dapodik yang Didukung:</h6>
                        <div class="table-responsive">
                            <table class="table mt-2 table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama</th>
                                        <th>NIPD (NIS)</th>
                                        <th>JK</th>
                                        <th>NISN</th>
                                        <th>Tempat Lahir</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Rombel Saat Ini</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Ababil Argya Ivander</td>
                                        <td>36822</td>
                                        <td>L</td>
                                        <td>0119421352</td>
                                        <td>Bukittinggi</td>
                                        <td>2011-04-29</td>
                                        <td>X TITL 1</td>
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
                    title: 'Sedang Memproses...',
                    text: 'Mohon tunggu, data sedang divalidasi dan diimport ke database.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                document.getElementById('form-import').submit();
            }
        });
    </script>

    <!-- 2. Penangkap Notifikasi Session SweetAlert (Aman & Anti Error Syntax) -->
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