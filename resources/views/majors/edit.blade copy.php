@section('title', 'Edit Jurusan')
<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Jurusan</h5>
                    </div>
                    <div class="card-body">
                        
                        <form id="editForm" action="{{ route('majors.update', $major->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Nama Jurusan</label>
                                {{-- PERUBAHAN 1: Saya tambahkan id="name" --}}
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name', $major->name) }}" required>
                                
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-text text-muted">
                                    Pastikan nama jurusan unik (tidak kembar).
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Kode Jurusan</label>
                                {{-- PERUBAHAN 2: Saya tambahkan id="code" dan readonly (opsional, jika ingin otomatis sepenuhnya) --}}
                                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" 
                                    value="{{ old('code', $major->code) }}" required>
                                
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-text text-muted">
                                    Kode ini akan terisi otomatis dari huruf depan Nama Jurusan.
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('majors.index') }}" class="btn btn-secondary">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // --- BAGIAN 1: SCRIPT GENERATE KODE OTOMATIS ---
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            // Jalankan fungsi setiap kali user mengetik di kolom Nama
            nameInput.addEventListener('input', function() {
                let text = this.value;
                
                // Logika Regex: Mengambil huruf pertama (\w) di setiap awal kata (\b)
                let matches = text.match(/\b(\w)/g);
                
                if (matches) {
                    // Gabungkan huruf, jadikan huruf besar (TKJ, TITL, dll)
                    let acronym = matches.join('').toUpperCase();
                    codeInput.value = acronym;
                } else {
                    codeInput.value = '';
                }
            });
        });

        // --- BAGIAN 2: SCRIPT SWEETALERT (YANG SEBELUMNYA) ---
        
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
            });
        @endif
        
        @if($errors->any())
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Mohon periksa kembali inputan Anda.',
            });
        @endif
    </script>
    
</x-app-layout>