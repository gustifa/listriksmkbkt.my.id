@section('title', 'Edit Program Keahlian')

<x-app-layout>
    <div class="page-content">
        <div class="col-md-8 mx-auto">
            <div class="card shadow border-0">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-edit me-2"></i> Edit Program Keahlian</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('programs.update', $program->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="fw-bold mb-1">Nama Program Keahlian</label>
                            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name', $program->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-1">Kode Program</label>
                            <input id="code" type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                value="{{ old('code', $program->code) }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-1">Ketua Program Keahlian (Penilai)</label>
                            <select name="program_teacher_id" class="form-select @error('program_teacher_id') is-invalid @enderror">
                                <option value="">-- Pilih Guru / Ketua Program --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('program_teacher_id', $program->program_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
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
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fitur auto-generate kode singkatan (opsional pada form edit)
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