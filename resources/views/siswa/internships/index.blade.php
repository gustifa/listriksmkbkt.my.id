@section('title', 'Dashboard PKL Siswa')

<x-app-layout>
    <div class="page-content">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-briefcase me-2"></i>Dashboard PKL</h4>
                <p class="text-muted small mb-0">Pusat informasi dan administrasi Praktik Kerja Lapangan.</p>
            </div>
            <div>
                <span class="badge bg-white text-dark border shadow-sm px-3 py-2">
                    <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-danger border-4">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- KOLOM KIRI: STATUS & AKTIVITAS UTAMA -->
            <div class="col-lg-8">
                
                <!-- 1. KARTU STATUS PENGAJUAN (Jika Ada) -->
                @if($myInternship)
                    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                        <div class="card-header bg-gradient-primary text-white py-3">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Status PKL Anda</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="fw-bold text-dark mb-1">{{ $myInternship->industry->name }}</h4>
                                    <p class="text-muted mb-3"><i class="fas fa-map-marker-alt me-2 text-danger"></i>{{ $myInternship->industry->address }}</p>
                                    
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                            <div>
                                                <small class="d-block text-muted" style="font-size: 0.7rem;">PEMBIMBING</small>
                                                <span class="fw-bold text-dark small">{{ $myInternship->advisor->name ?? 'Belum ditentukan' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 text-md-end border-start-md ps-md-4 mt-3 mt-md-0">
                                    <div class="mb-2 text-muted small fw-bold text-uppercase">Status Saat Ini</div>
                                    @if($myInternship->status == 'pending')
                                        <span class="badge bg-warning text-dark fs-6 px-3 py-2 w-100 rounded-pill"><i class="fas fa-clock me-1"></i> Menunggu</span>
                                    @elseif($myInternship->status == 'active')
                                        <span class="badge bg-success fs-6 px-3 py-2 w-100 rounded-pill"><i class="fas fa-check-circle me-1"></i> Aktif</span>
                                    @elseif($myInternship->status == 'completed')
                                        <span class="badge bg-info text-white fs-6 px-3 py-2 w-100 rounded-pill"><i class="fas fa-flag-checkered me-1"></i> Selesai</span>
                                    @elseif($myInternship->status == 'cancelled')
                                        <span class="badge bg-danger fs-6 px-3 py-2 w-100 rounded-pill"><i class="fas fa-times-circle me-1"></i> Ditolak</span>
                                    @endif

                                    <!-- Status Upload Surat -->
                                    <div class="mt-3 p-2 rounded bg-light border text-center">
                                        @if($myInternship->parent_consent_file)
                                            <small class="text-success fw-bold"><i class="fas fa-file-check me-1"></i> Surat Izin OK</small>
                                        @else
                                            <small class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i> Upload Surat!</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- ACTION BUTTONS -->
                            <div class="d-flex flex-wrap gap-2">
                                <!-- Cetak Surat -->
                                <a href="{{ route('student.internships.agreement') }}" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                                    <i class="fas fa-print me-1"></i> Cetak Surat Izin
                                </a>

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

                                <!-- Lihat Transkrip (Jika Selesai) -->
                                @if($myInternship->status == 'completed' || ($myInternship->grade ?? false))
                                    <a href="{{ route('student.internships.transcript') }}" class="btn btn-success btn-sm fw-bold">
                                        <i class="fas fa-certificate me-1"></i> Transkrip Nilai
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 2. PILIH PEMBIMBING (Jika Aktif tapi belum ada) -->
                    @if($myInternship->status == 'active' && !$myInternship->advisor_id)
                        <div class="card border-0 shadow-sm mb-4 border-start border-warning border-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-chalkboard-teacher me-2 text-warning"></i>Pilih Guru Pembimbing</h6>
                                <p class="small text-muted mb-3">Silakan ajukan guru yang Anda inginkan sebagai pembimbing lapangan.</p>
                                
                                <form action="{{ route('student.internships.request_advisor') }}" method="POST" class="row g-2 align-items-center">
                                    @csrf
                                    <input type="hidden" name="internship_id" value="{{ $myInternship->id }}">
                                    <div class="col-md-8">
                                        <select name="advisor_id" class="form-select form-select-sm" required>
                                            <option value="">-- Pilih Guru --</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Ajukan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($myInternship->advisor_status == 'pending')
                         <div class="alert alert-warning small border-0 shadow-sm">
                            <i class="fas fa-clock me-2"></i> Pengajuan Guru Pembimbing sedang menunggu persetujuan Admin/Kaprog.
                         </div>
                    @endif

                @endif

                <!-- 3. DAFTAR TEMPAT PKL (Jika Belum Ada / Ditolak) -->
                @if(!$myInternship || in_array($myInternship->status, ['completed', 'cancelled']))
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-building me-2 text-primary"></i>Daftar Industri Tersedia</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-uppercase small">
                                        <tr>
                                            <th class="ps-4">Nama Industri</th>
                                            <th>Bidang</th>
                                            <th class="text-center">Kuota</th>
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
                                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> {{ \Illuminate\Support\Str::limit($ind->address, 40) }}</small>
                                                </td>
                                                <td><span class="badge bg-light text-dark border">{{ $ind->sector ?? '-' }}</span></td>
                                                <td class="text-center">
                                                    @if($ind->quota == 0)
                                                        <span class="badge bg-success">Unlimited</span>
                                                    @elseif($isFull)
                                                        <span class="badge bg-danger">Penuh</span>
                                                    @else
                                                        <span class="badge bg-primary">{{ $sisaKuota }} / {{ $ind->quota }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isFull)
                                                        <button class="btn btn-sm btn-secondary disabled" style="opacity: 0.6">Penuh</button>
                                                    @else
                                                        <form action="{{ route('student.internships.apply') }}" method="POST" onsubmit="return confirm('Yakin ingin mengajukan PKL di {{ $ind->name }}?')">
                                                            @csrf
                                                            <input type="hidden" name="industry_id" value="{{ $ind->id }}">
                                                            <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">Ajukan</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">Belum ada daftar tempat PKL.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- KOLOM KANAN: TIMELINE KEGIATAN -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-check me-2 text-primary"></i>Timeline Kegiatan</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($timelines as $item)
                                @php 
                                    $status = $item->status; 
                                    $bgColor = $status == 'active' ? 'bg-primary-subtle' : '';
                                    $iconColor = $status == 'completed' ? 'text-success' : ($status == 'active' ? 'text-primary' : 'text-secondary');
                                    $icon = $status == 'completed' ? 'fa-check-circle' : ($status == 'active' ? 'fa-clock' : 'fa-circle');
                                @endphp
                                
                                <div class="list-group-item {{ $bgColor }} p-3 border-start border-4 {{ $status == 'active' ? 'border-primary' : 'border-transparent' }}">
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 fw-bold {{ $status == 'completed' ? 'text-muted text-decoration-line-through' : 'text-dark' }}">
                                            {{ $item->title }}
                                        </h6>
                                        <i class="fas {{ $icon }} {{ $iconColor }}"></i>
                                    </div>
                                    <small class="d-block text-muted mb-1">
                                        {{ $item->start_date->format('d M') }} 
                                        @if($item->end_date) - {{ $item->end_date->format('d M Y') }} @endif
                                    </small>
                                    @if($item->description)
                                        <p class="mb-0 small text-secondary fst-italic">{{ $item->description }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted">
                                    <small>Belum ada jadwal kegiatan.</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="card border-0 shadow-sm mt-3 bg-light">
                    <div class="card-body">
                        <small class="fw-bold text-uppercase text-muted mb-2 d-block">Kontak Bantuan</small>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-1"><i class="fas fa-phone me-2 text-primary"></i> Hubin: 0812-XXXX-XXXX</li>
                            <li><i class="fas fa-envelope me-2 text-primary"></i> hubin@smkn1bkt.sch.id</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL UPLOAD SURAT IZIN -->
    @if($myInternship)
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('student.internships.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="internship_id" value="{{ $myInternship->id }}">
                    
                    <div class="modal-header bg-warning text-dark border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-upload me-2"></i> Upload Surat Izin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="alert alert-light border border-warning text-dark small mb-3">
                            <strong>Langkah-langkah:</strong>
                            <ol class="ps-3 mb-0">
                                <li>Cetak surat izin melalui tombol <b>Cetak</b>.</li>
                                <li>Minta orang tua menandatangani di atas materai.</li>
                                <li>Foto/Scan surat tersebut.</li>
                                <li>Upload file (JPG/PDF) di sini.</li>
                            </ol>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Pilih File</label>
                            <input type="file" name="file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Maksimal 2MB.</small>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold px-4">Upload Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</x-app-layout>