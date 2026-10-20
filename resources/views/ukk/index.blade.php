@section('title', 'Uji Kompetensi Keahlian (UKK)')

<x-app-layout>
    <div class="page-content">
        <div class="container-fluid">
            <!-- Breadcrumb -->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">UKK</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Manajemen & Penilaian UKK</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- CARD GENERATE JADWAL OTOMATIS (Khusus Admin / Hak Akses Kelola) -->
            @if(Auth::user()->role === 'admin' || Auth::user()->hasRole('admin'))
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    <i class="fas fa-calendar-plus me-2"></i> Generate Jadwal UKK Otomatis (Kelas XII TITL & TPTUP)
                </div>
                <div class="card-body">
                    <form action="{{ route('ukk.generate') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Pilih Kelas XII</label>
                                <select name="classroom_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classrooms as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Paket UKK</label>
                                <select name="ukk_package_id" class="form-select" required>
                                    <option value="">-- Pilih Paket --</option>
                                    @foreach($packages as $pkg)
                                        <option value="{{ $pkg->id }}">[{{ $pkg->major_code }}] {{ $pkg->package_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Tgl Mulai Ujian</label>
                                <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Kuota / Hari</label>
                                <input type="number" name="quota_per_day" class="form-control" value="10" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Penguji Internal</label>
                                <select name="internal_examiner_id" class="form-select" required>
                                    <option value="">-- Pilih Penguji Internal --</option>
                                    @foreach($examiners as $ex)
                                        <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Penguji Eksternal (DUDI)</label>
                                <select name="external_examiner_id" class="form-select" required>
                                    <option value="">-- Pilih Penguji Eksternal --</option>
                                    @foreach($examiners as $ex)
                                        <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100 fw-bold"><i class="fas fa-cog me-1"></i> Buat Jadwal Otomatis</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- CARD DAFTAR PESERTA & PENILAIAN -->
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-user-check me-2"></i> Daftar Peserta & Penilaian UKK</h6>
                    <form action="{{ route('ukk.index') }}" method="GET" class="d-flex gap-2 mt-2 mt-sm-0">
                        <select name="classroom_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($classrooms as $class)
                                <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Nama / NIS..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th>Tanggal Ujian</th>
                                    <th>Siswa / NIS</th>
                                    <th>Kelas</th>
                                    <th>Paket UKK</th>
                                    <th class="text-center">Nilai Internal</th>
                                    <th class="text-center">Nilai Eksternal</th>
                                    <th class="text-center">Nilai Akhir</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" width="12%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assessments as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $assessments->firstItem() + $index }}</td>
                                    <td>
                                        <i class="fas fa-calendar-alt text-muted me-1"></i>
                                        {{ \Carbon\Carbon::parse($item->exam_date)->translatedFormat('d/m/Y') }}
                                    </td>
                                    <td>
                                        <strong class="d-block text-dark">{{ $item->student->name }}</strong>
                                        <small class="text-muted">NIS: {{ $item->student->nis }}</small>
                                    </td>
                                    <td>{{ $item->student->classroom->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark me-1">{{ $item->package->major_code }}</span>
                                        <small class="fw-semibold">{{ $item->package->package_name }}</small>
                                    </td>
                                    <td class="text-center fw-bold">{{ $item->score_internal ?? '-' }}</td>
                                    <td class="text-center fw-bold">{{ $item->score_external ?? '-' }}</td>
                                    <td class="text-center fw-bold text-primary">{{ $item->final_score ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($item->status === 'Kompeten')
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Kompeten</span>
                                        @elseif($item->status === 'Belum Kompeten')
                                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Belum Kompeten</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i> Belum Diuji</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- Tombol Modal Input Nilai -->
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalScore{{ $item->id }}" title="Input / Edit Nilai">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- Tombol Cetak Sertifikat jika Kompeten -->
                                            @if($item->status === 'Kompeten')
                                                <a href="{{ route('ukk.certificate', $item->student_id) }}" target="_blank" class="btn btn-sm btn-warning text-white" title="Cetak Sertifikat A4">
                                                    <i class="fas fa-certificate"></i>
                                                </a>
                                            @endif
                                        </div>

                                        <!-- MODAL INPUT / EDIT NILAI UKK -->
                                        <div class="modal fade" id="modalScore{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <form action="{{ route('ukk.store_score', $item->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header bg-primary text-white">
                                                            <h6 class="modal-title fw-bold">
                                                                <i class="fas fa-user-graduate me-1"></i> Penilaian UKK: {{ $item->student->name }}
                                                            </h6>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-light border mb-3">
                                                                <small class="d-block"><strong>Paket Ujian:</strong> {{ $item->package->package_name }}</small>
                                                                <small class="d-block"><strong>Penguji Internal:</strong> {{ $item->internalExaminer->name ?? '-' }}</small>
                                                                <small class="d-block"><strong>Penguji Eksternal:</strong> {{ $item->externalExaminer->name ?? '-' }}</small>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Input Nilai Ujian (Skala 0 - 100)</label>
                                                                <input type="number" step="0.01" name="score" class="form-control" value="{{ $item->final_score }}" min="0" max="100" placeholder="Masukkan nilai..." required>
                                                                <small class="text-muted" style="font-size: 0.75rem;">Siswa dinyatakan <strong>Kompeten</strong> jika Nilai Akhir &ge; 75.00</small>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Catatan Evaluasi / Penguji</label>
                                                                <textarea name="notes" class="form-control" rows="3" placeholder="Masukkan catatan opsional untuk peserta...">{{ $item->notes }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-save me-1"></i> Simpan Nilai</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="fas fa-folder-open fs-3 mb-2 d-block"></i>
                                        Belum ada data ujian UKK. Silakan pilih kelas & generate jadwal otomatis terlebih dahulu.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Menampilkan {{ $assessments->firstItem() ?? 0 }} - {{ $assessments->lastItem() ?? 0 }} dari {{ $assessments->total() }} data</small>
                        {{ $assessments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert Flash Notification -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 2500, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}", timer: 3000, showConfirmButton: true });
        @endif
    </script>
</x-app-layout>