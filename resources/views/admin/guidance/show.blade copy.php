@section('title', 'Pembinaan Siswa')

<x-app-layout>
    <div class="page-content">
        
        <!-- Header Info Siswa -->
        <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1 fw-bold">{{ $student->name }}</h4>
                    <p class="mb-0 op-8">NIS: {{ $student->nis }} | Kelas: {{ $student->classroom->name ?? '-' }}</p>
                </div>
                <div class="text-center">
                    <small class="d-block text-white-50 uppercase fw-bold">Total Poin Pelanggaran</small>
                    <h2 class="mb-0 fw-bold">{{ $student->violations->sum(fn($v) => $v->type->points) }}</h2>
                </div>
            </div>
        </div>

        <div class="row">
            
            <!-- KOLOM KIRI: RIWAYAT PELANGGARAN -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-danger"><i class="fas fa-exclamation-circle me-2"></i>Riwayat Pelanggaran</h6>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addViolationModal">
                            + Catat Pelanggaran
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Tanggal</th>
                                        <th>Pelanggaran</th>
                                        <th class="text-center">Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($student->violations->sortByDesc('date') as $v)
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ \Carbon\Carbon::parse($v->date)->format('d/m/y') }}</td>
                                        <td>
                                            <span class="fw-bold d-block text-dark">{{ $v->type->name }}</span>
                                            <small class="text-muted">{{ $v->note }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger rounded-pill">{{ $v->type->points }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada pelanggaran tercatat.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIWAYAT PEMBINAAN SEBELUMNYA -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-success"><i class="fas fa-history me-2"></i>Riwayat Pembinaan</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse($student->guidances->sortByDesc('date') as $g)
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-primary">{{ $g->teacher->name }} <small class="text-muted">({{ ucfirst($g->role_context) }})</small></span>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($g->date)->format('d M Y') }}</small>
                                </div>
                                <p class="mb-1 small mt-1"><strong>Masalah:</strong> {{ $g->problem_summary }}</p>
                                <p class="mb-0 small text-success"><strong>Solusi:</strong> {{ $g->advice }}</p>
                                <div class="mt-2">
                                    <span class="badge bg-{{ $g->status == 'resolved' ? 'success' : 'warning' }}">{{ ucfirst($g->status) }}</span>
                                </div>
                            </li>
                            @empty
                            <div class="text-center text-muted small">Belum ada riwayat pembinaan.</div>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: FORM PEMBINAAN -->
            <div class="col-md-6">
                <div class="card border-0 shadow-lg sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Form Pembinaan Siswa</h5>
                    </div>
                    <div class="card-body p-4">
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
                                <button type="submit" class="btn btn-success btn-lg shadow-sm">
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
                    <div class="modal-header bg-danger text-white">
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