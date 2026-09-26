@section('title', 'Rekap Tahfiz Juz 30')

<x-app-layout>
    @push('styles')
    <style>
        .hover-elevate { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-elevate:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .record-list::-webkit-scrollbar { width: 4px; }
        .record-list::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        #tahfiz-container { transition: opacity 0.3s ease; }
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

        <div class="mb-4 bg-white border-0 shadow-sm card">
            <div class="p-4 card-body">
                <form id="filterForm" class="p-3 mb-0 border rounded bg-light">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="bg-white input-group-text"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" id="search_input" name="search" class="form-control border-start-0" placeholder="Cari nama siswa..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="bg-white input-group-text"><i class="fas fa-chalkboard text-muted"></i></span>
                                <select name="kelas_id" id="kelas_filter" class="form-select border-start-0">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($classes as $kelas)
                                        <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="gap-2 col-md-3 d-flex">
                            <button class="btn btn-primary w-100" type="submit">Filter</button>
                            <button type="button" id="btnReset" class="btn btn-outline-secondary w-100">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="tahfiz-container">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                @php $groupedRecords = $records->groupBy('student_id'); @endphp

                @forelse($groupedRecords as $studentId => $studentRecords)
                    @php
                        // Ambil data siswa dari record pertama yang valid
                        $studentInfo = $studentRecords->first()->student;
                        $namaSiswa = $studentInfo ? $studentInfo->name : 'Siswa Terhapus / Data Tidak Sinkron';
                    @endphp
                    <div class="col">
                        <div class="border-0 shadow-sm card h-100 hover-elevate">
                            <div class="pt-3 pb-3 text-white border-0 card-header bg-primary rounded-top">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h6 class="mb-0 fw-bold text-truncate" title="{{ $namaSiswa }}">{{ $namaSiswa }}</h6>
                                        <small class="text-white-50">{{ $studentRecords->count() }} Setoran</small>
                                    </div>
                                </div>
                            </div>
                            <div class="p-0 card-body">
                                <div class="list-group list-group-flush record-list" style="max-height: 300px; overflow-y: auto;">
                                    @foreach($studentRecords as $row)
                                        @php
                                            $badge = match($row->predicate) {
                                                'Mumtaz (A)' => 'primary',
                                                'Jayyid Jiddan (B)' => 'info',
                                                'Jayyid (C)' => 'warning text-dark',
                                                'Mengulang' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <div class="p-3 list-group-item border-bottom">
                                            <div class="mb-2 d-flex justify-content-between align-items-start">
                                                <div>
                                                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</small>
                                                    <div class="flex-wrap gap-1 mt-1 d-flex">
                                                        @foreach(explode(', ', $row->surah_name) as $s)
                                                            <span class="badge bg-success" style="font-size: 0.7rem;">{{ $s }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <span class="badge bg-{{ $badge }} rounded-pill" style="font-size: 0.65rem;">{{ $row->predicate }}</span>
                                            </div>
                                            @if($row->notes)
                                                <div class="p-2 mt-1 rounded bg-light border-start border-3 border-{{ str_replace(' text-dark', '', $badge) }} small text-muted">
                                                    {{ $row->notes }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-5 text-center col-12"><p class="text-muted">Data tidak ditemukan.</p></div>
                @endforelse
            </div>
            <div class="mt-4 d-flex justify-content-center ajax-pagination">{{ $records->links() }}</div>
        </div>
    </div>

    @hasanyrole('guru|admin|super_admin|guru_pai')
    <div class="modal fade" id="tambahTahfizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('tahfiz.store') }}" method="POST">
                    @csrf
                    <div class="text-white modal-header bg-primary">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Catat Hafalan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Siswa</label>
                                <select name="student_id" class="form-select select2" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Tanggal</label>
                                <input type="date" name="date" class="form-control" value="{{ $today }}" required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Surah</label>
                                <select name="surah_name[]" class="form-select select2" multiple="multiple" required>
                                    @foreach($surahs as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Ayat</label>
                                <input type="text" name="ayat" class="form-control" placeholder="Contoh: 1-10" value="Lengkap">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Nilai</label>
                                <select name="predicate" class="form-select" required>
                                    <option value="Mumtaz (A)">Mumtaz (A)</option>
                                    <option value="Jayyid Jiddan (B)">Jayyid Jiddan (B)</option>
                                    <option value="Jayyid (C)">Jayyid (C)</option>
                                    <option value="Mengulang">Mengulang</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Catatan</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endhasanyrole

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $('#tambahTahfizModal') });

            function fetch_data(page = 1) {
                let search = $('#search_input').val();
                let kelas_id = $('#kelas_filter').val();
                $('#tahfiz-container').css('opacity', '0.4');
                $.ajax({
                    url: "{{ route('tahfiz.index') }}",
                    data: { page: page, search: search, kelas_id: kelas_id },
                    success: function(res) {
                        $('#tahfiz-container').html($(res).find('#tahfiz-container').html()).css('opacity', '1');
                    }
                });
            }

            $('#search_input').on('keyup', function() { fetch_data(1); });
            $('#kelas_filter').on('change', function() { fetch_data(1); });
            $('#btnReset').on('click', function() { $('#search_input').val(''); $('#kelas_filter').val(''); fetch_data(1); });
        });
    </script>
    @endpush
</x-app-layout>
