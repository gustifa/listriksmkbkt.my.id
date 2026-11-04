@section('title', 'Generator Perangkat Ajar Dinamis')

<!-- Font Awesome Icons & SweetAlert2 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<x-app-layout>
<div class="bg-light py-4 min-vh-100">

    <div class="container">
        
        <!-- Header & Navigasi Admin -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="fw-bold text-dark mb-1">
                    <i class="fa-solid fa-file-word text-primary me-2"></i> Generator Perangkat Ajar & Video (AI)
                </h2>
                <p class="text-muted mb-0">Unggah template .docx Anda, pilih template otomatis, atau buat video naskah praktikum berdasarkan data PostgreSQL.</p>
            </div>
            <div>
                <a href="{{ route('admin.api-keys.index') }}" class="btn btn-outline-dark">
                    <i class="fa-solid fa-key me-1"></i> Kelola API Key AI
                </a>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- Form Upload Template Word -->
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white fw-bold py-3 border-0">
                        <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i> Upload Template Word (.docx)
                    </div>
                    <div class="card-body pt-0">
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
                            <button type="submit" id="btnUpload" class="btn btn-primary w-100 py-2">
                                <i class="fa-solid fa-upload me-1"></i> Unggah Template
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Form Generate Dokumen & Video AI -->
            <div class="col-md-7">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white fw-bold py-3 border-0">
                        <i class="fa-solid fa-wand-magic-sparkles text-warning me-2"></i> Generate Dokumen & Video AI
                    </div>
                    <div class="card-body pt-0">
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
                                <select name="tp_id" id="tp_id_select" required class="form-select">
                                    <option value="">-- Pilih TP --</option>
                                    @foreach($tps as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->title ?? $tp->display_title ?? $tp->description ?? '-' }}</option>
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

                            <!-- Tombol Generate Dokumen Word -->
                            <button type="submit" id="btnGenerate" class="btn btn-success w-100 py-2 fw-bold">
                                <i class="fa-solid fa-file-word me-1"></i> Process & Generate Word (.docx)
                            </button>
                        </form>

                        <!-- Section Fitur Generate Video AI -->
                        <div class="mt-4 pt-3 border-top">
                            <label class="form-label fw-bold text-dark d-block">
                                <i class="fa-solid fa-clapperboard text-danger me-1"></i> Generate Media Video Pembelajaran (AI)
                            </label>
                            <div class="d-flex gap-2">
                                <button type="button" onclick="triggerGenerateVideo('short')" class="btn btn-outline-danger w-50 py-2">
                                    <i class="fa-solid fa-mobile-screen me-1"></i> Video Short (9:16)
                                </button>
                                <button type="button" onclick="triggerGenerateVideo('long')" class="btn btn-outline-dark w-50 py-2">
                                    <i class="fa-solid fa-display me-1"></i> Video Long (16:9)
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Script AJAX Handler (Word Download + Video Generator + SweetAlert2) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // 1. Handling Upload Form Template Word
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

        // 2. Handling Generate Form Dokumen Word via Base64 Direct Download
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

    // 3. Handling Generate Video AI (Short & Long) via AJAX
    function triggerGenerateVideo(format) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const tpId = document.getElementById('tp_id_select').value;
        
        if (!tpId) {
            Swal.fire({
                icon: 'warning',
                title: 'PILIH TP TERLEBIH DAHULU',
                text: 'Silakan pilih Tujuan Pembelajaran (TP) pada dropdown di atas.'
            });
            return;
        }

        Swal.fire({
            title: 'Menyusun Naskah & Merender Video...',
            text: 'Gemini AI sedang menyusun script dan mengeksekusi engine video/audio (' + format.toUpperCase() + ').',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("{{ route('dynamic-templates.generate-video') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ tp_id: tpId, video_format: format })
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                
                let downloadHtml = `<p class="small text-muted mb-3">${data.message} (Provider: <strong>${data.provider}</strong>)</p>`;
                
                if (data.video_url) {
                    downloadHtml += `<a href="${data.video_url}" target="_blank" class="btn btn-success w-100 py-2 mb-3"><i class="fa-solid fa-download me-1"></i> Unduh Video MP4</a>`;
                }
                if (data.audio_url) {
                    downloadHtml += `
                        <div class="mb-3 text-start">
                            <label class="fw-bold small">Preview Voiceover Bahasa Indonesia:</label>
                            <audio controls class="w-100 mt-1"><source src="${data.audio_url}" type="audio/mpeg"></audio>
                        </div>`;
                }

                downloadHtml += `
                    <div class="text-start bg-light p-3 rounded border">
                        <strong class="d-block mb-1 small text-dark">Naskah Pengisi Suara (AI Scriptwriter):</strong>
                        <div style="max-height: 150px; overflow-y: auto; font-size: 0.85rem; white-space: pre-wrap;">${data.script}</div>
                    </div>`;

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Dibuat!',
                    html: downloadHtml,
                    confirmColor: '#0d6efd'
                });

            } else {
                Swal.fire('Gagal Generate', data.message || 'Terjadi kesalahan.', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Kesalahan Sistem', error.message, 'error');
        });
    }
</script>
</x-app-layout>