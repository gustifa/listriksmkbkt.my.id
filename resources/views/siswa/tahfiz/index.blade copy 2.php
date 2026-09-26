@section('title', 'Rekap Tahfiz Juz 30')

<x-app-layout>
    @push('styles')
    <style>
        .hover-elevate {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-elevate:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        .record-list::-webkit-scrollbar {
            width: 4px;
        }
        .record-list::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
        }
    </style>
    @endpush

    <div class="py-4 page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold text-primary"><i class="fas fa-book-open me-2"></i> Rekap Tahfiz Juz 30</h4>
                <p class="mb-0 text-muted small">Daftar setoran hafalan Al-Qur'an siswa</p>
            </div>

            @hasanyrole('guru|admin|super_admin|guru_pai')
            <button class="shadow-sm btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahTahfizModal">
                <i class="fas fa-plus me-1"></i> Catat Setoran
            </button>
            @endhasanyrole
        </div>

        @if(session('success'))
            <div class="shadow-sm alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="mb-4 bg-white border-0 shadow-sm card">
            <div class="p-4 card-body">
                <form action="{{ route('tahfiz.index') }}" method="GET" class="p-3 mb-0 border rounded bg-light">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="bg-white input-group-text"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama siswa..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit">Cari</button>
                        </div>
                        @if(request('search'))
                            <div class="col-md-2">
                                <a href="{{ route('tahfiz.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            @php
                // Mengelompokkan record berdasarkan ID siswa
                $groupedRecords = $records->groupBy('student_id');
            @endphp

            @forelse($groupedRecords as $studentId => $studentRecords)
                @php
                    $siswa = $studentRecords->first()->student;
                @endphp
                <div class="col">
                    <div class="border-0 shadow-sm card h-100 hover-elevate">

                        <div class="pt-3 pb-3 text-white border-0 card-header bg-primary rounded-top">
                            <div class="d-flex align-items-center">
                                <div class="bg-white shadow-sm text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 45px; height: 45px;">
                                    <i class="fas fa-user-graduate fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 card-title fw-bold text-truncate" style="max-width: 200px;">
                                        {{ $siswa->name ?? 'Siswa Terhapus' }}
                                    </h5>
                                    <small class="text-white-50"><i class="fas fa-list-ol me-1"></i> {{ $studentRecords->count() }} Setoran</small>
                                </div>
                            </div>
                        </div>

                        <div class="p-0 card-body">
                            <div class="list-group list-group-flush record-list" style="max-height: 350px; overflow-y: auto;">
                                @foreach($studentRecords as $row)
                                    @php
                                        $badgeColor = match($row->predicate) {
                                            'Mumtaz (A)' => 'primary',
                                            'Jayyid Jiddan (B)' => 'info',
                                            'Jayyid (C)' => 'warning text-dark',
                                            'Mengulang' => 'danger',
                                            default => 'secondary'
                                        };
                                        $icon = $row->predicate == 'Mengulang' ? 'fa-times-circle' : 'fa-check-circle';
                                    @endphp

                                    <div class="list-group-item p-3 border-bottom {{ $loop->even ? 'bg-light' : '' }}">
                                        <div class="mb-2 d-flex justify-content-between align-items-start">

                                            <div>
                                                <small class="mb-1 text-muted d-block">
                                                    <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}
                                                </small>
                                                <div class="flex-wrap gap-1 mb-1 d-flex">
                                                    @foreach(explode(', ', $row->surah_name) as $surah)
                                                        <span class="badge bg-success"><i class="fas fa-quran me-1"></i>{{ trim($surah) }}</span>
                                                    @endforeach
                                                </div>
                                                <small class="text-secondary">Ayat: {{ $row->ayat ?? 'Lengkap' }}</small>
                                            </div>

                                            <div class="text-end">
                                                <span class="badge bg-{{ $badgeColor }} rounded-pill mb-2 shadow-sm">
                                                    <i class="fas {{ $icon }}"></i> {{ $row->predicate }}
                                                </span>

                                                @hasanyrole('super_admin|admin')
                                                <div>
                                                    <form action="{{ route('tahfiz.destroy', $row->id) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <button class="px-2 py-0 btn btn-sm btn-outline-danger" style="font-size: 0.7rem;" onclick="return confirm('Hapus data setoran ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                @endhasanyrole
                                            </div>
                                        </div>

                                        @if($row->notes)
                                            <div class="bg-white p-2 rounded small text-secondary mt-2 border-start border-3 border-{{ str_replace(' text-dark', '', $badgeColor) }} shadow-sm">
                                                <strong><i class="fas fa-comment-dots me-1"></i>Catatan:</strong> {{ $row->notes }}
                                            </div>
                                        @endif

                                        <div class="mt-2 text-muted small" style="font-size: 0.7rem;">
                                            <i class="fas fa-chalkboard-teacher me-1"></i> Disimak oleh: {{ $row->teacher->name ?? '-' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="border-0 shadow-sm card w-100">
                        <div class="py-5 text-center card-body text-muted">
                            <i class="mb-3 fas fa-folder-open fa-3x text-secondary"></i>
                            <p class="mb-0 fs-5">Belum ada data setoran tahfiz yang dicatat.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $records->links() }}
        </div>

    </div>

    @hasanyrole('guru|admin|super_admin|guru_pai')
    <div class="modal fade" id="tambahTahfizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('tahfiz.store') }}" method="POST">
                    @csrf
                    <div class="text-white modal-header bg-primary">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Input Setoran Tahfiz</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="py-2 mb-3 alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i> Gunakan form ini untuk mencatat setoran siswa.
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Nama Siswa <span class="text-danger">*</span></label>
                                <select name="student_id" class="form-select select2" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach($students as $siswa)
                                        <option value="{{ $siswa->id }}">{{ $siswa->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Tanggal Setoran <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Surah (Juz 30) <span class="text-danger">*</span></label>
                                <select name="surah_name[]" class="form-select select2" multiple="multiple" data-placeholder="-- Pilih Surah --" required>
                                    @foreach($surahs as $surah)
                                        <option value="{{ $surah }}">{{ $surah }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Ayat</label>
                                <input type="text" name="ayat" class="form-control" placeholder="Contoh: 1-15, atau Lengkap" value="Lengkap">
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Predikat / Nilai <span class="text-danger">*</span></label>
                                <select name="predicate" class="form-select" required>
                                    <option value="Mumtaz (A)">Mumtaz (A) - Sangat Lancar</option>
                                    <option value="Jayyid Jiddan (B)">Jayyid Jiddan (B) - Lancar</option>
                                    <option value="Jayyid (C)">Jayyid (C) - Cukup Lancar</option>
                                    <option value="Maqbul (D)">Maqbul (D) - Kurang Lancar</option>
                                    <option value="Mengulang">Mengulang - Belum Hafal</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Catatan Penyimak</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Perbaiki tajwid di ayat ke 5..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Setoran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endhasanyrole

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#tambahTahfizModal')
            });
        });
    </script>
    @endpush

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Cek apakah ada session 'success' yang dikirim dari controller
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000 // Notifikasi hilang otomatis setelah 2 detik
            });
        @endif

        // Opsional: Cek jika ada error validasi
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Mohon periksa kembali inputan Anda.',
            });
        @endif
    </script>
</x-app-layout>
