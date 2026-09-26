@section('title', 'Tambah Jenis Pelanggaran')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i>Tambah Jenis Pelanggaran</h4>
                <p class="mb-0 text-muted">Tambahkan jenis pelanggaran baru ke dalam sistem.</p>
            </div>
            <a href="{{ route('admin.violation-types.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="shadow-lg card border-0">
                    <div class="text-white card-header bg-primary">
                        <h5 class="mb-0 card-title"><i class="fas fa-exclamation-triangle me-2"></i>Formulir Pelanggaran</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Form Input -->
                        <form action="{{ route('admin.violation-types.store') }}" method="POST">
                            @csrf

                            <!-- Nama Pelanggaran -->
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Nama Pelanggaran <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Terlambat Masuk Sekolah" required>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Poin Pelanggaran -->
                            <div class="mb-3">
                                <label for="points" class="form-label fw-bold">Poin Pelanggaran <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('points') is-invalid @enderror" id="points" name="points" value="{{ old('points', 5) }}" min="1" required>
                                <div class="form-text text-muted">Masukkan bobot poin untuk pelanggaran ini (Contoh: 5, 10, 20).</div>
                                @error('points')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Kategori Pelanggaran -->
                            <div class="mb-4">
                                <label for="category" class="form-label fw-bold">Kategori Pelanggaran <span class="text-danger">*</span></label>
                                <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                    <option value="" selected disabled>-- Pilih Kategori --</option>
                                    <option value="ringan" {{ old('category') == 'ringan' ? 'selected' : '' }}>Ringan</option>
                                    <option value="sedang" {{ old('category') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                                    <option value="berat" {{ old('category') == 'berat' ? 'selected' : '' }}>Berat</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Tombol Simpan -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                    <i class="fas fa-save me-2"></i> Simpan Data
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>