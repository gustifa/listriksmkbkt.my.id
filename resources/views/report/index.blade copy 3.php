@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    <div class="page-content">
    <div class="row justify-content-center">
        <div class="col-md-12">
                <!-- <div class="mb-4 d-flex justify-content-between align-items-center">
                    <h3 class="fw-bold text-primary"><i class="fas fa-users me-2"></i> Laporan Presensi</h3>
                </div> -->

            <!-- CARD 1: LAPORAN UMUM (PERIODE & KELAS) -->
            <div class="mb-4 shadow card">
                <div class="text-white card-header bg-primary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i> Laporan Rekapitulasi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.print') }}" method="POST" target="_blank" id="filterForm">
                        @csrf

                        <div class="row">
                            <!-- Filter Periode -->
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Pilih Periode:</label>
                                <select id="periode_selector" name="periode" class="form-select" onchange="toggleFilter()">
                                    <option value="harian">Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                    <option value="bulanan">Bulanan</option>
                                    <option value="semester">Semester</option>
                                </select>
                            </div>

                            <!-- Filter Kelas (Opsional) -->
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Filter Kelas (Opsional):</label>
                                <select name="classroom_id" class="form-select select2">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($classrooms as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">Kosongkan untuk mencetak satu sekolah.</div>
                            </div>
                        </div>

                        <hr>

                        <!-- A. Form Harian -->
                        <div id="filter_harian" class="filter-section">
                            <div class="mb-3">
                                <label>Pilih Tanggal</label>
                                <input type="date" name="tanggal" id="input_tanggal" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <!-- B. Form Mingguan -->
                        <div id="filter_mingguan" class="filter-section d-none">
                            <div class="row">
                                <div class="col">
                                    <label>Dari Tanggal</label>
                                    <input type="date" name="start_date" id="input_start_date" class="form-control">
                                </div>
                                <div class="col">
                                    <label>Sampai Tanggal</label>
                                    <input type="date" name="end_date" id="input_end_date" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- C. Form Bulanan -->
                        <div id="filter_bulanan" class="filter-section d-none">
                            <div class="row">
                                <div class="col">
                                    <label>Bulan</label>
                                    <select name="bulan" class="form-select">
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <label>Tahun</label>
                                    <select name="tahun_bulan" class="form-select">
                                        @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- D. Form Semester -->
                        <div id="filter_semester" class="filter-section d-none">
                            <div class="row">
                                <div class="col">
                                    <label>Semester</label>
                                    <select name="semester" class="form-select">
                                        <option value="ganjil">Ganjil (Jul-Des)</option>
                                        <option value="genap">Genap (Jan-Jun)</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label>Tahun Ajaran Mulai</label>
                                    <select name="tahun_semester" class="form-select">
                                        @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                            <option value="{{ $y }}">{{ $y }} / {{ $y+1 }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-print me-2"></i> Cetak Laporan PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- CARD 2: LAPORAN INDIVIDU SISWA (TRANSKRIP) -->
            <div class="shadow card border-left-danger">
                <div class="bg-white card-header text-danger fw-bold">
                    <i class="fas fa-user-graduate me-2"></i> Laporan Riwayat Siswa (Transkrip)
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="mb-2 col-md-8">
                            <label class="form-label">Cari Nama Siswa:</label>
                            <select id="student_selector" class="form-select select2">
                                <option value="">-- Ketik Nama Siswa --</option>
                                @foreach($students as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->nis }} - {{ $s->name }} ({{ $s->classroom->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2 col-md-4 d-grid">
                            <a href="#" id="btn_print_student" class="btn btn-danger disabled" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Cetak Rapor
                            </a>
                        </div>
                    </div>
                    <div class="form-text text-muted small">
                        Pilih nama siswa untuk mencetak riwayat kehadiran lengkapnya dalam satu semester/tahun.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


@stack('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .border-left-danger { border-left: 5px solid #dc3545; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Init Select2 untuk pencarian
        $('.select2').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: 'Pilih opsi...'
        });

        // Logic Tombol Print Siswa (Enable button saat siswa dipilih)
        $('#student_selector').on('change', function() {
            let studentId = $(this).val();
            let btn = $('#btn_print_student');

            if (studentId) {
                // Ubah href tombol menjadi route cetak siswa
                btn.attr('href', "/report/student/" + studentId);
                btn.removeClass('disabled');
            } else {
                btn.attr('href', "#");
                btn.addClass('disabled');
            }
        });

        // 4. VALIDASI UTAMA: Form Filter Laporan
            $('#filterForm').on('submit', function(e) {
                let periode = $('#periode_selector').val();
                let isValid = true;
                let errorMessage = '';

                // Validasi Harian
                if (periode === 'harian') {
                    let tanggal = $('#input_tanggal').val();
                    if (!tanggal) {
                        isValid = false;
                        errorMessage = 'Mohon pilih Tanggal untuk laporan harian.';
                    }
                }

                // Validasi Mingguan
                if (periode === 'mingguan') {
                    let start = $('#input_start_date').val();
                    let end = $('#input_end_date').val();

                    if (!start || !end) {
                        isValid = false;
                        errorMessage = 'Mohon lengkapi Tanggal Awal dan Tanggal Akhir.';
                    } else if (new Date(start) > new Date(end)) {
                        isValid = false;
                        errorMessage = 'Tanggal Awal tidak boleh lebih besar dari Tanggal Akhir.';
                    }
                }

                // Jika Tidak Valid, Munculkan SweetAlert
                if (!isValid) {
                    e.preventDefault(); // Stop form submission
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: errorMessage,
                        confirmButtonText: 'Oke, Saya Lengkapi',
                        confirmButtonColor: '#4e73df'
                    });
                }
            });
    });

    // Logic Toggle Filter Periode (Switch Case Tampilan)
    function toggleFilter() {
        let selected = document.getElementById('periode_selector').value;

        // Sembunyikan semua form filter periode
        document.querySelectorAll('.filter-section').forEach(el => el.classList.add('d-none'));

        // Tampilkan form filter yang dipilih
        let target = document.getElementById('filter_' + selected);
        if(target) {
            target.classList.remove('d-none');
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('error'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'warning', // Icon kuning (peringatan)
            title: 'Data Kosong',
            text: "{{ session('error') }}",
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
</x-app-layout>