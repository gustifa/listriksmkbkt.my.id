@section('title', 'Edit Mata Pelajaran')
<x-app-layout>
    <div class="page-content">

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Mata Pelajaran</h5>
                    </div>
                    <div class="card-body">
                        
                        <form action="{{ route('subjects.update', $subject->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Nama Mata Pelajaran</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name', $subject->name) }}" required>
                                
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-text text-muted">
                                    Pastikan nama mata pelajaran unik (tidak kembar).
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Kode Mata Pelajaran</label>
                                <input type="text" name="code" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('code', $subject->code) }}" required>
                                
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-text text-muted">
                                    Pastikan nama mata pelajaran unik (tidak kembar).
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>      