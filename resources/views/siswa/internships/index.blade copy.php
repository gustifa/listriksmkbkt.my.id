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
                        <div class="col-md-8">
                            <h5 class="fw-bold text-dark">{{ $myInternship->industry->name }}</h5>
                            <p class="mb-1 text-muted"><i class="fas fa-map-marker-alt me-2"></i>{{ $myInternship->industry->address }}</p>
                            <p class="mb-0 text-muted"><i class="fas fa-user-tie me-2"></i>Pembimbing: {{ $myInternship->advisor->name ?? 'Belum ditentukan' }}</p>
                        </div>
                        <div class="text-center col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="mb-2">Status Pengajuan:</div>
                            @if($myInternship->status == 'pending')
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fas fa-clock me-1"></i> Menunggu Persetujuan</span>
                            @elseif($myInternship->status == 'active')
                                <span class="badge bg-success fs-6 px-3 py-2"><i class="fas fa-check-circle me-1"></i> Disetujui / Aktif</span>
                            @elseif($myInternship->status == 'completed')
                                <span class="badge bg-info text-dark fs-6 px-3 py-2"><i class="fas fa-flag-checkered me-1"></i> Selesai</span>
                            @elseif($myInternship->status == 'cancelled')
                                <span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-times-circle me-1"></i> Ditolak / Dibatalkan</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- DAFTAR TEMPAT PKL YANG TERSEDIA -->
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
</x-app-layout>