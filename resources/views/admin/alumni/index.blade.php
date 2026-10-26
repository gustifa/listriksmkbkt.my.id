@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Header Title -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bx bx-user-check text-primary me-2"></i>Kelola Data Alumni & BKK
            </h4>
            <p class="text-muted small mb-0">Kelola data kelulusan, penyerapan kerja, dan kompetensi alumni sekolah.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Alumni</li>
            </ol>
        </nav>
    </div>

    <!-- Main Card Data Alumni -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="mb-0 fw-bold text-dark">Daftar Alumni & Penyerapan Kerja</h6>
            <div class="d-flex gap-2">
                <input type="text" id="search-input" class="form-control form-control-sm rounded-pill px-3" placeholder="Cari Nama / NISN..." style="width: 250px;">
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Alumni</th>
                            <th>Tahun Lulus</th>
                            <th>Status Penyerapan</th>
                            <th>Tempat Kerja / Kampus</th>
                            <th>Kompetensi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="alumni-table-body">
                        <!-- Data di-render via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL & EDIT ALUMNI -->
<div class="modal fade" id="modalAlumniDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light py-3">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="bx bx-id-card text-primary me-2"></i>Kelola Profil Alumni
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="form-alumni-profile" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="modal-student-id" name="student_id">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Alumni</label>
                            <input type="text" id="modal-student-name" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tahun Lulus</label>
                            <input type="number" id="modal-graduation-year" name="graduation_year" class="form-control" required placeholder="2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Penyerapan</label>
                            <select id="modal-current-status" name="current_status" class="form-select" required>
                                <option value="searching">Mencari Kerja</option>
                                <option value="working">Bekerja</option>
                                <option value="studying">Kuliah / Studi Lanjut</option>
                                <option value="entrepreneur">Wirausaha</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Perusahaan / Kampus / Usaha</label>
                            <input type="text" id="modal-current-company" name="current_company" class="form-control" placeholder="Nama PT / Universitas">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jabatan / Prodi</label>
                            <input type="text" id="modal-position" name="position" class="form-control" placeholder="Contoh: Teknisi Listrik">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. WhatsApp</label>
                            <input type="text" id="modal-phone-number" name="phone_number" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Upload CV Terbaru (PDF max 2MB)</label>
                            <input type="file" name="cv_file" class="form-control" accept=".pdf">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 mb-3">
                        <i class="bx bx-save me-1"></i> Simpan Profil
                    </button>
                </form>

                <hr>

                <!-- BAGIAN KOMPETENSI / SKILL -->
                <h6 class="fw-bold text-dark mb-3"><i class="bx bx-award text-warning me-2"></i>Daftar Kompetensi & Sertifikasi</h6>
                <form id="form-alumni-skill" class="row g-2 mb-3">
                    @csrf
                    <input type="hidden" id="skill-student-id" name="student_id">
                    <div class="col-md-5">
                        <input type="text" name="skill_name" class="form-control form-control-sm" placeholder="Nama Keahlian (misal: PLC)" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="certificate_name" class="form-control form-control-sm" placeholder="Sertifikat (misal: BNSP)">
                    </div>
                    <div class="col-md-3">
                        <select name="level" class="form-select form-select-sm" required>
                            <option value="basic">Basic</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">+ Tambah Keahlian</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Keahlian</th>
                                <th>Sertifikat</th>
                                <th>Level</th>
                                <th class="text-center" style="width: 50px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="modal-skills-list">
                            <!-- Skill Render via AJAX -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 Terbaru CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.getElementById('alumni-table-body');
    const searchInput = document.getElementById('search-input');
    const modal = new bootstrap.Modal(document.getElementById('modalAlumniDetail'));

    // 1. Fetch Data Alumni via AJAX
    function loadAlumni(search = '') {
        fetch(`{{ route('alumni.data') }}?search=${search}`)
            .then(res => res.json())
            .then(res => {
                let html = '';
                if (!res.data || res.data.length === 0) {
                    html = `<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data alumni (status graduated)</td></tr>`;
                } else {
                    res.data.forEach(item => {
                        const profile = item.alumni_profile || {};
                        const statusBadge = {
                            'working': '<span class="badge bg-success">Bekerja</span>',
                            'studying': '<span class="badge bg-info text-dark">Kuliah</span>',
                            'entrepreneur': '<span class="badge bg-warning text-dark">Wirausaha</span>',
                            'searching': '<span class="badge bg-secondary">Mencari Kerja</span>'
                        }[profile.current_status] || '<span class="badge bg-light text-muted">Belum diisi</span>';

                        const skills = item.alumni_skills && item.alumni_skills.length > 0
                            ? item.alumni_skills.map(s => `<span class="badge bg-light text-dark border me-1">${s.skill_name}</span>`).join('') 
                            : '-';

                        html += `
                            <tr>
                                <td class="ps-4">
                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">${item.name}</h6>
                                    <small class="text-muted">NISN: ${item.nisn || '-'}</small>
                                </td>
                                <td>${profile.graduation_year || '-'}</td>
                                <td>${statusBadge}</td>
                                <td>${profile.current_company || '-'}</td>
                                <td>${skills}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary rounded-circle btn-edit" data-id="${item.id}" title="Edit Profil">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                tableBody.innerHTML = html;
            });
    }

    loadAlumni();

    // Event Search
    searchInput.addEventListener('keyup', function () {
        loadAlumni(this.value);
    });

    // 2. Open Modal & Load Detail
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-edit')) {
            const btn = e.target.closest('.btn-edit');
            const id = btn.getAttribute('data-id');

            fetch(`/alumni/detail/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modal-student-id').value = data.id;
                    document.getElementById('skill-student-id').value = data.id;
                    document.getElementById('modal-student-name').value = data.name;

                    const profile = data.alumni_profile || {};
                    document.getElementById('modal-graduation-year').value = profile.graduation_year || new Date().getFullYear();
                    document.getElementById('modal-current-status').value = profile.current_status || 'searching';
                    document.getElementById('modal-current-company').value = profile.current_company || '';
                    document.getElementById('modal-position').value = profile.position || '';
                    document.getElementById('modal-phone-number').value = profile.phone_number || '';

                    renderSkills(data.alumni_skills || []);
                    modal.show();
                });
        }
    });

    // Render Skill List Modal
    function renderSkills(skills) {
        const skillsContainer = document.getElementById('modal-skills-list');
        if (skills.length === 0) {
            skillsContainer.innerHTML = `<tr><td colspan="4" class="text-center text-muted small py-2">Belum ada data kompetensi</td></tr>`;
            return;
        }

        let html = '';
        skills.forEach(s => {
            html += `
                <tr>
                    <td>${s.skill_name}</td>
                    <td>${s.certificate_name || '-'}</td>
                    <td><span class="badge bg-light text-dark">${s.level}</span></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm text-danger p-0 btn-delete-skill" data-id="${s.id}">
                            <i class="bx bx-trash fs-5"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        skillsContainer.innerHTML = html;
    }

    // 3. Simpan Profil via AJAX SweetAlert2
    document.getElementById('form-alumni-profile').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("{{ route('alumni.storeProfile') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false });
                loadAlumni(searchInput.value);
            }
        });
    });

    // 4. Tambah Skill via AJAX
    document.getElementById('form-alumni-skill').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("{{ route('alumni.storeSkill') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Tersimpan!', text: data.message, timer: 1200, showConfirmButton: false });
                this.reset();
                const studentId = document.getElementById('modal-student-id').value;
                fetch(`/alumni/detail/${studentId}`).then(r => r.json()).then(d => renderSkills(d.alumni_skills || []));
                loadAlumni(searchInput.value);
            }
        });
    });

    // 5. Hapus Skill via SweetAlert2 Confirm
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-delete-skill')) {
            const btn = e.target.closest('.btn-delete-skill');
            const skillId = btn.getAttribute('data-id');

            Swal.fire({
                title: 'Hapus Kompetensi?',
                text: "Data keahlian ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/alumni/delete-skill/${skillId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, timer: 1200, showConfirmButton: false });
                            const studentId = document.getElementById('modal-student-id').value;
                            fetch(`/alumni/detail/${studentId}`).then(r => r.json()).then(d => renderSkills(d.alumni_skills || []));
                            loadAlumni(searchInput.value);
                        }
                    });
                }
            });
        }
    });
});
</script>
@endsection