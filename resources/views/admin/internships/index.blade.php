@section('title', 'Penempatan PKL Siswa')

<x-app-layout>
    <!-- Include CDN Select2 (CSS) -->
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    <div class="page-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary"><i class="fas fa-briefcase me-2"></i>Penempatan PKL Siswa</h4>
            <div class="d-flex gap-2">
                <!-- Tombol Pemicu Modal -->
                <button type="button" class="btn btn-warning btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalUpdatePeriode" data-toggle="modal" data-target="#modalUpdatePeriode">
                    <i class="fas fa-calendar-alt"></i> Ubah Periode Semua Siswa
                </button>
                <button type="button" class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#addInternshipModal" data-toggle="modal" data-target="#addInternshipModal">
                    <i class="fas fa-plus me-1"></i> Penempatan Baru
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" data-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" data-dismiss="alert"></button></div>
        @endif

        <div class="card border-0 shadow-lg mb-4">
            <div class="card-header bg-white py-3">
                <!-- FORM FILTER & PENCARIAN SISWA -->
                <form method="GET" class="row g-2 align-items-center">
                    
                    <!-- Input Ketik Nama Siswa (Select2) -->
                    <div class="col-md-4">
                        <select name="student_id" class="form-select select2-filter" onchange="this.form.submit()">
                            <option value="">-- Ketik Nama / NIS Siswa --</option>
                            @foreach($all_students as $st)
                                <option value="{{ $st->id }}" {{ request('student_id') == $st->id ? 'selected' : '' }}>
                                    {{ $st->nis }} - {{ $st->name }} ({{ $st->classroom->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tempat PKL -->
                    <div class="col-md-3">
                        <select name="industry_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Tempat PKL --</option>
                            @foreach($industries as $ind)
                                <option value="{{ $ind->id }}" {{ request('industry_id') == $ind->id ? 'selected' : '' }}>
                                    {{ $ind->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div class="col-md-3">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                    <!-- Tombol Reset Filter -->
                    <div class="col-md-2">
                        @if(request('student_id') || request('industry_id') || request('status'))
                            <a href="{{ route('admin.internships.index') }}" class="btn btn-outline-danger w-100">
                                <i class="fas fa-undo me-1"></i> Reset Filter
                            </a>
                        @else
                            <button type="submit" class="btn btn-secondary w-100" disabled>
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        @endif
                    </div>
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Siswa</th>
                                <th>Tempat PKL</th>
                                <th>Periode</th>
                                <th>Guru Pembimbing</th>
                                <th class="text-center">Surat Izin</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($internships as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $item->student->name }}</div>
                                    <small class="text-muted">{{ $item->student->nis }} • {{ $item->student->classroom->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-bold text-primary">{{ $item->industry->name }}</div>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i> 
                                                {{ \Illuminate\Support\Str::limit($item->industry->address, 30) }}
                                            </small>
                                        </div>
                                        <!-- Tombol Pemicu Modal Edit Tempat PKL -->
                                        <button type="button" class="btn btn-sm btn-outline-warning ms-2" data-bs-toggle="modal" data-bs-target="#modalEditIndustry-{{ $item->id }}" data-toggle="modal" data-target="#modalEditIndustry-{{ $item->id }}" title="Ubah Tempat PKL">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm">
                                        <i class="far fa-calendar-alt text-success me-1"></i> {{ optional($item->start_date)->format('d M Y') ?? '-' }}<br>
                                        <i class="far fa-calendar-check text-danger me-1"></i> {{ optional($item->end_date)->format('d M Y') ?? '-' }}
                                    </div>
                                </td>
                                
                                <td>
                                    @if($item->advisor)
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span>{{ $item->advisor->name }}</span>
                                            
                                            @if($item->advisor_status == 'pending')
                                                <button class="btn btn-sm btn-warning ms-2 py-0 px-2" title="Siswa Mengajukan Ini (Klik untuk Setujui/Ganti)" 
                                                        onclick="setAdvisor('{{ $item->id }}', '{{ $item->advisor_id }}')">
                                                    <i class="fas fa-clock"></i> Req
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-light text-secondary ms-2 py-0 px-2" title="Ganti Pembimbing" 
                                                        onclick="setAdvisor('{{ $item->id }}', '{{ $item->advisor_id }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <button class="btn btn-sm btn-outline-primary dashed-border w-100" onclick="setAdvisor('{{ $item->id }}', '')">
                                            + Set Pembimbing
                                        </button>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($item->parent_consent_file)
                                        <a href="{{ asset('storage/'.$item->parent_consent_file) }}" target="_blank" class="btn btn-sm btn-info text-white" title="Lihat Surat">
                                            <i class="fas fa-file-pdf"></i> Lihat
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">Belum Upload</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.internships.status', $item->id) }}" method="POST">
                                        @csrf 
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm fw-bold border-0 bg-transparent text-center
                                            {{ $item->status == 'active' ? 'text-success' : ($item->status == 'completed' ? 'text-primary' : ($item->status == 'pending' ? 'text-warning' : 'text-danger')) }}" 
                                            onchange="this.form.submit()">
                                            <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="active" {{ $item->status == 'active' ? 'selected' : '' }}>Aktif</option>
                                            <option value="completed" {{ $item->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                            <option value="cancelled" {{ $item->status == 'cancelled' ? 'selected' : '' }}>Batal</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.internships.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus penempatan ini?')">
                                        @csrf 
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada data penempatan PKL.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $internships->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL PENEMPATAN BARU -->
    <div class="modal fade" id="addInternshipModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Penempatan Siswa PKL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.internships.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Siswa</label>
                            <select name="student_id" class="form-select select2-modal" required>
                                <option value="">-- Cari Siswa --</option>
                                @foreach($available_students as $s)
                                    <option value="{{ $s->id }}">{{ $s->nis }} - {{ $s->name }} ({{ $s->classroom->name ?? '-' }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">*Hanya siswa yang belum memiliki jadwal PKL aktif yang tampil.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tempat PKL (DU/DI)</label>
                            <select name="industry_id" class="form-select select2-modal" required>
                                <option value="">-- Pilih Industri --</option>
                                @foreach($industries as $ind)
                                    @php 
                                        $terisi = $ind->terisi_count ?? 0;
                                        $sisa = $ind->quota - $terisi;
                                    @endphp
                                    <option value="{{ $ind->id }}" {{ $sisa <= 0 && $ind->quota > 0 ? 'disabled' : '' }}>
                                        {{ $ind->name }} - Sisa Kuota: {{ $ind->quota == 0 ? 'Unlimited' : $sisa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Guru Pembimbing (Opsional)</label>
                            <select name="advisor_id" class="form-select select2-modal">
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Bisa dikosongkan dan diisi nanti.</small>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal Selesai</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Penempatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL SET PEMBIMBING -->
    <div class="modal fade" id="advisorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" id="formAdvisor">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-chalkboard-teacher me-2"></i> Tentukan Pembimbing</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Guru Pembimbing</label>
                            <select name="advisor_id" id="modal_advisor_select" class="form-select select2-modal-advisor" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Memilih di sini akan otomatis menyetujui request siswa (jika status Pending).</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan / Setujui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL UPDATE PERIODE MASSAL -->
    <div class="modal fade" id="modalUpdatePeriode" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.internships.bulk_update_period') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Periode PKL Massal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai PKL</label>
                        <input type="date" name="start_date" class="form-control" value="2026-06-15" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai PKL</label>
                        <input type="date" name="end_date" class="form-control" value="2026-12-15" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT TEMPAT PKL SISWA (DILOOP TERPISAH DENGAN SELECT2) -->
    @foreach($internships as $item)
    <div class="modal fade" id="modalEditIndustry-{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.internships.update_industry', $item->id) }}" method="POST" class="modal-content">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-6 fw-bold">
                        <i class="fas fa-building me-2"></i>Ubah Tempat PKL Siswa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Siswa</label>
                        <input type="text" class="form-control bg-light" value="{{ $item->student->name }} ({{ $item->student->nis }})" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Tempat PKL / Industri Baru</label>
                        <select name="industry_id" class="form-select select2-edit-industry" required>
                            <option value="">-- Pilih Industri --</option>
                            @foreach($industries as $industry)
                                <option value="{{ $industry->id }}" {{ $item->industry_id == $industry->id ? 'selected' : '' }}>
                                    {{ $industry->name }} - {{ $industry->address }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <!-- INCLUDE CDN JQUERY & SELECT2 (JS) -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Select2 Filter
            $('.select2-filter').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- Ketik Nama / NIS Siswa --',
                allowClear: true
            });

            // Select2 Modal Tambah Penempatan
            $('.select2-modal').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#addInternshipModal')
            });
            
            // Select2 Modal Pembimbing
            $('.select2-modal-advisor').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#advisorModal')
            });

            // Inisialisasi Select2 secara otomatis ketika Modal Edit Tempat PKL dibuka
            $('[id^="modalEditIndustry-"]').on('shown.bs.modal', function () {
                $(this).find('.select2-edit-industry').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $(this)
                });
            });
        });

        function setAdvisor(internshipId, currentAdvisorId) {
            let url = "{{ route('admin.internships.assign', ':id') }}";
            url = url.replace(':id', internshipId);
            document.getElementById('formAdvisor').action = url;

            $('#modal_advisor_select').val(currentAdvisorId).trigger('change');
            
            var modalElem = document.getElementById('advisorModal');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var myModal = new bootstrap.Modal(modalElem);
                myModal.show();
            } else {
                $(modalElem).modal('show');
            }
        }
    </script>
    @endpush

</x-app-layout>