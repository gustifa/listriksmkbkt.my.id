@section('title', 'Laporan Datang dan Pulang')
<x-app-layout>
    <div class="page-content">

            <div class="mb-4 shadow card border-left-primary">
                <div class="py-3 card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('report.print.absensi') }}" method="POST" id="filterForm" target="_blank">
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

            <div class="shadow card border-left-danger">
                <div class="bg-white card-header text-danger fw-bold">
                    <i class="fas fa-user-graduate me-2"></i> Laporan Riwayat Datang & Pulang Siswa
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

            

            <!-- TABEL DATA -->
            <div class="mb-4 shadow card">
                <div class="py-3 card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Data Detail Absensi</h6>
                    <!-- Tombol Print/Export bisa ditambahkan di sini -->
                     <form action="{{route('report.print.absensi')}}" METHOD="POST" target="_blank">
                         @csrf
                        <button onclick="window.print()" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i> Cetak</button>
                     </form>
                    
                    
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                            <thead class="text-center text-white bg-dark">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Tanggal</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Jam Datang</th>
                                    <th>Jam Pulang</th>
                                    <th>Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $key => $row)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($row->created_at)->translatedFormat('l, d F Y') }}</td>
                                    <td class="text-center">{{ $row->student->nis ?? '-' }}</td>
                                    <td>{{ $row->student->name ?? 'Data Siswa Terhapus' }}</td>
                                    <td class="text-center">{{ $row->student->classroom->name ?? '-' }}</td>

                                    <!-- Jam Datang -->
                                    <td class="text-center fw-bold text-success">
                                        {{ $row->arrival_time ? \Carbon\Carbon::parse($row->arrival_time)->format('H:i') : '-' }}
                                    </td>

                                    <!-- Jam Pulang -->
                                    <td class="text-center fw-bold text-primary">
                                        {{ $row->departure_time ? \Carbon\Carbon::parse($row->departure_time)->format('H:i') : '-' }}
                                    </td>

                                    <!-- Status -->
                                    <td class="text-center">
                                        @if($row->status == 'hadir')
                                            <span class="px-3 py-2 badge badge-success">Hadir</span>
                                        @elseif($row->status == 'terlambat')
                                            <span class="px-3 py-2 badge badge-warning text-dark">Terlambat</span>
                                        @elseif($row->status == 'sakit')
                                            <span class="px-3 py-2 badge badge-info">Sakit</span>
                                        @elseif($row->status == 'izin')
                                            <span class="px-3 py-2 badge badge-primary">Izin</span>
                                        @elseif($row->status == 'alpa')
                                            <span class="px-3 py-2 badge badge-danger">Alpha</span>
                                        @else
                                            <span class="px-3 py-2 badge badge-secondary">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="py-5 text-center text-muted">
                                        <i class="mb-3 fas fa-folder-open fa-3x"></i><br>
                                        Tidak ada data absensi yang ditemukan untuk filter ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                btn.attr('href', "/report/absensi/student/" + studentId);
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
