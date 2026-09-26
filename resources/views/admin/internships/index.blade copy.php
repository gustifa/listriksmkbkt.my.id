@section('title', 'Penentuan Tempat PKL')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary"><i class="fas fa-briefcase me-2"></i>Penempatan PKL Siswa</h4>
            <div class="d-flex gap-2">
                <button class="btn btn-primary shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addInternshipModal">
                    <i class="fas fa-plus me-1"></i> Penempatan Baru
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card border-0 shadow-lg mb-4">
            <div class="card-header bg-white py-3">
                <form method="GET" class="row g-2">
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
                    <div class="col-md-3">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-filter"></i> Filter</button>
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
                                    <div class="fw-bold text-primary">{{ $item->industry->name }}</div>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ Str::limit($item->industry->address, 30) }}</small>
                                </td>
                                <td>
                                    <div class="text-sm">
                                        <i class="far fa-calendar-alt text-success me-1"></i> {{ $item->start_date->format('d M Y') }}<br>
                                        <i class="far fa-calendar-check text-danger me-1"></i> {{ $item->end_date->format('d M Y') }}
                                    </div>
                                </td>
                                <td>{{ $item->advisor->name ?? 'Belum Ditentukan' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('admin.internships.status', $item->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm fw-bold border-0 bg-transparent text-center
                                            {{ $item->status == 'active' ? 'text-success' : ($item->status == 'completed' ? 'text-primary' : 'text-danger') }}" 
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
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data penempatan PKL.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $internships->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL PENEMPATAN BARU -->
    <div class="modal fade" id="addInternshipModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Penempatan Siswa PKL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.internships.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Siswa</label>
                            <select name="student_id" class="form-select select2" required>
                                <option value="">-- Cari Siswa --</option>
                                @foreach($students as $s)
                                    <option value="{{ $s->id }}">{{ $s->nis }} - {{ $s->name }} ({{ $s->classroom->name ?? '-' }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">*Hanya siswa yang belum memiliki jadwal PKL aktif yang tampil.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tempat PKL (DU/DI)</label>
                            <select name="industry_id" class="form-select select2" required>
                                <option value="">-- Pilih Industri --</option>
                                @foreach($industries as $ind)
                                    @php 
                                        $terisi = \App\Models\Internship::where('industry_id', $ind->id)->whereIn('status', ['pending', 'active'])->count();
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
                            <select name="advisor_id" class="form-select select2">
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Penempatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>