@section('title', 'Manajemen Master Tujuan Pembelajaran (TP)')

<x-app-layout>
    <!-- Include SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="page-content container-fluid">
        <!-- Header Halaman -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-list-check me-2"></i>Manajemen Master TP (Tujuan Pembelajaran)
                </h4>
                <p class="mb-0 text-muted small">Kelola kriteria dan indikator penilaian observasi PKL secara interaktif per konsentrasi keahlian.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" onclick="openAddModal()">
                    <i class="fas fa-plus me-1"></i> Tambah Master TP Manual / Upload File
                </button>
            </div>
        </div>

        <!-- Tabel Daftar Master TP -->
        <div class="border-0 shadow-sm card">
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover" id="tpTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" width="10%">Kode</th>
                                <th>Judul TP</th>
                                <th>Konsentrasi Keahlian</th>
                                <th class="text-center" width="12%">Skala Maks</th>
                                <th class="text-center" width="15%">Jml Indikator</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tpMasters as $tp)
                            <tr id="row-{{ $tp->id }}">
                                <td class="ps-4">
                                    <span class="badge bg-primary fs-6">{{ $tp->code }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $tp->title }}</div>
                                </td>
                                <td>
                                    @if($tp->major)
                                        <span class="badge bg-info text-dark">{{ $tp->major->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Umum (Semua Jurusan)</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-dark">
                                    1 s.d {{ $tp->max_score_per_item }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border fw-bold">
                                        {{ count($tp->indicators ?? []) }} Indikator
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info text-white me-1" onclick="viewDetail('{{ $tp->id }}')" title="Lihat Indikator">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning fw-bold me-1" onclick="openEditModal('{{ $tp->id }}')" title="Edit TP">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteTp('{{ $tp->id }}')" title="Hapus TP">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="empty-row">
                                <td colspan="6" class="py-4 text-center text-muted">
                                    <i class="fas fa-folder-open fs-2 mb-2"></i><br>
                                    Belum ada data Master TP yang dibuat.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FORM TAMBAH / EDIT TP -->
    <div class="modal fade" id="modalTpForm" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">
                        <i class="fas fa-edit me-2"></i>Form Master TP
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="tpForm">
                    @csrf
                    <input type="hidden" id="tp_id" name="tp_id">
                    <div class="modal-body">
                        
                        <!-- SECTION UPLOAD FILE OTOMATIS -->
                        <div class="mb-3 p-3 bg-light border rounded">
                            <label class="form-label fw-bold text-primary">
                                <i class="fas fa-file-upload me-1"></i> Impor Indikator Otomatis dari File Document (.docx / .txt)
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="file" id="document_file" class="form-control" accept=".docx,.txt">
                                <button type="button" class="btn btn-outline-primary fw-bold" onclick="uploadDocument(event)">
                                    <i class="fas fa-magic me-1"></i> Ekstrak Indikator
                                </button>
                            </div>
                            <small class="text-muted">Upload berkas Lembar Observasi (.docx) untuk mengisi baris indikator secara otomatis.</small>
                        </div>

                        <!-- INPUT UTAMA -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Kode TP <span class="text-danger">*</span></label>
                                <input type="text" id="code" name="code" class="form-control" placeholder="Contoh: TP1, TP2" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Judul TP <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title" class="form-control" placeholder="Contoh: Soft Skills Dunia Kerja" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Konsentrasi Keahlian</label>
                                <select id="major_id" name="major_id" class="form-select">
                                    <option value="">-- Umum (Semua Jurusan) --</option>
                                    @foreach($majors as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Skala Skor Maksimal Per Item</label>
                                <select id="max_score_per_item" name="max_score_per_item" class="form-select" required>
                                    <option value="4">1 s.d 4 (Kurang, Cukup, Baik, Sangat Baik)</option>
                                    <option value="5">1 s.d 5 (Sangat Kurang ... Sangat Baik)</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <!-- LIST DINAMIS INDIKATOR PENILAIAN -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-list me-1"></i> Draf Indikator Penilaian
                            </h6>
                            <button type="button" class="btn btn-sm btn-success fw-bold" onclick="addIndicatorRow()">
                                <i class="fas fa-plus me-1"></i> Tambah Baris Indikator
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle" id="indicatorTable">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="45%">Nama Indikator Penilaian</th>
                                        <th>Kriteria / Deskripsi</th>
                                        <th width="10%">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="indicatorContainer">
                                    <!-- Baris indikator di-load via JS -->
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btnSave">
                            <i class="fas fa-save me-1"></i> Simpan Data TP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL INDIKATOR (VIEW ONLY) -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold" id="detailModalTitle">
                        <i class="fas fa-info-circle me-2"></i>Rincian Indikator TP
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="8%" class="text-center">No</th>
                                <th width="40%">Indikator Penilaian</th>
                                <th>Kriteria / Deskripsi Penilaian</th>
                            </tr>
                        </thead>
                        <tbody id="detailIndicatorContainer">
                            <!-- Rows loaded via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT JAVASCRIPT AJAX & SWEETALERT2 -->
    <script>
        let modalEl = document.getElementById('modalTpForm');
        let detailModalEl = document.getElementById('modalDetail');
        let modalTpForm = null;
        let modalDetail = null;

        // Inisialisasi Instance Modal Bootstrap 5 saat DOM Siap
        document.addEventListener('DOMContentLoaded', function () {
            if (modalEl) modalTpForm = new bootstrap.Modal(modalEl);
            if (detailModalEl) modalDetail = new bootstrap.Modal(detailModalEl);
        });

        function getFormModalInstance() {
            if (!modalTpForm && modalEl) {
                modalTpForm = new bootstrap.Modal(modalEl);
            }
            return modalTpForm;
        }

        function getDetailModalInstance() {
            if (!modalDetail && detailModalEl) {
                modalDetail = new bootstrap.Modal(detailModalEl);
            }
            return modalDetail;
        }

        // BUKA MODAL TAMBAH
        function openAddModal() {
            document.getElementById('tpForm').reset();
            document.getElementById('tp_id').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Tambah Master TP Baru';
            document.getElementById('indicatorContainer').innerHTML = '';
            addIndicatorRow();
            
            let instance = getFormModalInstance();
            if (instance) instance.show();
        }

        // BUKA MODAL EDIT VIA AJAX
        function openEditModal(id) {
            Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch(`/guru/tp-master/${id}`, {
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(resData => {
                Swal.close();
                
                // Ambil data dari resData.data atau resData langsung
                let tp = resData.data ? resData.data : (resData.id ? resData : null);
                
                if (tp && tp.id) {
                    document.getElementById('tp_id').value = tp.id;
                    document.getElementById('code').value = tp.code;
                    document.getElementById('title').value = tp.title;
                    document.getElementById('major_id').value = tp.major_id ?? '';
                    document.getElementById('max_score_per_item').value = tp.max_score_per_item;
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Master TP';

                    document.getElementById('indicatorContainer').innerHTML = '';
                    let indicators = typeof tp.indicators === 'string' ? JSON.parse(tp.indicators) : tp.indicators;
                    
                    if (indicators && indicators.length > 0) {
                        indicators.forEach(ind => {
                            addIndicatorRow(ind.name, ind.criteria);
                        });
                    } else {
                        addIndicatorRow();
                    }

                    let instance = getFormModalInstance();
                    if (instance) instance.show();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: resData.message || 'Data TP tidak ditemukan.' });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal mengambil data TP dari server.' });
            });
        }

        // VIEW DETAIL INDIKATOR VIA AJAX
        function viewDetail(id) {
            Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch(`/guru/tp-master/${id}`, {
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(resData => {
                Swal.close();
                
                let tp = resData.data ? resData.data : (resData.id ? resData : null);
                
                if (tp && tp.id) {
                    document.getElementById('detailModalTitle').innerHTML = `<i class="fas fa-info-circle me-2"></i>Rincian Indikator - ${tp.code} (${tp.title})`;
                    
                    let container = document.getElementById('detailIndicatorContainer');
                    container.innerHTML = '';
                    
                    let indicators = typeof tp.indicators === 'string' ? JSON.parse(tp.indicators) : tp.indicators;

                    if (indicators && indicators.length > 0) {
                        indicators.forEach((ind, index) => {
                            let tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td class="text-center fw-bold">${index + 1}</td>
                                <td class="fw-bold">${ind.name ?? '-'}</td>
                                <td><small class="text-muted">${ind.criteria ?? '-'}</small></td>
                            `;
                            container.appendChild(tr);
                        });
                    } else {
                        container.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Belum ada indikator.</td></tr>';
                    }

                    let instance = getDetailModalInstance();
                    if (instance) instance.show();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: resData.message || 'Detail data TP tidak ditemukan.' });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat detail indikator.' });
            });
        }

        // MANAJEMEN BARIS TABEL INDIKATOR FORM
        function addIndicatorRow(name = '', criteria = '') {
            let container = document.getElementById('indicatorContainer');
            let index = container.children.length;
            let row = document.createElement('tr');
            row.innerHTML = `
                <td class="text-center fw-bold row-number">${index + 1}</td>
                <td><input type="text" name="indicators[${index}][name]" class="form-control form-control-sm" value="${name}" placeholder="Masukkan Indikator" required></td>
                <td><input type="text" name="indicators[${index}][criteria]" class="form-control form-control-sm" value="${criteria}" placeholder="Deskripsi Kriteria (Opsional)"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeIndicatorRow(this)"><i class="fas fa-times"></i></button></td>
            `;
            container.appendChild(row);
            updateRowNumbers();
        }

        function removeIndicatorRow(btn) {
            btn.closest('tr').remove();
            updateRowNumbers();
        }

        function updateRowNumbers() {
            let rows = document.querySelectorAll('.row-number');
            rows.forEach((td, idx) => td.innerText = idx + 1);
        }

        // UPLOAD & EKSTRAK FILE DOCX VIA AJAX
        function uploadDocument(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            let fileInput = document.getElementById('document_file');
            if (fileInput.files.length === 0) {
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Perhatian', 
                    text: 'Pilih file dokumen (.docx / .txt) terlebih dahulu!',
                    target: '#modalTpForm' 
                });
                return;
            }

            let formData = new FormData();
            formData.append('document', fileInput.files[0]);

            Swal.fire({ 
                title: 'Mengekstrak Indikator...', 
                text: 'Mohon tunggu sejenak', 
                allowOutsideClick: false, 
                target: '#modalTpForm',
                didOpen: () => Swal.showLoading() 
            });

            fetch("{{ route('guru.tp_master.parse_file') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            })
            .then(async res => {
                let data = await res.json();
                Swal.close();
                if (res.ok && data.status === 'success') {
                    document.getElementById('indicatorContainer').innerHTML = '';
                    data.indicators.forEach(ind => {
                        addIndicatorRow(ind.name, ind.criteria);
                    });
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Berhasil Ekstrak!', 
                        text: `${data.indicators.length} Indikator berhasil dimuat ke dalam form.`, 
                        timer: 1800, 
                        showConfirmButton: false,
                        target: '#modalTpForm'
                    });
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Gagal Ekstrak', 
                        text: data.message || 'Format dokumen tidak mendukung.',
                        target: '#modalTpForm'
                    });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error System', 
                    text: 'Terjadi masalah pada server saat membaca berkas.',
                    target: '#modalTpForm'
                });
            });
        }

        // SIMPAN / UPDATE VIA AJAX
        document.getElementById('tpForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let id = document.getElementById('tp_id').value;
            // Pastikan URL hanya menambahkan ID jika ID valid (bukan null, kosong, atau "0")
            let url = (id && id !== "0" && id !== "") ? `/guru/tp-master/${id}` : "{{ route('guru.tp_master.store') }}";
            let formData = new FormData(this);

            if (id && id !== "0" && id !== "") {
                formData.append('_method', 'PUT');
            }

            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch(url, {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async res => {
                let data = await res.json();
                Swal.close();
                if (res.ok && data.status === 'success') {
                    let instance = getFormModalInstance();
                    if (instance) instance.hide();
                    
                    Swal.fire({ icon: 'success', title: 'Sukses!', text: data.message, timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Gagal Menyimpan', 
                        text: data.message || 'Periksa kembali kelengkapan data form.',
                        target: '#modalTpForm'
                    });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error System', 
                    text: 'Terjadi kesalahan sistem saat menyimpan data.',
                    target: '#modalTpForm'
                });
            });
        });

        // HAPUS VIA SWEETALERT2 & AJAX
        function deleteTp(id) {
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Data Master TP yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/guru/tp-master/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-HTTP-Method-Override': 'DELETE',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire('Terhapus!', data.message, 'success');
                            let targetRow = document.getElementById(`row-${id}`);
                            if (targetRow) targetRow.remove();
                        }
                    });
                }
            });
        }
    </script>
</x-app-layout>