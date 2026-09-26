@section('title', 'Tambah Jurusan')

<x-app-layout>
    <div class="page-content">
        <div class="col-md-8 mx-auto">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-plus-circle me-1"></i> Tambah Jurusan / Konsentrasi Keahlian</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('majors.store') }}" method="POST">
                        @csrf
                        
                        <!-- Pilihan Program Keahlian (Dropdown) -->
                        <div class="mb-4">
                            <label class="fw-bold mb-1">Program Keahlian</label>
                            <select name="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Program Keahlian --</option>
                                {{-- Looping dari tabel programs --}}
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }} ({{ $program->code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih program keahlian yang mewadahi konsentrasi ini.</small>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nama Konsentrasi Keahlian (Major Name) -->
                        <div class="mb-4">
                            <label class="fw-bold mb-1">Nama Konsentrasi Keahlian</label>
                            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Contoh: Teknik Instalasi Tenaga Listrik" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kepala Bengkel -->
                        <div class="mb-4">
                            <label class="fw-bold mb-1">Kepala Bengkel (Kabeng)</label>
                            <select name="workshop_teacher_id" class="form-select @error('workshop_teacher_id') is-invalid @enderror">
                                <option value="">-- Pilih Kepala Bengkel --</option>
                                {{-- Looping dari tabel teachers menggunakan ID --}}
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('workshop_teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Opsional. Pilih guru yang ditugaskan sebagai Kepala Bengkel.</small>
                            @error('workshop_teacher_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kode Jurusan (Auto Generated) -->
                        <div class="mb-4">
                            <label class="fw-bold mb-1">Kode Singkatan</label>
                            <input id="code" type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                placeholder="Otomatis terisi (Contoh: TITL)" value="{{ old('code') }}" required>
                            <small class="text-muted">Gunakan huruf besar, contoh: TITL, TAV, DPIB.</small>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between pt-2">
                            <a href="{{ route('majors.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save me-1"></i> Simpan Data Jurusan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- CDN SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- LOGIC GENERATE KODE OTOMATIS ---
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            if(nameInput && codeInput) {
                nameInput.addEventListener('input', function() {
                    let text = this.value;

                    // Mengambil huruf pertama dari setiap kata (Regex)
                    let matches = text.match(/\b(\w)/g);

                    if (matches) {
                        // Gabungkan huruf dan jadikan UPPERCASE
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