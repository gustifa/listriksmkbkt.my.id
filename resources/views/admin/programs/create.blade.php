@section('title', 'Tambah Program Keahlian')

<x-app-layout>
    <div class="page-content">
        <div class="col-md-8 mx-auto">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-plus-circle me-2"></i> Tambah Program Keahlian</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('programs.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="fw-bold mb-1">Nama Program Keahlian</label>
                            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                placeholder="Contoh: Teknik Energi Terbarukan" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-1">Kode Program</label>
                            <input id="code" type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                placeholder="Otomatis terisi (Contoh: TET)" value="{{ old('code') }}" required>
                            <small class="text-muted">Kode disarankan menggunakan singkatan huruf besar.</small>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-1">Ketua Program Keahlian (Penilai)</label>
                            <select name="program_teacher_id" class="form-select @error('program_teacher_id') is-invalid @enderror">
                                <option value="">-- Pilih Guru / Ketua Program --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('program_teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih guru yang bertugas sebagai Ketua Program Keahlian.</small>
                            @error('program_teacher_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        
                        <div class="d-flex justify-content-between pt-2">
                            <a href="{{ route('programs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save me-1"></i> Simpan Program
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fitur auto-generate kode singkatan (Acronym)
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            if(nameInput && codeInput) {
                nameInput.addEventListener('input', function() {
                    let text = this.value;
                    let matches = text.match(/\b(\w)/g);
                    
                    if (matches) {
                        let acronym = matches.join('').toUpperCase();
                        codeInput.value = acronym;
                    } else {
                        codeInput.value = '';
                    }
                });
            }
        });
    </script>
</x-app-layout>