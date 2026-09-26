@section('title', 'Mapping Kelas PKL')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i> Mapping Kelas PKL</h5>
                        <small>Aktifkan kelas yang sedang periode PKL</small>
                    </div>
                    <div class="card-body">
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis small">
                            <i class="fas fa-info-circle me-1"></i> 
                            <strong>Info:</strong> Siswa yang berada di kelas yang <strong>tidak dicentang</strong> tidak akan bisa mengakses menu Pemilihan Tempat, Absensi, maupun Jurnal PKL.
                        </div>

                        <form action="{{ route('admin.pkl.mapping.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold">Daftar Kelas</span>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                    <label class="form-check-label small text-muted" for="checkAll">Pilih Semua</label>
                                </div>
                            </div>

                            <div class="row g-3">
                                @foreach($classrooms as $class)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card h-100 border shadow-sm {{ $class->is_pkl_active ? 'border-primary bg-primary-subtle' : 'bg-light' }}">
                                            <div class="card-body d-flex align-items-center">
                                                <div class="form-check form-switch w-100">
                                                    <input class="form-check-input class-checkbox" type="checkbox" 
                                                           name="active_classes[]" 
                                                           value="{{ $class->id }}" 
                                                           id="class_{{ $class->id }}"
                                                           {{ $class->is_pkl_active ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold stretched-link" for="class_{{ $class->id }}">
                                                        {{ $class->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 border-top pt-3 text-end">
                                <button type="submit" class="btn btn-primary fw-bold px-4">
                                    <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Script Check All
        document.getElementById('checkAll').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.class-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                toggleCardStyle(cb);
            });
        });

        // Script Visual Change on Click
        document.querySelectorAll('.class-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                toggleCardStyle(this);
            });
        });

        function toggleCardStyle(checkbox) {
            let card = checkbox.closest('.card');
            if (checkbox.checked) {
                card.classList.remove('bg-light');
                card.classList.add('border-primary', 'bg-primary-subtle');
            } else {
                card.classList.remove('border-primary', 'bg-primary-subtle');
                card.classList.add('bg-light');
            }
        }
    </script>
    @endpush
</x-app-layout>