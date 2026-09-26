<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="shadow card">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <i class="fas fa-user-edit me-2"></i> Edit Data Siswa
                    </div>
                    <div class="card-body">

                        <form action="{{ route('students.update', $student->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-bold">NIS</label>
                                <input type="number" name="nis" class="form-control" value="{{ old('nis', $student->nis) }}" required>
                                @error('nis') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $student->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Kelas</label>
                                <select name="classroom_id" class="form-select select2" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classrooms as $class)
                                        <option value="{{ $class->id }}" {{ $student->classroom_id == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nomor HP Orang Tua (WhatsApp)</label>
                                <input type="number" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}" placeholder="Contoh: 08123456789">
                                <div class="form-text">Nomor ini digunakan untuk notifikasi kehadiran otomatis.</div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('students.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: "bootstrap-5",
            width: '100%'
        });
    });
</script>
@endpush
