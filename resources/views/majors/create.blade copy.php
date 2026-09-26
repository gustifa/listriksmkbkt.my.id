@section('title', 'Tambah Jurusan')

<x-app-layout>
    <div class="page-content">
        <div class="col-md-8 mx-auto">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">Tambah Jurusan / Konsentrasi Keahlian</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('majors.store') }}" method="POST">
                        @csrf
                        <!-- Nama Program Keahlian -->
                        <div class="mb-3">
                            <label class="fw-bold mb-1">Program Keahlian</label>
                            <input type="text" name="program_name" class="form-control @error('program_name') is-invalid @enderror"
                                placeholder="Contoh: Ketenagalistrikan" value="{{ old('program_name') }}" required>
                            @error('program_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nama Konsentrasi Keahlian (Major Name) -->
                        <div class="mb-3">
                            <label class="fw-bold mb-1">Nama Konsentrasi Keahlian</label>
                            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Contoh: Teknik Instalasi Tenaga Listrik" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>



                        <div class="row">
                            <!-- Ketua Program Keahlian -->
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold mb-1">Ketua Program Keahlian</label>
                                <select name="head_of_major" class="form-select @error('head_of_major') is-invalid @enderror">
                                    <option value="">-- Pilih Ketua Program --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->name }}" {{ old('head_of_major') == $teacher->name ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('head_of_major')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kepala Bengkel -->
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold mb-1">Kepala Bengkel (Kabeng)</label>
                                <select name="head_of_workshop" class="form-select @error('head_of_workshop') is-invalid @enderror">
                                    <option value="">-- Pilih Kepala Bengkel --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->name }}" {{ old('head_of_workshop') == $teacher->name ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('head_of_workshop')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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

                        <div class="d-flex justify-content-between border-top pt-3">
                            <a href="{{ route('majors.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
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
