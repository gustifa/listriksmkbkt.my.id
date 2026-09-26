@section('title', 'Jurnal Mengajar Guru')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-book-open me-2"></i> Jurnal Mengajar</h5>

                        <!-- Filter Tanggal -->
                        <form action="{{ route('journal.index') }}" method="GET" class="d-flex">
                            <input type="date" name="date" class="form-control form-control-sm me-2"
                                   value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()">
                        </form>
                    </div>
                    <div class="card-body">

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Menampilkan jadwal untuk hari: <strong>{{ $date->translatedFormat('l, d F Y') }}</strong>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">Jam</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Status Jurnal</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($schedules as $sched)
                                        @php
                                            $journal = $filledJournals[$sched->id] ?? null;
                                            $jam = substr($sched->start_time, 0, 5) . '-' . substr($sched->end_time, 0, 5);
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-bold">{{ $jam }}</td>
                                            <td>{{ $sched->classroom->name }}</td>
                                            <td>{{ $sched->subject->name }}</td>
                                            <td>
                                                @if($journal)
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Sudah Diisi</span>
                                                    <div class="mt-1 small text-muted text-truncate" style="max-width: 200px;">
                                                        Topik: {{ $journal->topic }}
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary"><i class="fas fa-times"></i> Belum Diisi</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-primary" onclick="openJournalModal('{{ $sched->id }}', '{{ $sched->subject->name }}', '{{ $sched->classroom->name }}')">
                                                    <i class="fas fa-edit"></i> {{ $journal ? 'Edit' : 'Isi' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-4 text-center text-muted">
                                                Tidak ada jadwal mengajar pada tanggal ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL JURNAL (REUSABLE) -->
    <div class="modal fade" id="journalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-pen me-2"></i> Input Jurnal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="journalForm">
                        @csrf
                        <input type="hidden" name="schedule_id" id="j_schedule_id">
                        <!-- Kirim tanggal agar tersimpan sesuai tanggal yang dipilih di filter -->
                        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

                        <div class="mb-3 border alert alert-light">
                            <small class="fw-bold d-block" id="label_mapel">Mapel: -</small>
                            <small class="fw-bold d-block" id="label_kelas">Kelas: -</small>
                            <small class="fw-bold d-block">Tanggal: {{ $date->translatedFormat('d F Y') }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Materi / Topik</label>
                            <input type="text" class="form-control" name="topic" id="j_topic" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Aktivitas</label>
                            <textarea class="form-control" name="activity" id="j_activity" rows="10"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea class="form-control" name="notes" id="j_notes" rows="4"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" onclick="saveJournal()" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function openJournalModal(scheduleId, mapel, kelas) {
            $('#journalForm')[0].reset();
            $('#j_schedule_id').val(scheduleId);
            $('#label_mapel').text('Mapel: ' + mapel);
            $('#label_kelas').text('Kelas: ' + kelas);

            // Load Data Existing (sesuai tanggal yang dipilih)
            let date = "{{ $date->format('Y-m-d') }}";

            $.get("{{ url('/journal/show') }}/" + scheduleId + "?date=" + date, function(res) {
                if(res.data) {
                    $('#j_topic').val(res.data.topic);
                    $('#j_activity').val(res.data.activity);
                    $('#j_notes').val(res.data.notes);
                }
                var myModal = new bootstrap.Modal(document.getElementById('journalModal'));
                myModal.show();
            });
        }

        function saveJournal() {
            let formData = new FormData(document.getElementById('journalForm'));

            $.ajax({
                url: "{{ route('journal.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal menyimpan jurnal.', 'error');
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
