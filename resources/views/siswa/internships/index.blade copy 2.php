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

        <!-- STATUS PENGAJUAN SAAT INI -->
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
                            <p class="mb-3 text-muted"><i class="fas fa-user-tie me-2"></i>Pembimbing: {{ $myInternship->advisor->name ?? 'Belum ditentukan' }}</p>

                            <!-- ACTION BUTTONS -->
                            <div class="flex-wrap gap-2 d-flex">
                                <!-- 1. Cetak Surat -->
                                <a href="{{ route('student.internships.agreement') }}" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                                    <i class="fas fa-print me-1"></i> Cetak Surat Izin
                                </a>

                                <!-- 2. Upload Surat -->
                                <button class="btn btn-warning btn-sm text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="fas fa-upload me-1"></i> {{ $myInternship->parent_consent_file ? 'Update Surat' : 'Upload Surat' }}
                                </button>

                                <!-- 3. Lihat Berkas -->
                                @if($myInternship->parent_consent_file)
                                    <a href="{{ asset('storage/'.$myInternship->parent_consent_file) }}" target="_blank" class="text-white btn btn-info btn-sm fw-bold">
                                        <i class="fas fa-eye me-1"></i> Lihat Berkas
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 text-center col-md-5 text-md-end mt-md-0">
                            <div class="mb-2 fw-bold text-muted">Status Pengajuan:</div>
                            @if($myInternship->status == 'pending')
                                <span class="px-3 py-2 badge bg-warning text-dark fs-6"><i class="fas fa-clock me-1"></i> Menunggu Persetujuan</span>
                            @elseif($myInternship->status == 'active')
                                <span class="px-3 py-2 badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> Disetujui / Aktif</span>
                            @elseif($myInternship->status == 'completed')
                                <span class="px-3 py-2 badge bg-info text-dark fs-6"><i class="fas fa-flag-checkered me-1"></i> Selesai</span>
                            @elseif($myInternship->status == 'cancelled')
                                <span class="px-3 py-2 badge bg-danger fs-6"><i class="fas fa-times-circle me-1"></i> Ditolak / Dibatalkan</span>
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
        @endif

        <!-- DAFTAR TEMPAT PKL YANG TERSEDIA -->
        @if(!$myInternship || in_array($myInternship->status, ['completed', 'cancelled']))
            <div class="border-0 shadow-sm card">
                <div class="py-3 bg-white border-bottom card-header">
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
                        <div class="alert alert-info small">
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
