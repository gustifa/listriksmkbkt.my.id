@section('title', 'Tambah Jurusan')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white fw-bold py-3">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Tambah Jurusan / Konsentrasi Keahlian</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('majors.store') }}" method="POST">
                            @csrf

                            <!-- Program Keahlian -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Program Keahlian</label>
                                <select name="program_id" class="form-select @error('program_id') is-invalid @enderror">
                                    <option value="">-- Pilih Program Keahlian --</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('program_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Konsentrasi Keahlian (Major) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Konsentrasi Keahlian (Major)</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Contoh: TEKNIK INSTALASI TENAGA LISTRIK" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kepala Bengkel -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kepala Bengkel (Kabeng)</label>
                                <select name="workshop_teacher_id" class="form-select @error('workshop_teacher_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kepala Bengkel --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('workshop_teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('workshop_teacher_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kode Jurusan -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Kode Jurusan</label>
                                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                                    value="{{ old('code') }}" placeholder="Contoh: TITL" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">Kode ini terisi otomatis dari inisial nama, namun bisa diubah manual.</div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between pt-2">
                                <a href="{{ route('majors.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-save me-1"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            nameInput.addEventListener('input', function() {
                let matches = this.value.match(/\b(\w)/g);
                codeInput.value = matches ? matches.join('').toUpperCase() : '';
            });
        });
    </script>
</x-app-layout>