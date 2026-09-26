@section('title', 'Laporan Datang dan Pulang')
<x-app-layout>
    <div class="page-content">
        <div class="py-4 container-fluid">

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h1 class="mb-0 text-gray-800 h3">Laporan Absensi Siswa</h1>
            </div>

            <!-- CARD FILTER -->
            <div class="mb-4 shadow card border-left-primary">
                <div class="py-3 card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('daily_attendance.report') }}" method="GET" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <!-- 1. Filter Siswa -->
                            <div class="mb-3 col-md-3">
                                <label class="form-label fw-bold">Pilih Siswa</label>
                                <select name="student_id" id="student_selector" class="form-control select2">
                                    <option value="">-- Semua Siswa --</option>
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>
                                            {{ $s->nis }} - {{ $s->name }} ({{ $s->classroom->name ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Jenis Filter -->
                            <div class="mb-3 col-md-3">
                                <label class="form-label fw-bold">Periode Laporan</label>
                                <select name="filter_type" id="filter_type" class="form-control" onchange="toggleInputs()">
                                    <option value="harian" {{ request('filter_type') == 'harian' ? 'selected' : '' }}>Harian</option>
                                    <option value="mingguan" {{ request('filter_type') == 'mingguan' ? 'selected' : '' }}>Mingguan / Rentang</option>
                                    <option value="bulanan" {{ request('filter_type') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="semester" {{ request('filter_type') == 'semester' ? 'selected' : '' }}>Semester</option>
                                </select>
                            </div>

                            <!-- 3. Input Dinamis -->
                            <div class="mb-3 col-md-4">

                                <!-- Input Harian -->
                                <div id="input_harian" class="filter-group">
                                    <label class="form-label fw-bold">Tanggal</label>
                                    <input type="date" name="date" class="form-control" value="{{ request('date') ?? date('Y-m-d') }}">
                                </div>

                                <!-- Input Mingguan (Rentang) -->
                                <div id="input_mingguan" class="filter-group d-none">
                                    <label class="form-label fw-bold">Rentang Tanggal</label>
                                    <div class="input-group">
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                        <span class="input-group-text bg-light">-</span>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                </div>

                                <!-- Input Bulanan -->
                                <div id="input_bulanan" class="filter-group d-none">
                                    <label class="form-label fw-bold">Bulan & Tahun</label>
                                    <div class="input-group">
                                        <select name="month" class="form-control">
                                            @foreach(range(1,12) as $m)
                                                <option value="{{ $m }}" {{ (request('month') ?? date('n')) == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="number" name="year" class="form-control" placeholder="Tahun" value="{{ request('year') ?? date('Y') }}">
                                    </div>
                                </div>

                                <!-- Input Semester -->
                                <div id="input_semester" class="filter-group d-none">
                                    <label class="form-label fw-bold">Semester & Tahun</label>
                                    <div class="input-group">
                                        <select name="semester" class="form-control">
                                            <option value="ganjil" {{ request('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil (Juli - Des)</option>
                                            <option value="genap" {{ request('semester') == 'genap' ? 'selected' : '' }}>Genap (Jan - Juni)</option>
                                        </select>
                                        <input type="number" name="year" class="form-control" placeholder="Tahun Awal Ajaran" value="{{ request('year') ?? date('Y') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Cari -->
                            <div class="mb-3 col-md-2">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="submit" class="shadow-sm btn btn-primary w-100">
                                    <i class="fas fa-filter fa-sm"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RINGKASAN STATISTIK -->
            <div class="mb-4 row">
                <div class="mb-4 col-xl-3 col-md-6">
                    <div class="py-2 shadow card border-left-success h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="mr-2 col">
                                    <div class="mb-1 text-xs font-weight-bold text-success text-uppercase">Hadir (Tepat Waktu)</div>
                                    <div class="mb-0 text-gray-800 h5 font-weight-bold">{{ $summary['hadir'] }}</div>
                                </div>
                                <div class="col-auto"><i class="text-gray-300 fas fa-check-circle fa-2x"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-4 col-xl-3 col-md-6">
                    <div class="py-2 shadow card border-left-warning h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="mr-2 col">
                                    <div class="mb-1 text-xs font-weight-bold text-warning text-uppercase">Terlambat</div>
                                    <div class="mb-0 text-gray-800 h5 font-weight-bold">{{ $summary['terlambat'] }}</div>
                                </div>
                                <div class="col-auto"><i class="text-gray-300 fas fa-clock fa-2x"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-4 col-xl-3 col-md-6">
                    <div class="py-2 shadow card border-left-warning h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="mr-2 col">
                                    <div class="mb-1 text-xs font-weight-bold text-warning text-uppercase">Izin</div>
                                    <div class="mb-0 text-gray-800 h5 font-weight-bold">{{ $summary['izin'] }}</div>
                                </div>
                                <div class="col-auto"><i class="text-gray-300 fas fa-clock fa-2x"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-4 col-xl-3 col-md-6">
                    <div class="py-2 shadow card border-left-warning h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="mr-2 col">
                                    <div class="mb-1 text-xs font-weight-bold text-warning text-uppercase">Sakit</div>
                                    <div class="mb-0 text-gray-800 h5 font-weight-bold">{{ $summary['sakit'] }}</div>
                                </div>
                                <div class="col-auto"><i class="text-gray-300 fas fa-clock fa-2x"></i></div>
                            </div>
                        </div>
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
        <script>
            // Fungsi untuk mengubah tampilan input berdasarkan filter dropdown
            function toggleInputs() {
                const type = document.getElementById('filter_type').value;

                // Sembunyikan semua grup input
                document.querySelectorAll('.filter-group').forEach(el => el.classList.add('d-none'));

                // Tampilkan yang dipilih
                const activeInput = document.getElementById('input_' + type);
                if(activeInput) {
                    activeInput.classList.remove('d-none');
                }
            }

            // Jalankan saat halaman selesai dimuat (agar state filter tersimpan setelah submit)
            document.addEventListener('DOMContentLoaded', function() {
                toggleInputs();
            });
        </script>
        <script>
            function toggleFilterInputs() {
                // Sembunyikan semua input dulu
                document.querySelectorAll('.filter-input').forEach(el => el.classList.add('d-none'));
                
                // Ambil value dropdown
                const type = document.getElementById('filter_type').value;
                
                // Tampilkan input yang sesuai
                document.getElementById('input_' + type).classList.remove('d-none');
            }

            // Jalankan saat halaman diload agar input tetap sesuai pilihan terakhir
            document.addEventListener('DOMContentLoaded', toggleFilterInputs);
        </script>
</x-app-layout>
