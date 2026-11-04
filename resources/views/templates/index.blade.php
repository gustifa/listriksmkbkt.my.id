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
                <p class="text-muted mb-0">Unggah template .docx Anda, pilih template otomatis, atau buat video naskah praktikum berdasarkan data PostgreSQL / Input Manual.</p>
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

                            <!-- Opsi Pilihan Sumber Tujuan Pembelajaran (TP) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold d-block mb-1">Sumber Tujuan Pembelajaran (TP)</label>
                                <div class="btn-group w-100 mb-2" role="group">
                                    <input type="radio" class="btn-check" name="tp_mode" id="tp_mode_db" value="database" checked onchange="toggleTpInputMode()">
                                    <label class="btn btn-outline-primary" for="tp_mode_db">
                                        <i class="fa-solid fa-database me-1"></i> Pilih dari Database
                                    </label>

                                    <input type="radio" class="btn-check" name="tp_mode" id="tp_mode_manual" value="manual" onchange="toggleTpInputMode()">
                                    <label class="btn btn-outline-primary" for="tp_mode_manual">
                                        <i class="fa-solid fa-keyboard me-1"></i> Ketik Manual TP
                                    </label>
                                </div>

                                <!-- Container Input TP Database -->
                                <div id="tp_database_container">
                                    <select name="tp_id" id="tp_id_select" class="form-select">
                                        <option value="">-- Pilih TP dari Database --</option>
                                        @foreach($tps as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->title ?? $tp->display_title ?? $tp->description ?? '-' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Container Input TP Manual -->
                                <div id="tp_manual_container" class="d-none">
                                    <textarea name="tp_manual" id="tp_manual_input" class="form-control" rows="3" placeholder="Ketikkan Tujuan Pembelajaran (TP) atau indikator praktikum di sini..."></textarea>
                                    <small class="text-muted">Contoh: Memahami prinsip kerja instalasi penerangan 1 fasa menggunakan saklar tunggal dan stop kontak.</small>
                                </div>
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
                            <label class="form-label fw-bold text-dark d-block mb-2">
                                <i class="fa-solid fa-clapperboard text-danger me-1"></i> Generate Media Video Pembelajaran (AI)
                            </label>
                            
                            <!-- Dropdown Pilihan Pembagian Part Naskah -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Pilih Pembagian Naskah (Part):</label>
                                <select id="part_count_select" class="form-select form-select-sm">
                                    <option value="auto" selected>✨ Otomatis AI (Disesuaikan Durasi)</option>
                                    <option value="2">2 Part (Pembukaan & Inti/Penutup)</option>
                                    <option value="3">3 Part (Pembukaan, Langkah Kerja, Penutup)</option>
                                    <option value="4">4 Part (Salam, K3LH/Alat, Praktikum, Evaluasi)</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" onclick="triggerGenerateVideo('short')" class="btn btn-outline-danger w-50 py-2">
                                    <i class="fa-solid fa-mobile-screen me-1"></i> Video Short (9:16)
                                </button>
                                <button type="button" onclick="triggerGenerateVideo('long')" class="btn btn-outline-dark w-50 py-2">
                                    <i class="fa-solid fa-display me-1"></i> Video Long (16:9)
                                </button>
                            </div>
                        </div>

                        <!-- Section Fitur Generate Gambar Ilustrasi AI -->
                        <div class="mt-3 pt-3 border-top">
                            <label class="form-label fw-bold text-dark d-block mb-2">
                                <i class="fa-solid fa-image text-primary me-1"></i> Generate Gambar / Skema Praktikum (AI)
                            </label>
                            <button type="button" onclick="triggerGenerateImage()" class="btn btn-outline-primary w-100 py-2">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Buat Gambar Ilustrasi dari TP
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Script AJAX Handler -->
<script>
    let currentScriptParts = [];

    // Switcher Mode Input TP (Database vs Manual)
    function toggleTpInputMode() {
        const mode = document.querySelector('input[name="tp_mode"]:checked').value;
        const dbContainer = document.getElementById('tp_database_container');
        const manualContainer = document.getElementById('tp_manual_container');

        if (mode === 'manual') {
            dbContainer.classList.add('d-none');
            manualContainer.classList.remove('d-none');
        } else {
            dbContainer.classList.remove('d-none');
            manualContainer.classList.add('d-none');
        }
    }

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

                const mode = document.querySelector('input[name="tp_mode"]:checked').value;
                const tpId = document.getElementById('tp_id_select').value;
                const tpManual = document.getElementById('tp_manual_input').value;

                if (mode === 'database' && !tpId) {
                    Swal.fire('Peringatan', 'Silakan pilih TP dari Database terlebih dahulu.', 'warning');
                    return;
                }
                if (mode === 'manual' && !tpManual.trim()) {
                    Swal.fire('Peringatan', 'Silakan ketikkan Tujuan Pembelajaran (TP) terlebih dahulu.', 'warning');
                    return;
                }

                // Masukkan input tp_mode dan tp_manual ke dalam FormData secara dinamis
                let formData = new FormData(this);
                formData.set('tp_mode', mode);
                if (mode === 'manual') {
                    formData.set('tp_manual', tpManual);
                }

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
                        const byteCharacters = atob(data.file_data);
                        const byteNumbers = new Array(byteCharacters.length);
                        for (let i = 0; i < byteCharacters.length; i++) {
                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                        }
                        const byteArray = new Uint8Array(byteNumbers);
                        const blob = new Blob([byteArray], { type: data.file_type });

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

    // 3. Handling Generate Video AI (Short & Long) Terkelompok per Part via AJAX
    function triggerGenerateVideo(format) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const mode = document.querySelector('input[name="tp_mode"]:checked').value;
        const tpId = document.getElementById('tp_id_select').value;
        const tpManual = document.getElementById('tp_manual_input').value;
        const partCount = document.getElementById('part_count_select').value;
        
        if (mode === 'database' && !tpId) {
            Swal.fire('Peringatan', 'Silakan pilih TP dari Database terlebih dahulu.', 'warning');
            return;
        }
        if (mode === 'manual' && !tpManual.trim()) {
            Swal.fire('Peringatan', 'Silakan ketikkan Tujuan Pembelajaran (TP) terlebih dahulu.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Menyusun Naskah per Part...',
            text: 'Gemini AI sedang menyusun naskah video (' + format.toUpperCase() + ') berdasarkan TP yang Anda masukkan.',
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
            body: JSON.stringify({ 
                tp_mode: mode,
                tp_id: tpId,
                tp_manual: tpManual,
                video_format: format,
                part_count: partCount 
            })
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                
                currentScriptParts = data.parts || [];

                let downloadHtml = `<p class="small text-muted mb-2">${data.message} (Engine: <strong>${data.provider}</strong>)</p>`;
                
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
                    <div class="d-flex justify-content-between align-items-center mb-2 mt-3 px-1">
                        <span class="small fw-bold text-dark"><i class="fa-solid fa-list-ol text-primary me-1"></i> Daftar Part Naskah:</span>
                        <div class="d-flex gap-1">
                            <button type="button" onclick="exportScriptToText()" class="btn btn-sm btn-outline-success py-1 px-2">
                                <i class="fa-solid fa-file-arrow-down me-1"></i> Export Text (.txt)
                            </button>
                            <button type="button" onclick="copyAllPartsScript()" id="btnCopyAll" class="btn btn-sm btn-outline-dark py-1 px-2">
                                <i class="fa-solid fa-copy me-1"></i> Salin Semua
                            </button>
                        </div>
                    </div>`;

                downloadHtml += `<div class="text-start" style="max-height: 260px; overflow-y: auto;">`;
                
                if (data.parts && data.parts.length > 0) {
                    data.parts.forEach((part, index) => {
                        downloadHtml += `
                            <div class="bg-light p-3 rounded border mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="small text-primary"><i class="fa-solid fa-layer-group me-1"></i> ${part.part_name}</strong>
                                    <button type="button" onclick="copyPartScript('part-text-${index}', 'btn-part-${index}')" id="btn-part-${index}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="fa-solid fa-copy me-1"></i> Salin Part
                                    </button>
                                </div>
                                <div id="part-text-${index}" style="font-size: 0.85rem; white-space: pre-wrap; color: #333; word-break: break-word;">${part.content}</div>
                            </div>`;
                    });
                }
                
                downloadHtml += `</div>`;

                Swal.fire({
                    icon: 'success',
                    title: 'Naskah Berhasil Di-generate!',
                    html: downloadHtml,
                    width: '620px',
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

    // 4. Handling Generate Gambar Ilustrasi AI
    function triggerGenerateImage() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const mode = document.querySelector('input[name="tp_mode"]:checked').value;
        const tpId = document.getElementById('tp_id_select').value;
        const tpManual = document.getElementById('tp_manual_input').value;

        if (mode === 'database' && !tpId) {
            Swal.fire('Peringatan', 'Silakan pilih TP dari Database terlebih dahulu.', 'warning');
            return;
        }
        if (mode === 'manual' && !tpManual.trim()) {
            Swal.fire('Peringatan', 'Silakan ketikkan Tujuan Pembelajaran (TP) terlebih dahulu.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Memproses Gambar AI...',
            text: 'AI sedang merancang ilustrasi visual/skema berdasarkan Tujuan Pembelajaran.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("{{ route('dynamic-templates.generate-image') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ tp_mode: mode, tp_id: tpId, tp_manual: tpManual })
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.status === 'success') {
                Swal.fire({
                    title: 'Gambar Berhasil Dibuat!',
                    html: `<img src="${data.image_url}" class="img-fluid rounded shadow mb-3" alt="Ilustrasi TP">
                           <br><a href="${data.image_url}" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-download me-1"></i> Unduh Gambar</a>`,
                    width: '600px',
                    confirmButtonText: 'Tutup'
                });
            } else {
                Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Kesalahan Sistem', error.message, 'error');
        });
    }

    // Helper Functions
    function copyPartScript(elementId, buttonId) {
        const scriptElem = document.getElementById(elementId);
        if (!scriptElem) return;

        const textToCopy = scriptElem.innerText;
        navigator.clipboard.writeText(textToCopy).then(() => {
            const btn = document.getElementById(buttonId);
            if (btn) {
                btn.className = "btn btn-sm btn-success py-0 px-2";
                btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Tersalin!';
                setTimeout(() => {
                    btn.className = "btn btn-sm btn-outline-primary py-0 px-2";
                    btn.innerHTML = '<i class="fa-solid fa-copy me-1"></i> Salin Part';
                }, 2000);
            }
        }).catch(err => alert('Gagal menyalin: ' + err));
    }

    function copyAllPartsScript() {
        if (!currentScriptParts || currentScriptParts.length === 0) return;

        let fullText = "";
        currentScriptParts.forEach(part => {
            fullText += `=== ${part.part_name} ===\n${part.content}\n\n`;
        });

        navigator.clipboard.writeText(fullText.trim()).then(() => {
            const btn = document.getElementById('btnCopyAll');
            if (btn) {
                btn.className = "btn btn-sm btn-success py-1 px-2";
                btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Semua Tersalin!';
                setTimeout(() => {
                    btn.className = "btn btn-sm btn-outline-dark py-1 px-2";
                    btn.innerHTML = '<i class="fa-solid fa-copy me-1"></i> Salin Semua';
                }, 2000);
            }
        });
    }

    function exportScriptToText() {
        if (!currentScriptParts || currentScriptParts.length === 0) return;

        let content = "=========================================\n";
        content += "  NASKAH VIDEO PEMBELAJARAN (AI SCRIPT)  \n";
        content += "  SMK NEGERI 1 BUKITTINGGI               \n";
        content += "=========================================\n\n";

        currentScriptParts.forEach((part) => {
            content += `[ ${part.part_name.toUpperCase()} ]\n`;
            content += `${part.content}\n\n`;
            content += "-----------------------------------------\n\n";
        });

        const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Naskah_Video_AI_Part_${Date.now()}.txt`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
</x-app-layout>