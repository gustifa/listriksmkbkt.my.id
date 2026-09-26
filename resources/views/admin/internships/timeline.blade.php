@section('title', 'Timeline Kegiatan PKL')

<x-app-layout>
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary"><i class="fas fa-calendar-alt me-2"></i>Timeline Kegiatan PKL</h4>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addTimelineModal">
                <i class="fas fa-plus me-1"></i> Tambah Agenda
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card border-0 shadow-lg">
            <div class="card-body">
                <!-- Timeline Visual -->
                <div class="timeline-container ps-2">
                    @forelse($timelines as $item)
                        @php $status = $item->calculated_status; @endphp
                        <div class="timeline-item position-relative pb-4 ps-4 border-start border-2 {{ $status == 'completed' ? 'border-success' : ($status == 'active' ? 'border-primary' : 'border-secondary') }}">
                            <!-- Dot -->
                            <div class="position-absolute top-0 start-0 translate-middle rounded-circle border border-white shadow-sm
                                {{ $status == 'completed' ? 'bg-success' : ($status == 'active' ? 'bg-primary' : 'bg-secondary') }}"
                                style="width: 16px; height: 16px;"></div>
                            
                            <div class="card border-0 bg-light shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="fw-bold mb-1 {{ $status == 'completed' ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                                {{ $item->title }}
                                            </h6>
                                            <small class="text-muted">
                                                <i class="far fa-calendar me-1"></i> 
                                                {{ $item->start_date->format('d M Y') }} 
                                                @if($item->end_date) s.d {{ $item->end_date->format('d M Y') }} @endif
                                            </small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-secondary" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editTimeline({{ json_encode($item) }})">Edit</a></li>
                                                <li>
                                                    <form action="{{ route('admin.timeline.destroy', $item->id) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item text-danger" onclick="return confirm('Hapus agenda ini?')">Hapus</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    @if($item->description)
                                        <p class="mb-0 mt-2 small text-secondary">{{ $item->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">Belum ada agenda kegiatan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit -->
    <div class="modal fade" id="addTimelineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.timeline.store') }}" method="POST" id="timelineForm">
                    @csrf
                    <div id="method-spoof"></div>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTitle">Tambah Agenda</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Kegiatan</label>
                            <input type="text" name="title" id="inp_title" class="form-control" required placeholder="Contoh: Monitoring Ke-1">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="inp_start" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Sampai (Opsional)</label>
                                <input type="date" name="end_date" id="inp_end" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" id="inp_desc" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Status --</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="active">Active</option>
                                <option value="completed">Selesai</option> 
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function editTimeline(data) {
            document.getElementById('modalTitle').innerText = 'Edit Agenda';
            document.getElementById('timelineForm').action = '/admin/pkl/timeline/' + data.id;
            document.getElementById('method-spoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            
            document.getElementById('inp_title').value = data.title;
            document.getElementById('inp_start').value = data.start_date.split('T')[0];
            document.getElementById('inp_end').value = data.end_date ? data.end_date.split('T')[0] : '';
            document.getElementById('inp_desc').value = data.description;
            document.getElementById('status').value = data.status;
            
            new bootstrap.Modal(document.getElementById('addTimelineModal')).show();
        }
        
        document.getElementById('addTimelineModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('timelineForm').reset();
            document.getElementById('modalTitle').innerText = 'Tambah Agenda';
            document.getElementById('method-spoof').innerHTML = '';
            document.getElementById('timelineForm').action = "{{ route('admin.timeline.store') }}";
        });
    </script>
    @endpush
</x-app-layout>