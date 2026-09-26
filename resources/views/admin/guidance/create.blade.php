@section('title', 'Input Data BK/Pelanggaran')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i>Catat Pelanggaran / Pembinaan</h4>
                <p class="mb-0 text-muted">Formulir input data Bimbingan Konseling & Kedisiplinan Siswa</p>
            </div>
            <a href="{{ route('admin.guidance.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="shadow-lg card border-0">
                    <div class="text-white card-header bg-primary">
                        <h5 class="mb-0 card-title"><i class="fas fa-user-tag me-2"></i>Form Input Data</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- 1. PILIH SISWA -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Siswa <span class="text-danger">*</span></label>
                            <select id="student_select" class="form-select select2" onchange="updateFormAction()">
                                <option value="" selected disabled>-- Cari Nama / NIS Siswa --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->nis }} - {{ $student->name }} ({{ $student->classroom->name ?? 'No Class' }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">Cari siswa berdasarkan Nama atau NIS.</div>
                        </div>

                        <!-- 2. PILIH JENIS INPUT (TABS) -->
                        <ul class="mb-3 nav nav-pills nav-fill" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold" id="pills-violation-tab" data-bs-toggle="pill" data-bs-target="#pills-violation" type="button" role="tab" onclick="setMode('violation')">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Catat Pelanggaran
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold" id="pills-guidance-tab" data-bs-toggle="pill" data-bs-target="#pills-guidance" type="button" role="tab" onclick="setMode('guidance')">
                                    <i class="fas fa-hands-helping me-1"></i> Catat Pembinaan (BK)
                                </button>
                            </li>
                        </ul>

                        <!-- FORM WRAPPER -->
                        <!-- Action akan diupdate via JS berdasarkan siswa dan mode -->
                        <form id="mainForm" method="POST">
                            @csrf
                            
                            <div class="tab-content" id="pills-tabContent">
                                
                                <!-- A. FORM PELANGGARAN -->
                                <div class="tab-pane fade show active" id="pills-violation" role="tabpanel">
                                    <div class="p-3 border rounded bg-light-danger border-danger-subtle">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Jenis Pelanggaran <span class="text-danger">*</span></label>
                                            <select name="violation_type_id" class="form-select">
                                                <option value="" selected disabled>-- Pilih Pelanggaran --</option>
                                                @foreach($violationTypes as $type)
                                                    <option value="{{ $type->id }}">
                                                        [{{ ucfirst($type->category) }}] {{ $type->name }} ({{ $type->points }} Poin)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Tanggal Kejadian <span class="text-danger">*</span></label>
                                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Catatan / Kronologi</label>
                                            <textarea name="note" class="form-control" rows="3" placeholder="Deskripsikan kejadian pelanggaran..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- B. FORM PEMBINAAN -->
                                <div class="tab-pane fade" id="pills-guidance" role="tabpanel">
                                    <div class="p-3 border rounded bg-light-success border-success-subtle">
                                        <input type="hidden" name="role_context" value="guru_bk"> <!-- Default role -->
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Tanggal Pembinaan <span class="text-danger">*</span></label>
                                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Rangkuman Masalah <span class="text-danger">*</span></label>
                                            <textarea name="problem_summary" class="form-control" rows="2" placeholder="Inti permasalahan siswa..."></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nasihat / Solusi <span class="text-danger">*</span></label>
                                            <textarea name="advice" class="form-control" rows="3" placeholder="Saran atau solusi yang diberikan..."></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Komitmen Siswa</label>
                                            <textarea name="student_commitment" class="form-control" rows="2" placeholder="Janji siswa untuk perbaikan..."></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Status Kasus</label>
                                            <select name="status" class="form-select">
                                                <option value="open">Masih Dalam Pantauan</option>
                                                <option value="resolved">Selesai (Resolved)</option>
                                                <option value="escalated">Eskalasi (Lanjut ke Jenjang Lebih Tinggi)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-4 d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold" id="btnSubmit" disabled>
                                    <i class="fas fa-save me-2"></i> Simpan Data
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Select2 (Pastikan sudah di-include di layout utama atau tambahkan CDN disini) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        let currentMode = 'violation'; // Default mode
        
        $(document).ready(function() {
            // Inisialisasi Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        });

        function setMode(mode) {
            currentMode = mode;
            updateFormAction();
            
            // Ubah warna tombol submit sesuai mode
            const btn = document.getElementById('btnSubmit');
            if (mode === 'violation') {
                btn.className = 'btn btn-danger btn-lg fw-bold';
                btn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Simpan Pelanggaran';
            } else {
                btn.className = 'btn btn-success btn-lg fw-bold';
                btn.innerHTML = '<i class="fas fa-hands-helping me-2"></i> Simpan Pembinaan';
            }
        }

        function updateFormAction() {
            const studentId = document.getElementById('student_select').value;
            const form = document.getElementById('mainForm');
            const btn = document.getElementById('btnSubmit');

            if (!studentId) {
                btn.disabled = true;
                return;
            }

            btn.disabled = false;

            // Generate URL Dinamis berdasarkan mode dan ID siswa
            // Route format: admin.violation.store -> /admin/guidance/{id}/violation
            // Route format: admin.guidance.store -> /admin/guidance/{id}/store
            
            let url = "";
            if (currentMode === 'violation') {
                url = "{{ route('admin.violation.store', ':id') }}";
            } else {
                url = "{{ route('admin.guidance.store', ':id') }}";
            }

            // Replace placeholder :id dengan ID siswa yang dipilih
            form.action = url.replace(':id', studentId);
        }
    </script>
    @endpush
    
    <style>
        .bg-light-danger { background-color: #fff5f5; }
        .bg-light-success { background-color: #f0fff4; }
        .border-danger-subtle { border-color: #fcc; }
        .border-success-subtle { border-color: #cfc; }
    </style>
</x-app-layout>
