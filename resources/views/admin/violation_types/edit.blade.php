@section('title', 'Edit Pelanggaran')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold text-warning"><i class="fas fa-edit me-2"></i>Edit Jenis Pelanggaran</h4>
                    <a href="{{ route('admin.violation-types.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-pen me-2"></i>Perbarui Data</h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.violation-types.update', $violationType->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Pelanggaran <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $violationType->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Bobot Poin <span class="text-danger">*</span></label>
                                    <input type="number" name="points" class="form-control @error('points') is-invalid @enderror" 
                                           value="{{ old('points', $violationType->points) }}" min="1" required>
                                    @error('points') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                                    <select name="category" class="form-select @error('category') is-invalid @enderror">
                                        <option value="ringan" {{ old('category', $violationType->category) == 'ringan' ? 'selected' : '' }}>Ringan</option>
                                        <option value="sedang" {{ old('category', $violationType->category) == 'sedang' ? 'selected' : '' }}>Sedang</option>
                                        <option value="berat" {{ old('category', $violationType->category) == 'berat' ? 'selected' : '' }}>Berat</option>
                                    </select>
                                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <hr>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-warning fw-bold px-4 text-dark">
                                    <i class="fas fa-save me-1"></i> Perbarui Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>