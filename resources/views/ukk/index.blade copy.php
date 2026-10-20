@section('title', 'Penilaian UKK')

<x-app-layout>
    <div class="page-content">
        <div class="container-fluid px-2 px-md-3">
            
            <!-- BREADCRUMB -->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">UKK</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Penilaian UKK</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- CARD GENERATE JADWAL OTOMATIS (KHUSUS ADMIN) -->
    @if(Auth::user()->role === 'admin' || Auth::user()->hasRole('admin'))
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-primary text-white fw-bold py-2">
            <i class="fas fa-calendar-plus me-1"></i> Generate Jadwal UKK Otomatis
        </div>
        <div class="card-body p-3">
            <form action="{{ route('ukk.generate') }}" method="POST">
                @csrf
                <div class="row g-2">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold mb-1">Kelas XII</label>
                        <select name="classroom_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- DIBATASI TEKSNYA AGAR TIDAK MELUAP -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold mb-1">Paket UKK</label>
                        <select name="ukk_package_id" class="form-select form-select-sm text-truncate" required style="max-width: 100%;">
                            <option value="">-- Pilih Paket --</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" title="[{{ $pkg->major_code }}] {{ $pkg->package_name }}">
                                    [{{ $pkg->major_code }}] {{ \Illuminate\Support\Str::limit($pkg->package_name, 50, '...') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold mb-1">Tgl Mulai</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold mb-1">Kuota / Hari</label>
                        <input type="number" name="quota_per_day" class="form-control form-control-sm" value="10" min="1" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold mb-1">Penguji Internal</label>
                        <select name="internal_examiner_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Internal --</option>
                            @foreach($internalExaminers ?? $examiners ?? [] as $ex)
                                <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <label class="form-label small fw-bold mb-0">Penguji Eksternal (DUDI)</label>
        <button type="button" class="btn btn-link p-0 small text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddExternalExaminer" style="font-size: 0.78rem;">
            <i class="fas fa-plus-circle me-1"></i> Tambah DUDI
        </button>
    </div>
    <select name="external_examiner_id" class="form-select form-select-sm" required>
        <option value="">-- Pilih Eksternal DUDI --</option>
        @foreach($externalExaminers ?? [] as $ex)
            <option value="{{ $ex->id }}">
                {{ $ex->name }} ({{ $ex->company_name }})
            </option>
        @endforeach
    </select>
</div>

                    <div class="col-12 col-md-4 d-flex align-items-end mt-2">
                        <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                            <i class="fas fa-cog me-1"></i> Buat Jadwal Otomatis
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

            <!-- CARD DAFTAR PESERTA & PENILAIAN -->
            <div class="card border-0 shadow-sm">
                <!-- HEADER FILTER & PENCARIAN -->
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center g-2">
                        <div class="col-12 col-md-4">
                            <h6 class="fw-bold mb-0 text-primary">
                                <i class="fas fa-user-check me-1"></i> Peserta & Penilaian UKK
                            </h6>
                        </div>
                        <div class="col-12 col-md-8">
                            <form action="{{ route('ukk.index') }}" method="GET" class="row g-2 justify-content-md-end">
                                <div class="col-6 col-md-4">
                                    <select name="classroom_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">-- Semua Kelas --</option>
                                        @foreach($classrooms as $class)
                                            <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-5 d-flex gap-1">
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Nama/NIS..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    
                    <!-- 1. TAMPILAN CARD UNTUK LAYAR MOBILE (d-block d-lg-none) -->
                    <div class="d-block d-lg-none p-2">
                        @forelse($assessments as $item)
                        <div class="card border mb-2 shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-secondary me-1">{{ $item->student->classroom->name ?? '-' }}</span>
                                        <strong class="text-dark d-block">{{ $item->student->name }}</strong>
                                        <small class="text-muted">NIS: {{ $item->student->nis }}</small>
                                    </div>
                                    <div>
                                        @if($item->status === 'Kompeten')
                                            <span class="badge bg-success">Kompeten</span>
                                        @elseif($item->status === 'Belum Kompeten')
                                            <span class="badge bg-danger">Belum Kompeten</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Diuji</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="bg-light p-2 rounded mb-2 small">
                                    <div class="text-truncate">
                                        <span class="badge bg-info text-dark me-1">{{ $item->package->major_code }}</span>
                                        <strong>{{ $item->package->package_name }}</strong>
                                    </div>
                                    <div class="mt-1 text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i> Tgl: {{ \Carbon\Carbon::parse($item->exam_date)->format('d/m/Y') }}
                                    </div>
                                </div>

                                <div class="row text-center mb-2 small g-1">
                                    <div class="col-4 border-end">
                                        <span class="d-block text-muted" style="font-size:0.7rem;">Internal</span>
                                        <strong class="text-dark">{{ $item->score_internal ?? '-' }}</strong>
                                    </div>
                                    <div class="col-4 border-end">
                                        <span class="d-block text-muted" style="font-size:0.7rem;">Eksternal</span>
                                        <strong class="text-dark">{{ $item->score_external ?? '-' }}</strong>
                                    </div>
                                    <div class="col-4">
                                        <span class="d-block text-muted" style="font-size:0.7rem;">Nilai Akhir</span>
                                        <strong class="text-primary">{{ $item->final_score ?? '-' }}</strong>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#modalScore{{ $item->id }}">
                                        <i class="fas fa-edit me-1"></i> Input / Edit Nilai
                                    </button>
                                    @if($item->status === 'Kompeten')
                                        <a href="{{ route('ukk.certificate', $item->student_id) }}" target="_blank" class="btn btn-sm btn-warning text-white px-3">
                                            <i class="fas fa-certificate"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">Belum ada data peserta UKK.</div>
                        @endforelse
                    </div>

                    <!-- TAMPILAN TABEL UNTUK LAYAR DESKTOP (d-none d-lg-block) -->
                    <div class="d-none d-lg-block table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" style="table-layout: fixed;">
                            <thead class="table-dark text-nowrap">
                                <tr>
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th style="width: 10%;">Tanggal</th>
                                    <th style="width: 22%;">Siswa / NIS</th>
                                    <th style="width: 10%;">Kelas</th>
                                    <th style="width: 28%;">Paket UKK</th>
                                    <th class="text-center" style="width: 7%;">Int</th>
                                    <th class="text-center" style="width: 7%;">Eks</th>
                                    <th class="text-center" style="width: 8%;">Akhir</th>
                                    <th class="text-center" style="width: 10%;">Status</th>
                                    <th class="text-center" style="width: 8%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assessments as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $assessments->firstItem() + $index }}</td>
                                    <td><small class="text-muted">{{ \Carbon\Carbon::parse($item->exam_date)->format('d/m/Y') }}</small></td>
                                    <td>
                                        <strong class="d-block text-dark text-wrap">{{ $item->student->name }}</strong>
                                        <small class="text-muted">NIS: {{ $item->student->nis }}</small>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $item->student->classroom->name ?? '-' }}</span></td>
                                    <td>
                                        <span class="badge bg-info text-dark me-1">{{ $item->package->major_code }}</span>
                                        <span class="small fw-semibold text-wrap text-break d-inline-block" style="max-width: 90%;">
                                            {{ $item->package->package_name }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $item->score_internal ?? '-' }}</td>
                                    <td class="text-center fw-bold">{{ $item->score_external ?? '-' }}</td>
                                    <td class="text-center fw-bold text-primary">{{ $item->final_score ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($item->status === 'Kompeten')
                                            <span class="badge bg-success">Kompeten</span>
                                        @elseif($item->status === 'Belum Kompeten')
                                            <span class="badge bg-danger">Belum Kompeten</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Diuji</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalScore{{ $item->id }}" title="Input Nilai">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($item->status === 'Kompeten')
                                                <a href="{{ route('ukk.certificate', $item->student_id) }}" target="_blank" class="btn btn-sm btn-warning text-white" title="Cetak Sertifikat">
                                                    <i class="fas fa-certificate"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">Belum ada data peserta.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- MODAL DI-RENDER DI SINI AGAR BISA DIPANGGIL MOBILE MAUPUN DESKTOP -->
                    @foreach($assessments as $item)
                    <div class="modal fade" id="modalScore{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-start">
                                <form action="{{ route('ukk.store_score', $item->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header bg-primary text-white py-2">
                                        <h6 class="modal-title fw-bold">
                                            <i class="fas fa-user-graduate me-1"></i> Nilai: {{ $item->student->name }}
                                        </h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-3">
                                        <div class="alert alert-light border mb-3 small">
                                            <div class="text-truncate"><strong>Paket:</strong> {{ $item->package->package_name }}</div>
                                            <div><strong>Penguji Int:</strong> {{ $item->internalExaminer->name ?? '-' }}</div>
                                            <div><strong>Penguji Eks:</strong> {{ $item->externalExaminer->name ?? '-' }}</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Input Nilai (0 - 100)</label>
                                            <input type="number" step="0.01" name="score" class="form-control" value="{{ $item->final_score }}" min="0" max="100" placeholder="Masukkan nilai..." required>
                                            <small class="text-muted" style="font-size: 0.72rem;">Kompeten jika Nilai Akhir &ge; 75.00</small>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label fw-bold small">Catatan Evaluasi</label>
                                            <textarea name="notes" class="form-control" rows="2" placeholder="Catatan opsional...">{{ $item->notes }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light py-2">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-save me-1"></i> Simpan Nilai</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>

                <!-- PAGINASI -->
                <div class="card-footer bg-white py-2">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <small class="text-muted">Menampilkan {{ $assessments->firstItem() ?? 0 }} - {{ $assessments->lastItem() ?? 0 }} dari {{ $assessments->total() }} data</small>
                        <div>{{ $assessments->links() }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL TAMBAH PENGUJI EKSTERNAL / DUDI + UPLOAD LOGO -->
<div class="modal fade" id="modalAddExternalExaminer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('external_examiners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title fw-bold">
                        <i class="fas fa-building me-1"></i> Tambah Penguji Eksternal (DUDI)
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">Nama Penguji Eksternal</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Contoh: Budi Santoso, S.T." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">Nama Perusahaan / DUDI</label>
                        <input type="text" name="company_name" class="form-control form-control-sm" placeholder="Contoh: PT PLN (Persero)" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">Jabatan (Opsional)</label>
                        <input type="text" name="position" class="form-control form-control-sm" placeholder="Contoh: Senior Engineer">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">No. HP / Kontak (Opsional)</label>
                        <input type="text" name="phone" class="form-control form-control-sm" placeholder="Contoh: 081234567890">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small mb-1">Upload Logo Perusahaan (PNG/JPG)</label>
                        <input type="file" name="logo" class="form-control form-control-sm" accept="image/png, image/jpeg, image/jpg">
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                            <i class="fas fa-info-circle me-1"></i> Format PNG transparan, maks. 2MB.
                        </small>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                        <i class="fas fa-save me-1"></i> Simpan Penguji DUDI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- SWEETALERT NOTIFIKASI AMAN DARI NEWLINE & UNESCAPED QUOTES -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: {!! json_encode(session('success')) !!},
                    timer: 2500,
                    showConfirmButton: false
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: {!! json_encode(session('error')) !!},
                    timer: 4000,
                    showConfirmButton: true
                });
            @endif
        });
    </script>
    
</x-app-layout>