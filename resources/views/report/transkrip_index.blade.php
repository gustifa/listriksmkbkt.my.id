<x-app-layout>
    <div class="page-content py-3">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    <i class="fas fa-file-invoice me-2"></i> Cetak Transkrip Absensi Siswa
                </div>
                <div class="card-body p-4">
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form id="form-transkrip" method="GET" action="{{ route('transkrip.print') }}" target="_blank">
                        <div class="row g-3">
                            
                            <!-- 1. OPSI CETAK -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Opsi Cetak</label>
                                <select id="mode" name="mode" class="form-select" onchange="toggleModeOptions()">
                                    <option value="student">Per Perorangan Siswa</option>
                                    @if(!$isWaliKelas)
                                        <option value="classroom">Per Kelas / Rombel</option>
                                        <option value="all">Seluruh Kelas (Semua Siswa)</option>
                                    @else
                                        <option value="classroom">Seluruh Siswa Kelas Binaan</option>
                                    @endif
                                </select>
                            </div>

                            <!-- Pilih Siswa (Mode: student) -->
                            <div class="col-md-3" id="container-student">
                                <label class="form-label fw-bold">Pilih Siswa</label>
                                <select id="student_id" name="student_id" class="form-select select2">
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach($students as $st)
                                        <option value="{{ $st->id }}">
                                            {{ $st->name }} (NIS: {{ $st->nis }}) - {{ $st->classroom->name ?? 'Tanpa Kelas' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pilih Kelas (Mode: classroom untuk Admin/BK) -->
                            @if(!$isWaliKelas)
                            <div class="col-md-3 d-none" id="container-classroom">
                                <label class="form-label fw-bold">Pilih Kelas</label>
                                <select id="classroom_id" name="classroom_id" class="form-select select2">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classrooms as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Rentang Tanggal -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Dari Tanggal</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ date('Y-m-01') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Sampai Tanggal</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ date('Y-m-t') }}" required>
                            </div>

                            <!-- 2. CHECKBOX KOMPONEN ABSENSI DINAMIS -->
                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold d-block">Pilih Komponen Absensi yang Dicetak:</label>
                                <div class="d-flex gap-4 p-3 bg-light rounded border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="include_gate" id="include_gate" value="1" checked>
                                        <label class="form-check-label fw-bold" for="include_gate">
                                            <i class="fas fa-door-open text-primary me-1"></i> Absensi Gerbang (Daily)
                                        </label>
                                    </div>

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="include_learning" id="include_learning" value="1" checked>
                                        <label class="form-check-label fw-bold" for="include_learning">
                                            <i class="fas fa-book-reader text-success me-1"></i> Absensi Pembelajaran (KBM)
                                        </label>
                                    </div>

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="include_prayer" id="include_prayer" value="1" checked>
                                        <label class="form-check-label fw-bold" for="include_prayer">
                                            <i class="fas fa-mosque text-warning me-1"></i> Absensi Sholat & Ibadah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Cetak -->
                            <div class="col-12 text-end mt-3">
                                <button type="submit" class="btn btn-success fw-bold px-4 py-2">
                                    <i class="fas fa-print me-1"></i> Cetak Transkrip
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- STYLES & SCRIPTS SELECT2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            initSelect2();
        });

        function initSelect2() {
            $('.select2').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "-- Pilih Data --",
                allowClear: true
            });
        }

        function toggleModeOptions() {
            const mode = $('#mode').val();
            
            if (mode === 'student') {
                $('#container-student').removeClass('d-none');
                $('#container-classroom').addClass('d-none');
            } else if (mode === 'classroom') {
                @if(!$isWaliKelas)
                    $('#container-student').addClass('d-none');
                    $('#container-classroom').removeClass('d-none');
                @else
                    $('#container-student').addClass('d-none');
                    $('#container-classroom').addClass('d-none');
                @endif
            } else {
                $('#container-student').addClass('d-none');
                $('#container-classroom').addClass('d-none');
            }

            initSelect2();
        }
    </script>
</x-app-layout>