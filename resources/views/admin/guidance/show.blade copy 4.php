@section('title', 'Pembinaan Siswa')

<x-app-layout>
    <div class="page-content">

        <!-- Header Info Siswa -->
        <div class="mb-4 text-white border-0 shadow-sm card bg-primary">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1 fw-bold">{{ $student->name }}</h4>
                    <p class="mb-0 op-8">NIS: {{ $student->nis }} | Kelas: {{ $student->classroom->name ?? '-' }}</p>
                </div>
                <div class="text-center">
                    <small class="uppercase d-block text-white-50 fw-bold">Total Poin Pelanggaran</small>
                    <h2 class="mb-0 fw-bold">{{ $student->violations->sum(fn($v) => $v->type->points ?? 0) }}</h2>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="border-0 border-4 shadow-sm alert alert-success alert-dismissible fade show border-start border-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">

            <!-- KOLOM KIRI: RIWAYAT PELANGGARAN -->
            <div class="col-md-6">
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-danger"><i class="fas fa-exclamation-circle me-2"></i>Riwayat Pelanggaran</h6>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addViolationModal">
                            + Catat Pelanggaran
                        </button>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Tanggal</th>
                                        <th>Pelanggaran</th>
                                        <th class="text-center">Poin</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($student->violations->sortByDesc('date') as $v)
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ \Carbon\Carbon::parse($v->date)->format('d/m/y') }}</td>
                                        <td>
                                            <span class="fw-bold d-block text-dark">{{ $v->type->name ?? 'Unknown' }}</span>
                                            <small class="text-muted">{{ $v->note }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger rounded-pill">{{ $v->type->points ?? 0 }}</span>
                                        </td>
                                        <!-- Form Hapus -->
                                                <form action="{{ route('admin.violation.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggaran ini? Poin akan dikembalikan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="py-4 text-center text-muted">Belum ada pelanggaran tercatat.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIWAYAT PEMBINAAN SEBELUMNYA -->
                <div class="border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold text-success"><i class="fas fa-history me-2"></i>Riwayat Pembinaan & Tindakan</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse($student->guidances->sortByDesc('date') as $g)
                            <li class="px-0 pb-3 list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="fw-bold text-primary">{{ $g->teacher->name ?? ($g->teacher->user->name ?? 'Unknown') }}</span>
                                        <small class="text-muted">({{ ucfirst($g->role_context) }})</small>
                                    </div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($g->date)->format('d M Y') }}</small>
                                </div>
                                <p class="mt-2 mb-1 small"><strong>Masalah:</strong> {{ $g->problem_summary }}</p>
                                <p class="mb-2 small text-success"><strong>Solusi/Janji:</strong> {{ $g->advice }} / {{ $g->student_commitment }}</p>

                                <!-- STATUS UPLOAD SURAT DARI SISWA (Jika ada) -->
                                @if($g->agreement_file)
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/'.$g->agreement_file) }}" target="_blank" class="text-white badge bg-info text-decoration-none">
                                            <i class="fas fa-file-signature me-1"></i> Bukti Perjanjian Siswa
                                        </a>
                                    </div>
                                @endif

                                <!-- TOMBOL AKSI: CETAK PERJANJIAN & PANGGILAN ORTU -->
                                <div class="p-2 mt-3 border rounded d-flex justify-content-between align-items-center bg-light">
                                    <span class="badge bg-{{ $g->status == 'resolved' ? 'success' : 'warning' }} text-dark">{{ ucfirst($g->status) }}</span>

                                    <div class="btn-group">
                                        <!-- Tombol Perjanjian -->
                                        <a href="{{ route('admin.guidance.print_agreement', $g->id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold" title="Cetak Surat Perjanjian untuk ditandatangani siswa">
                                            <i class="fas fa-file-contract me-1"></i> Perjanjian
                                        </a>

                                        <!-- Tombol Panggilan Orang Tua -->
                                        @if($g->is_summoned && $g->summon_file)
                                            <!-- Jika sudah dipanggil, tampilkan tombol lihat PDF -->
                                            <a href="{{ asset('storage/'.$g->summon_file) }}" target="_blank" class="btn btn-sm btn-outline-danger fw-bold">
                                                <i class="fas fa-file-pdf me-1"></i> Surat Panggilan
                                            </a>
                                        @else
                                            <!-- Jika belum, tampilkan tombol modal untuk memanggil -->
                                            <button type="button" class="btn btn-sm btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#summonModal{{ $g->id }}">
                                                <i class="fas fa-bullhorn me-1"></i> Panggil Ortu
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- MODAL INPUT PANGGILAN ORTU (Spesifik per item) -->
                                @if(!$g->is_summoned)
                                <div class="modal fade" id="summonModal{{ $g->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <!-- Pastikan route admin.guidance.summon sudah ada di web.php -->
                                            <form action="{{ route('admin.guidance.summon', $g->id) }}" method="POST">
                                                @csrf
                                                <div class="text-white modal-header bg-danger">
                                                    <h5 class="modal-title"><i class="fas fa-envelope-open-text me-2"></i> Buat Surat Panggilan</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning small">
                                                        <i class="fas fa-info-circle me-1"></i> Surat akan dibuat dalam format PDF dan tautannya akan otomatis dikirim ke WhatsApp Orang Tua (<strong>{{ $student->parent_phone ?? $student->phone ?? 'Nomor tidak tersedia' }}</strong>).
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Hari / Tanggal Panggilan</label>
                                                        <input type="date" name="summon_date" class="form-control" required min="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Waktu Panggilan (WIB)</label>
                                                        <input type="time" name="summon_time" class="form-control" required value="08:00">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger fw-bold"><i class="fas fa-paper-plane me-1"></i> Buat & Kirim WA</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                            </li>
                            @empty
                            <div class="py-4 text-center text-muted small">Belum ada riwayat pembinaan.</div>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: FORM PEMBINAAN -->
            <div class="col-md-6">
                <div class="border-0 shadow-lg card sticky-top" style="top: 20px; z-index: 1;">
                    <div class="py-3 text-white card-header bg-success">
                        <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Form Pembinaan Siswa</h5>
                    </div>
                    <div class="p-4 card-body">
                        <form action="{{ route('admin.guidance.store', $student->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="role_context" value="{{ $role }}">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Pembinaan</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Rangkuman Masalah</label>
                                <textarea name="problem_summary" class="form-control" rows="3" placeholder="Jelaskan pelanggaran yang dibahas..." required></textarea>
                                <div class="form-text">Lihat daftar pelanggaran di sebelah kiri sebagai referensi.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nasihat / Solusi / Tindak Lanjut</label>
                                <textarea name="advice" class="form-control" rows="3" placeholder="Saran yang diberikan kepada siswa..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Komitmen Siswa</label>
                                <textarea name="student_commitment" class="form-control" rows="2" placeholder="Apa janji siswa untuk perbaikan?" required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Status Kasus</label>
                                <select name="status" class="form-select">
                                    <option value="open">Masih Dalam Pantauan</option>
                                    <option value="resolved">Selesai (Resolved)</option>
                                    <option value="escalated">Eskalasi (Lanjut ke Jenjang Lebih Tinggi)</option>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="shadow-sm btn btn-success btn-lg">
                                    <i class="fas fa-save me-2"></i> Simpan Laporan Pembinaan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL TAMBAH PELANGGARAN -->
    <div class="modal fade" id="addViolationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.violation.store', $student->id) }}" method="POST">
                    @csrf
                    <div class="text-white modal-header bg-danger">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Catat Pelanggaran Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Jenis Pelanggaran</label>
                            <select name="violation_type_id" class="form-select" required>
                                @foreach($violationTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->points }} Poin)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Kejadian</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Tambahan</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Kronologi singkat..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Simpan Pelanggaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
