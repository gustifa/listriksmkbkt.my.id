@section('title', 'Penilaian Observasi TP Siswa')

<x-app-layout>
    <div class="page-content container-fluid">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold text-primary"><i class="fas fa-clipboard-check me-2"></i>Lembar Observasi Penilaian PKL</h4>
                <p class="mb-0 text-muted small">Input skor indikator observasi siswa berdasarkan Master TP yang berlaku.</p>
            </div>
            <a href="" class="btn btn-secondary btn-sm fw-bold"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
        </div>

        <!-- Informasi Siswa -->
        <div class="mb-4 border-0 shadow-sm card bg-light">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table mb-0 table-borderless table-sm">
                            <tr><td width="35%" class="fw-bold">Nama Siswa</td><td>: <strong>{{ $internship->student->name }}</strong></td></tr>
                            <tr><td class="fw-bold">NIS / Kelas</td><td>: {{ $internship->student->nis }} • {{ $internship->student->classroom->name ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table mb-0 table-borderless table-sm">
                            <tr><td width="35%" class="fw-bold">Konsentrasi Keahlian</td><td>: {{ $internship->student->classroom->major->name ?? '-' }}</td></tr>
                            <tr><td class="fw-bold">Tempat PKL</td><td>: {{ $internship->industry->name }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($tpMasters->isEmpty())
            <div class="alert alert-warning shadow-sm">
                <i class="fas fa-exclamation-triangle me-2"></i> Belum ada Master TP yang dikonfigurasi untuk jurusan ini. Silakan atur di menu <strong>Manajemen Master TP</strong> terlebih dahulu.
            </div>
        @else
            <form id="formTpAssessment" action="{{ route('guru.internships.tp.store', $internship->id) }}" method="POST">
                @csrf

                <!-- NAV TABS (ID Menggunakan Str::slug agar perpindahan tab lancar) -->
                <ul class="mb-3 nav nav-pills nav-fill gap-2" id="tpTab" role="tablist">
                    @foreach($tpMasters as $index => $tp)
                        @php $tabId = 'tab-' . \Illuminate\Support\Str::slug($tp->code, '-'); @endphp
                        <li class="nav-item">
                            <button class="nav-link {{ $index == 0 ? 'active' : '' }} fw-bold border" 
                                    id="{{ $tabId }}-btn" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#{{ $tabId }}" 
                                    type="button" 
                                    role="tab">
                                {{ $tp->code }}: {{ $tp->title }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <!-- TAB CONTENT DINAMIS -->
                <div class="tab-content" id="tpTabContent">
                    @php 
                        $savedScores = $internship->tpScore->scores ?? []; 
                        $savedNotes  = $internship->tpScore->notes ?? []; 
                    @endphp

                    @foreach($tpMasters as $index => $tp)
                        @php 
                            $tabId = 'tab-' . \Illuminate\Support\Str::slug($tp->code, '-'); 
                            $tpCode = $tp->code;
                            $tpSlug = \Illuminate\Support\Str::slug($tpCode, '-');
                            $tpSavedScores = $savedScores[$tpCode] ?? ($savedScores[$tpSlug] ?? []);
                        @endphp
                        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="{{ $tabId }}" role="tabpanel">
                            <div class="border-0 shadow-sm card mb-4">
                                <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
                                    <span>{{ $tp->code }} - {{ $tp->title }}</span>
                                    <span class="badge bg-light text-dark fw-bold">Skala 1 s.d {{ $tp->max_score_per_item }}</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light text-center">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="35%">Aspek / Indikator</th>
                                                    <th>Kriteria Penilaian</th>
                                                    <th width="20%">Skor Perolehan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tp->indicators ?? [] as $indIndex => $indicator)
                                                    @php 
                                                        $indId = $indicator['id'] ?? ($indIndex + 1); 
                                                        $cleanName = preg_replace('/^\d+[\.\)\s]+/', '', $indicator['name'] ?? '');
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                                        <td class="fw-bold text-dark">{{ $cleanName }}</td>
                                                        <td><small class="text-secondary">{{ $indicator['criteria'] ?? '-' }}</small></td>
                                                        <td>
                                                            <select name="scores[{{ $tp->code }}][{{ $indId }}]" class="form-select form-select-sm score-select">
                                                                <option value="">-- Pilih Skor --</option>
                                                                @for($s = $tp->max_score_per_item; $s >= 1; $s--)
                                                                    <option value="{{ $s }}" {{ ($tpSavedScores[$indId] ?? '') == $s ? 'selected' : '' }}>
                                                                        Skor {{ $s }} 
                                                                        @if($tp->max_score_per_item == 4)
                                                                            ({{ $s == 4 ? 'Sangat Baik' : ($s == 3 ? 'Baik' : ($s == 2 ? 'Cukup' : 'Kurang')) }})
                                                                        @endif
                                                                    </option>
                                                                @endfor
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-3">Belum ada indikator pada TP ini.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-3">
                                        <label class="form-label fw-bold text-dark">Catatan & Saran Pembimbing ({{ $tp->code }}):</label>
                                        <textarea name="notes[{{ $tp->code }}]" class="form-control" rows="2" placeholder="Masukkan catatan khusus pembimbing...">{{ $savedNotes[$tp->code] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" id="btnSubmitTp" class="btn btn-success btn-lg fw-bold shadow">
                        <i class="fas fa-save me-2"></i>Simpan Penilaian TP
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formTpAssessment');
    const btnSubmit = document.getElementById('btnSubmitTp');

    if (form) {
        form.addEventListener('submit', function (e) {
            let emptyCount = 0;
            const selects = form.querySelectorAll('.score-select');
            
            selects.forEach(select => {
                if (!select.value) {
                    emptyCount++;
                }
            });

            if (emptyCount > 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Penilaian Belum Lengkap',
                    text: `Masih ada ${emptyCount} indikator yang belum diberi skor. Yakin ingin menyimpan?`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
                        form.submit();
                    }
                });
            } else {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
            }
        });
    }
});
</script>