@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h4 class="text-dark"><i class="fas fa-book-open me-2 text-success"></i> Rekap Tahfiz Juz 30</h4>

        @hasanyrole('guru|admin|super_admin|guru_pai')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahTahfizModal">
            <i class="fas fa-plus-circle"></i> Catat Setoran
        </button>
        @endhasanyrole
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="border-0 shadow-sm card">
        <div class="card-body">
            <!-- Form Pencarian -->
            <form action="{{ route('tahfiz.index') }}" method="GET" class="mb-3 w-50">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama siswa..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i> Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Surah (Juz 30)</th>
                            <th>Ayat</th>
                            <th>Predikat/Nilai</th>
                            <th>Penyimak</th>
                            <th>Catatan</th>
                            @hasanyrole('super_admin|admin')
                            <th>Aksi</th>
                            @endhasanyrole
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $index => $row)
                        <tr>
                            <td>{{ $records->firstItem() + $index }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                            <td class="fw-bold">{{ $row->student->name ?? 'Siswa Terhapus' }}</td>
                            <td><span class="badge bg-success">{{ $row->surah_name }}</span></td>
                            <td>{{ $row->ayat }}</td>
                            <td>
                                @php
                                    $badgeClass = match($row->predicate) {
                                        'Mumtaz (A)' => 'bg-primary',
                                        'Jayyid Jiddan (B)' => 'bg-info',
                                        'Jayyid (C)' => 'bg-warning text-dark',
                                        'Mengulang' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $row->predicate }}</span>
                            </td>
                            <td>{{ $row->teacher->name ?? '-' }}</td>
                            <td><small class="text-muted">{{ $row->notes ?? '-' }}</small></td>

                            @hasanyrole('super_admin|admin')
                            <td>
                                <form action="{{ route('tahfiz.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                            @endhasanyrole
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada data setoran tahfiz.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Tahfiz -->
@hasanyrole('guru|admin|super_admin|guru_pai')
<div class="modal fade" id="tambahTahfizModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('tahfiz.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Input Setoran Tahfiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label>Nama Siswa <span class="text-danger">*</span></label>
                            <select name="student_id" class="form-select select2" required>
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($students as $siswa)
                                    <option value="{{ $siswa->id }}">{{ $siswa->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label>Tanggal Setoran <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label>Surah (Juz 30) <span class="text-danger">*</span></label>
                            <select name="surah_name" class="form-select select2" required>
                                <option value="">-- Pilih Surah --</option>
                                @foreach($surahs as $surah)
                                    <option value="{{ $surah }}">{{ $surah }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label>Ayat (Contoh: 1-10, atau Lengkap)</label>
                            <input type="text" name="ayat" class="form-control" placeholder="Contoh: 1-15, atau Lengkap" value="Lengkap">
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label>Predikat / Nilai <span class="text-danger">*</span></label>
                            <select name="predicate" class="form-select" required>
                                <option value="Mumtaz (A)">Mumtaz (A) - Sangat Lancar</option>
                                <option value="Jayyid Jiddan (B)">Jayyid Jiddan (B) - Lancar</option>
                                <option value="Jayyid (C)">Jayyid (C) - Cukup Lancar</option>
                                <option value="Maqbul (D)">Maqbul (D) - Kurang Lancar</option>
                                <option value="Mengulang">Mengulang - Belum Hafal</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label>Catatan Penyimak</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Perbaiki tajwid di ayat ke 5..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Setoran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endhasanyrole
@endsection
