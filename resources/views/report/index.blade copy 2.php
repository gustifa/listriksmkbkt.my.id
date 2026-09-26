<x-app-layout>
    <div class="page-content">
        <div class="col-md-12">
            <div class="mx-auto shadow card col-md-12">
                <div class="text-white card-header bg-primary">
                    <h5 class="mb-0">Cetak Laporan Absensi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.print') }}" method="POST" target="_blank">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Periode Laporan:</label>
                            <select id="periode_selector" name="periode" class="form-select" onchange="toggleFilter()">
                                <option value="harian">Harian</option>
                                <option value="mingguan">Mingguan (Rentang Tanggal)</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="semester">Semester</option>
                            </select>
                        </div>

                        <hr>

                        <div id="filter_harian" class="filter-section">
                            <div class="mb-3">
                                <label>Pilih Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div id="filter_mingguan" class="filter-section d-none">
                            <div class="row">
                                <div class="col">
                                    <label>Dari Tanggal</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="col">
                                    <label>Sampai Tanggal</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                            </div>
                        </div>

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

                        <div id="filter_semester" class="filter-section d-none">
                            <div class="alert alert-info small">
                                <strong>Info:</strong> <br>
                                Ganjil = Juli s/d Desember.<br>
                                Genap = Januari s/d Juni.
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label>Semester</label>
                                    <select name="semester" class="form-select">
                                        <option value="ganjil">Ganjil</option>
                                        <option value="genap">Genap</option>
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
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-printer"></i> Cetak PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>


        </div>

        <script>
            function toggleFilter() {
                // Sembunyikan semua
                document.querySelectorAll('.filter-section').forEach(el => el.classList.add('d-none'));

                // Ambil value dropdown
                let selected = document.getElementById('periode_selector').value;

                // Tampilkan yang dipilih
                document.getElementById('filter_' + selected).classList.remove('d-none');
            }
        </script>
    </div>
</x-app-layout>
