@section('title', 'Monitoring Siswa')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-primary"><i class="fas fa-binoculars me-2"></i>Monitoring Siswa</h4>
                <p class="text-muted mb-0">Dashboard Walas & Bimbingan Konseling</p>
            </div>
        </div>

        <!-- Daftar Kelas -->
        <div class="row">
            @forelse($myClasses as $class)
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm hover-shadow transition h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">{{ $class->name }}</h5>
                                    <small class="text-muted">{{ $class->students()->count() }} Siswa</small>
                                </div>
                                <div class="bg-primary/10 p-2 rounded-circle text-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <span class="badge bg-light text-secondary border">
                                    @if(Auth::user()->teacher && $class->homeroom_teacher_id == Auth::user()->teacher->id)
                                        <i class="fas fa-user-tie me-1"></i> Wali Kelas
                                    @else
                                        <i class="fas fa-heart me-1"></i> Guru BK
                                    @endif
                                </span>
                            </div>

                            <div class="d-grid gap-2">
                                <!-- Tombol Lihat Harian -->
                                <a href="{{ route('teacher.monitoring.show', $class->id) }}" class="btn btn-outline-primary fw-bold">
                                    <i class="fas fa-eye me-1"></i> Lihat Data Hari Ini
                                </a>
                                
                                <!-- Tombol Cetak Laporan (Baru) -->
                                <button type="button" class="btn btn-primary fw-bold text-white" onclick="openPrintModal('{{ $class->id }}', '{{ $class->name }}')">
                                    <i class="fas fa-print me-1"></i> Cetak Laporan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-folder-open text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-muted">Anda belum ditugaskan di kelas manapun.</h5>
                    <p class="text-secondary small">Hubungi admin untuk seting Wali Kelas atau Guru BK.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- MODAL PILIH LAPORAN -->
    <div class="modal fade" id="printModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-file-pdf me-2"></i> Cetak Laporan Kelas <span id="modalClassName" class="fw-bold"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('teacher.monitoring.print') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="classroom_id" id="inputClassId">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jenis Laporan</label>
                            <select name="type" class="form-select" required>
                                <option value="gate">Absensi Gerbang (Kehadiran)</option>
                                <option value="learning">Absensi Pembelajaran (Mapel)</option>
                                <option value="prayer">Absensi Sholat</option>
                            </select>
                            <div class="form-text text-muted small">
                                Pilih jenis rekapitulasi yang ingin dicetak.
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Bulan</label>
                                <select name="month" class="form-select" required>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tahun</label>
                                <select name="year" class="form-select" required>
                                    @foreach(range(date('Y'), date('Y')-2) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="fas fa-print me-1"></i> Cetak PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openPrintModal(id, name) {
            // Set nilai ID dan Nama Kelas ke dalam Modal
            document.getElementById('inputClassId').value = id;
            document.getElementById('modalClassName').innerText = name;
            
            // Tampilkan Modal
            var myModal = new bootstrap.Modal(document.getElementById('printModal'));
            myModal.show();
        }
    </script>
    @endpush
</x-app-layout>