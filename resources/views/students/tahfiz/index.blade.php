<x-app-layout>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
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

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('tahfiz.index') }}" method="GET" class="mb-4">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama siswa..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-chalkboard text-muted"></i></span>
                                <select name="kelas_id" class="form-select border-start-0">
                                    <option value="">-- Semua Kelas --</option>
                                    @if(isset($classes))
                                        @foreach($classes as $kelas)
                                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            
                            @if(request('search') || request('kelas_id'))
                                <a href="{{ route('tahfiz.index') }}" class="btn btn-outline-danger">
                                    <i class="fas fa-sync-alt"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
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
                                <td>
                                    @foreach(explode(', ', $row->surah_name) as $surahItem)
                                        <span class="badge bg-success mb-1">{{ $surahItem }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $row->ayat }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($row->predicate) {
                                            'Mumtaz (A)' => 'bg-primary',
                                            'Jayyid Jiddan (B)' => 'bg-info',
                                            'Jayyid (C)' => 'bg-warning text-dark',
                                            'Maqbul (D)' => 'bg-secondary',
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
                        
                        <div class="row mb-3 bg-light p-3 rounded mx-1">
                            <div class="col-md-12">
                                <label class="fw-bold text-primary mb-2"><i class="fas fa-filter"></i> Filter Kelas (Opsional)</label>
                                <select id="filter_kelas_modal" class="form-select border-primary">
                                    <option value="">-- Semua Kelas --</option>
                                    @if(isset($classes))
                                        @foreach($classes as $kelas)
                                            <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="text-muted d-block mt-1">Pilih kelas terlebih dahulu untuk memudahkan mencari nama siswa.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nama Siswa <span class="text-danger">*</span></label>
                                <select name="student_id" id="student_id_modal" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach($students as $siswa)
                                        <option value="{{ $siswa->id }}" data-kelas="{{ $siswa->classroom_id ?? $siswa->kelas_id }}">
                                            {{ $siswa->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Tanggal Setoran <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Surah (Juz 30) <span class="text-danger">*</span></label>
                                <select name="surah_name[]" id="surah_multiple" class="form-select" multiple="multiple" required>
                                    @foreach($surahs as $surah)
                                        <option value="{{ $surah }}">{{ $surah }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Bisa pilih lebih dari satu surah.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Ayat (Contoh: 1-10, atau Lengkap)</label>
                                <input type="text" name="ayat" class="form-control" placeholder="Contoh: 1-15, atau Lengkap" value="Lengkap">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Predikat / Nilai <span class="text-danger">*</span></label>
                                <select name="predicate" class="form-select" required>
                                    <option value="Mumtaz (A)">Mumtaz (A) - Sangat Lancar</option>
                                    <option value="Jayyid Jiddan (B)">Jayyid Jiddan (B) - Lancar</option>
                                    <option value="Jayyid (C)">Jayyid (C) - Cukup Lancar</option>
                                    <option value="Maqbul (D)">Maqbul (D) - Kurang Lancar</option>
                                    <option value="Mengulang">Mengulang - Belum Hafal</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
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

    <script>
        // 1. Script Filter Kelas Dinamis
        document.addEventListener('DOMContentLoaded', function() {
            const filterKelas = document.getElementById('filter_kelas_modal');
            const selectSiswa = document.getElementById('student_id_modal');
            
            if (filterKelas && selectSiswa) {
                const allStudents = Array.from(selectSiswa.options).filter(opt => opt.value !== "");
                let cachedOptions = allStudents.map(opt => opt.cloneNode(true));

                filterKelas.addEventListener('change', function() {
                    const selectedKelas = this.value;
                    selectSiswa.innerHTML = '<option value="">-- Pilih Siswa --</option>';

                    cachedOptions.forEach(opt => {
                        const kelasSiswa = opt.getAttribute('data-kelas');
                        if (selectedKelas === "" || kelasSiswa == selectedKelas) {
                            selectSiswa.appendChild(opt.cloneNode(true));
                        }
                    });

                    // Update UI Select2 untuk siswa jika sudah aktif
                    if (typeof jQuery !== 'undefined' && $('#student_id_modal').hasClass('select2-hidden-accessible')) {
                        $('#student_id_modal').trigger('change');
                    }
                });
            }
        });

        // 2. Inisialisasi Select2 menggunakan jQuery
        if (typeof jQuery !== 'undefined') {
            $(document).ready(function() {
                // Select2 untuk Siswa (Single Select)
                $('#student_id_modal').select2({
                    theme: 'bootstrap-5', // Hapus baris ini jika kamu tidak pakai tema bootstrap-5
                    dropdownParent: $('#tambahTahfizModal'),
                    width: '100%'
                });

                // Select2 untuk Surah (Multi Select)
                $('#surah_multiple').select2({
                    theme: 'bootstrap-5', // Hapus baris ini jika kamu tidak pakai tema bootstrap-5
                    dropdownParent: $('#tambahTahfizModal'),
                    placeholder: "-- Pilih Beberapa Surah --",
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    </script>
    @endhasanyrole
</x-app-layout>