<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Input Manual Absensi Harian</h5>
                    </div>
                    <div class="card-body">

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('daily.storeManual') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Pilih Siswa -->
                                <div class="mb-3 col-md-12">
                                    <label class="form-label fw-bold">Nama Siswa</label>
                                    <select name="student_id" class="form-select select2" required>
                                        <option value="">-- Cari Siswa --</option>
                                        <!-- Pastikan controller mengirim variabel $students -->
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->nis }} - {{ $student->name }} ({{ $student->classroom->name ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tanggal -->
                                <div class="mb-3 col-md-12">
                                    <label class="form-label fw-bold">Tanggal</label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Jam Masuk -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Jam Masuk (Datang)</label>
                                    <input type="time" name="arrival_time" class="form-control" value="07:00">
                                    <div class="form-text text-muted">Kosongkan jika siswa tidak hadir/alpa.</div>
                                </div>

                                <!-- Jam Pulang -->
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Jam Pulang</label>
                                    <input type="time" name="departure_time" class="form-control">
                                    <div class="form-text text-muted">Isi hanya jika siswa sudah pulang.</div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Status Kehadiran</label>
                                <select name="status" class="form-select">
                                    <option value="hadir">Hadir</option>
                                    <option value="terlambat">Terlambat</option>
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="alpa">Alpa</option>
                                </select>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('daily.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Simpan Data
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@push('scripts')
<!-- jQuery & Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: 'Cari Nama atau NIS...'
        });
    });
</script>
</x-app-layout>
