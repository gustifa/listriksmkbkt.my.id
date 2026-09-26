@section('title', 'Edit Jurusan')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-warning text-dark fw-bold py-3">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Jurusan / Konsentrasi Keahlian</h5>
                    </div>
                    <div class="card-body">
                        <form id="editForm" action="{{ route('majors.update', $major->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Nama Program Keahlian -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Program Keahlian</label>
                                <input type="text" name="program_name" class="form-control @error('program_name') is-invalid @enderror"
                                    value="{{ old('program_name', $major->program_name) }}" placeholder="Contoh: Ketenagalistrikan" required>
                                @error('program_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Konsentrasi Keahlian (Major) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Konsentrasi Keahlian (Major)</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $major->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">Pastikan nama konsentrasi keahlian unik.</div>
                            </div>



                            <div class="row">
                                <!-- Ketua Program Keahlian (Dropdown) -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ketua Program Keahlian</label>
                                    <select name="head_of_major" class="form-select @error('head_of_major') is-invalid @enderror">
                                        <option value="">-- Pilih Ketua Program --</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->name }}" {{ old('head_of_major', $major->head_of_major) == $teacher->name ? 'selected' : '' }}>
                                                {{ $teacher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('head_of_major')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Kepala Bengkel (Dropdown) -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Kepala Bengkel (Kabeng)</label>
                                    <select name="head_of_workshop" class="form-select @error('head_of_workshop') is-invalid @enderror">
                                        <option value="">-- Pilih Kepala Bengkel --</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->name }}" {{ old('head_of_workshop', $major->head_of_workshop) == $teacher->name ? 'selected' : '' }}>
                                                {{ $teacher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('head_of_workshop')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Kode Jurusan -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Kode Jurusan</label>
                                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                                    value="{{ old('code', $major->code) }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">Kode ini terisi otomatis, namun tetap bisa diubah manual.</div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between pt-2">
                                <a href="{{ route('majors.index') }}" class="btn btn-secondary">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            // --- LOGIC GENERATE KODE OTOMATIS ---
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

            // --- SWEETALERT NOTIFICATIONS ---
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 2000
                });
            @endif

            @if($errors->any())
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Mohon periksa kembali inputan Anda.',
                });
            @endif
        });
    </script>
</x-app-layout>
