@section('title', 'Pemilihan Tempat PKL')

<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-briefcase me-2"></i>Pemilihan Tempat PKL</h4>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        {{-- Fallback jika variabel teachers belum dikirim dari controller --}}
        @php
            if(!isset($teachers)) {
                $teachers = \App\Models\Teacher::orderBy('name')->get();
            }
        @endphp

        <!-- 1. STATUS PENGAJUAN SAAT INI -->
        @if($myInternship)
            <div class="mb-4 border-0 shadow-sm card">
                <div class="text-white card-header bg-primary">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Status PKL Anda</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h5 class="fw-bold text-dark">{{ $myInternship->industry->name }}</h5>
                            <p class="mb-1 text-muted"><i class="fas fa-map-marker-alt me-2"></i>{{ $myInternship->industry->address }}</p>
                            
                            <!-- ACTION BUTTONS -->
                            <div class="mt-3 flex-wrap gap-2 d-flex">
                                <!-- Cetak Surat -->
                                <a href="{{ route('student.internships.agreement') }}" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                                    <i class="fas fa-print me-1"></i> Cetak Surat Izin
                                </a>

                                @if($myInternship && $myInternship->status == 'completed')
                                    <a href="{{ route('student.internships.transcript') }}" class="btn btn-success fw-bold w-100 mt-2">
                                        <i class="fas fa-certificate me-1"></i> Lihat Transkrip Nilai
                                    </a>
                                @endif

                                <!-- Upload Surat -->
                                <button class="btn btn-warning btn-sm text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="fas fa-upload me-1"></i> {{ $myInternship->parent_consent_file ? 'Update Surat' : 'Upload Surat' }}
                                </button>

                                <!-- Lihat Berkas -->
                                @if($myInternship->parent_consent_file)
                                    <a href="{{ asset('storage/'.$myInternship->parent_consent_file) }}" target="_blank" class="btn btn-info btn-sm text-white fw-bold">
                                        <i class="fas fa-eye me-1"></i> Lihat Berkas
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 text-center col-md-5 text-md-end mt-md-0">
                            <div class="mb-2 fw-bold text-muted">Status Pengajuan:</div>
                            @if($myInternship->status == 'pending')
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fas fa-clock me-1"></i> Menunggu Persetujuan</span>
                            @elseif($myInternship->status == 'active')
                                <span class="badge bg-success fs-6 px-3 py-2"><i class="fas fa-check-circle me-1"></i> Disetujui / Aktif</span>
                            @elseif($myInternship->status == 'completed')
                                <span class="badge bg-info text-dark fs-6 px-3 py-2"><i class="fas fa-flag-checkered me-1"></i> Selesai</span>
                            @elseif($myInternship->status == 'cancelled')
                                <span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-times-circle me-1"></i> Ditolak / Dibatalkan</span>
                            @endif

                            <!-- Status Upload -->
                            <div class="mt-2 small">
                                @if($myInternship->parent_consent_file)
                                    <span class="text-success fw-bold"><i class="fas fa-check-circle"></i> Surat Izin Terupload</span>
                                @else
                                    <span class="text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> Surat Izin Belum Diupload</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. PILIH PEMBIMBING (Hanya jika PKL sudah Aktif) -->
            @if($myInternship->status == 'active')
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="bg-white border-bottom card-header py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-chalkboard-teacher me-2 text-warning"></i>Guru Pembimbing Lapangan</h6>
                    </div>
                    <div class="card-body">
                        @if($myInternship->advisor_id)
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $myInternship->advisor->name }}</h5>
                                    <p class="text-muted small mb-0">NIP: {{ $myInternship->advisor->nip ?? '-' }}</p>
                                </div>
                                <div>
                                    @if($myInternship->advisor_status == 'pending')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Menunggu Persetujuan Admin</span>
                                    @elseif($myInternship->advisor_status == 'approved')
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Disetujui</span>
                                    @else
                                        <span class="badge bg-secondary">Status Tidak Diketahui</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info small mb-3 border-0 bg-info-subtle text-info-emphasis">
                                <i class="fas fa-info-circle me-1"></i> Silakan ajukan guru yang Anda inginkan sebagai pembimbing lapangan.
                            </div>
                            
                            <form action="{{ route('student.internships.request_advisor') }}" method="POST" class="row g-2 align-items-center">
                                @csrf
                                <input type="hidden" name="internship_id" value="{{ $myInternship->id }}">
                                
                                <div class="col-md-8">
                                    <select name="advisor_id" class="form-select" required>
                                        <option value="">-- Pilih Guru Pembimbing --</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary fw-bold w-100">
                                        <i class="fas fa-paper-plane me-1"></i> Ajukan Pembimbing
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        <!-- 3. DAFTAR TEMPAT PKL YANG TERSEDIA (Hanya Tampil Jika Belum Mengajukan) -->
        @if(!$myInternship || in_array($myInternship->status, ['completed', 'cancelled']))
            <div class="border-0 shadow-sm card">
                <div class="bg-white border-bottom card-header py-3">
                    <h6 class="mb-0 fw-bold text-dark">Daftar Industri / Tempat PKL Tersedia</h6>
                </div>
                <div class="p-0 card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nama Perusahaan / DU-DI</th>
                                    <th>Bidang</th>
                                    <th class="text-center">Kuota Tersedia</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($industries as $ind)
                                    @php 
                                        $sisaKuota = $ind->quota - $ind->terisi;
                                        $isFull = ($ind->quota > 0 && $sisaKuota <= 0);
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $ind->name }}</div>
                                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> {{ \Illuminate\Support\Str::limit($ind->address, 50) }}</small>
                                        </td>
                                        <td><span class="badge bg-info text-dark">{{ $ind->sector ?? '-' }}</span></td>
                                        <td class="text-center">
                                            @if($ind->quota == 0)
                                                <span class="text-success fw-bold">Tak Terbatas</span>
                                            @elseif($isFull)
                                                <span class="text-danger fw-bold">Penuh (0/{{ $ind->quota }})</span>
                                            @else
                                                <span class="text-primary fw-bold">{{ $sisaKuota }} / {{ $ind->quota }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isFull)
                                                <button class="btn btn-sm btn-secondary disabled">Penuh</button>
                                            @else
                                                <form action="{{ route('student.internships.apply') }}" method="POST" onsubmit="return confirm('Anda yakin ingin mengajukan PKL di {{ $ind->name }}? Pastikan pilihan Anda sudah tepat.')">
                                                    @csrf
                                                    <input type="hidden" name="industry_id" value="{{ $ind->id }}">
                                                    <button type="submit" class="btn btn-sm btn-primary fw-bold">
                                                        <i class="fas fa-paper-plane me-1"></i> Ajukan
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-muted">Belum ada daftar tempat PKL yang tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- MODAL UPLOAD SURAT IZIN -->
    @if($myInternship)
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('student.internships.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="internship_id" value="{{ $myInternship->id }}">
                    
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title fw-bold"><i class="fas fa-upload me-2"></i> Upload Surat Izin Orang Tua</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="alert alert-info small border-0 bg-info-subtle text-info-emphasis">
                            <i class="fas fa-info-circle me-1"></i> Silakan <strong>cetak surat izin</strong> terlebih dahulu, minta tanda tangan orang tua di atas materai, lalu foto/scan dan upload di sini.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">File Surat (PDF/JPG/PNG)</label>
                            <input type="file" name="file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Maksimal ukuran file 2MB.</small>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold">Upload Berkas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</x-app-layout>