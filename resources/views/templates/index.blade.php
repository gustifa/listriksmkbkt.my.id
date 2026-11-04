
@section('title', 'Generator Perangkat Ajar Dinamis')
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<x-app-layout>
<body class="bg-light py-4">

    <div class="container">
        
        <!-- Header -->
        <div class="mb-4">
            <h2 class="fw-bold text-dark">
                <i class="fa-solid fa-file-word text-primary me-2"></i> Generator Perangkat Ajar (AI + Template Word)
            </h2>
            <p class="text-muted">Unggah template .docx Anda atau pilih template otomatis untuk membuat dokumen berdasarkan data PostgreSQL.</p>
        </div>

        <div class="row g-4">
            
            <!-- Form Upload Template -->
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">
                        <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i> Upload Template Word (.docx)
                    </div>
                    <div class="card-body">
                        <form id="uploadTemplateForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Template</label>
                                <input type="text" name="template_name" class="form-control" required placeholder="Contoh: Template Jobsheet 2026">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">File Dokumen (.docx)</label>
                                <input type="file" name="template_file" accept=".docx" class="form-control" required>
                                <small class="text-muted d-block mt-1">Gunakan placeholder seperti <code>${nama_mapel}</code>, <code>${tujuan_pembelajaran_detail}</code> di dalam Word.</small>
                            </div>
                            <button type="submit" id="btnUpload" class="btn btn-primary w-100">
                                <i class="fa-solid fa-upload me-1"></i> Unggah Template
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Form Generate Dokumen -->
            <div class="col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">
                        <i class="fa-solid fa-wand-magic-sparkles text-warning me-2"></i> Generate Dokumen AI
                    </div>
                    <div class="card-body">
                        <form id="generateDocumentForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Template Master</label>
                                <select name="template_path" class="form-select">
                                    <option value="auto" selected>✨ Generate Otomatis (Template Standar SMK)</option>
                                    <optgroup label="Template Unggahan Anda">
                                        @foreach($uploadedTemplates as $template)
                                            <option value="{{ $template }}">{{ basename($template) }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <small class="text-muted">Pilih "Generate Otomatis" jika Anda tidak ingin mengunggah file template .docx sendiri.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tujuan Pembelajaran (TP)</label>
                                <select name="tp_id" required class="form-select">
                                    <option value="">-- Pilih TP --</option>
                                    @foreach($tps as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Guru Pengampu</label>
                                <select name="teacher_id" required class="form-select">
                                    <option value="">-- Pilih Guru --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" id="btnGenerate" class="btn btn-success w-100 py-2">
                                <i class="fa-solid fa-gears me-1"></i> Process & Generate Word (.docx)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script AJAX + SweetAlert2 + Base64 Direct Download -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // 1. Handling Upload Form
            const uploadForm = document.getElementById('uploadTemplateForm');
            if (uploadForm) {
                uploadForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    let btn = document.getElementById('btnUpload');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Mengunggah...';

                    let formData = new FormData(this);

                    fetch("{{ route('dynamic-templates.upload') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async response => {
                        const data = await response.json();
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-upload me-1"></i> Unggah Template';

                        if (response.ok && data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmColor: '#0d6efd'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message || 'Terjadi kesalahan saat unggah.'
                            });
                        }
                    })
                    .catch(error => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-upload me-1"></i> Unggah Template';
                        Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: error.message });
                    });
                });
            }

            // 2. Handling Generate Form via Base64 Direct Download
            const generateForm = document.getElementById('generateDocumentForm');
            if (generateForm) {
                generateForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    let formData = new FormData(this);

                    Swal.fire({
                        title: 'Memproses AI & Perangkat Ajar...',
                        text: 'Sedang mengekstrak data dan menyusun dokumen Word via Gemini AI.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch("{{ route('dynamic-templates.generate') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async response => {
                        const data = await response.json();

                        if (response.ok && data.status === 'success') {
                            
                            // Decode Base64 ke Uint8Array dan buat Blob lokal
                            const byteCharacters = atob(data.file_data);
                            const byteNumbers = new Array(byteCharacters.length);
                            for (let i = 0; i < byteCharacters.length; i++) {
                                byteNumbers[i] = byteCharacters.charCodeAt(i);
                            }
                            const byteArray = new Uint8Array(byteNumbers);
                            const blob = new Blob([byteArray], { type: data.file_type });

                            // Trigger Direct Download di Browser
                            const link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = data.filename;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Unduh!',
                                text: 'Dokumen .docx berhasil terunduh otomatis ke komputer Anda.',
                                confirmColor: '#198754'
                            });

                        } else {
                            Swal.fire({ 
                                icon: 'error', 
                                title: 'Gagal Generate', 
                                text: data.message || 'Terjadi kesalahan pada server.' 
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: error.message });
                    });
                });
            }
        });
    </script>
</body>
</x-app-layout>