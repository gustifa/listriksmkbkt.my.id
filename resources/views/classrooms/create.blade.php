@section('title')
   Setting Kelas
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Tambah Kelas Baru</h5>
                    </div>
                    <div class="card-body">
                        
                        <form action="{{ route('classrooms.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                    placeholder="Contoh: XII RPL 1" value="{{ old('name') }}" required>
                                
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-text text-muted">
                                    Gunakan format baku agar seragam, misal: Tingkat Spasi Jurusan Spasi Nomor (Contoh: X TKJ 2).
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('classrooms.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Simpan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>