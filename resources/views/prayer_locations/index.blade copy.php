@extends('layouts.app')

@section('title', 'Pengaturan Lokasi Masjid Sekolah')

@section('content')
<div class="container-fluid py-3 px-2 px-md-3">
    <!-- Header Page & Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('student.prayer-attendances.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Absensi
            </a>
            <div class="d-flex align-items-center gap-2">
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-md-3">Kelola Titik Lokasi Masjid</h3>
                <span class="badge bg-primary text-white fw-bold">
                    <i class="fas fa-map-marked-alt me-1"></i> GPS & Radius
                </span>
            </div>
        </div>
        
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
            <button type="button" class="btn btn-primary fw-semibold btn-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modalCreateLocation">
                <i class="fas fa-plus me-1"></i> Tambah Lokasi Masjid
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Daftar Titik Lokasi -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark fs-6 fs-md-5">Daftar Titik Koordinat Masjid Sekolah</h5>
        </div>

        <div class="card-body p-2 p-md-4">
            @forelse($locations as $index =>$loc)
                <div class="border rounded-3 p-3 mb-3 bg-light-subtle shadow-sm">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-primary fs-6">#{{ $index + 1 }}</span>
                            <h6 class="fw-bold text-dark mb-0 fs-6">{{ $loc->name }}</h6>
                            @if($loc->is_active)
                                <span class="badge bg-success text-white fw-bold">
                                    <i class="fas fa-check-circle me-1"></i> Utama / Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary text-white">Non-Aktif</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditLocation{{ $loc->id }}">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-semibold" onclick="deleteLocation('{{ $loc->id }}')">
                                <i class="fas fa-trash me-1"></i> Hapus
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 text-dark small">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Latitude:</small>
                            <span class="fw-bold">{{ $loc->latitude }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Longitude:</small>
                            <span class="fw-bold">{{ $loc->longitude }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Toleransi Radius:</small>
                            <span class="badge bg-info-subtle text-info border border-info-subtle fw-bold">
                                {{ $loc->radius_meters }} Meter
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Peta Digital:</small>
                            <a href="https://maps.google.com/?q={{ $loc->latitude }},{{$loc->longitude }}" target="_blank" class="text-primary fw-bold">
                                Buka Google Maps <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- MODAL EDIT LOKASI -->
                <div class="modal fade" id="modalEditLocation{{ $loc->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white py-3">
                                <h6 class="modal-title fw-bold">
                                    <i class="fas fa-edit me-2"></i> Edit Lokasi Masjid
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('prayer-locations.update', $loc->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body p-3 p-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary">Nama Tempat / Masjid</label>
                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $loc->name }}" required>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label fw-bold small text-secondary">Latitude</label>
                                            <input type="text" name="latitude" class="form-control form-control-sm" value="{{ $loc->latitude }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-bold small text-secondary">Longitude</label>
                                            <input type="text" name="longitude" class="form-control form-control-sm" value="{{ $loc->longitude }}" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary">Toleransi Radius (Meter)</label>
                                        <input type="number" name="radius_meters" class="form-control form-control-sm" value="{{ $loc->radius_meters }}" required>
                                    </div>

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="active{{ $loc->id }}" value="1" {{ $loc->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small text-dark" for="active{{ $loc->id }}">Set Sebagai Lokasi Utama</label>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light py-2">
                                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <p class="mb-3">Belum ada data lokasi masjid sekolah yang dikonfigurasi.</p>
                    <button type="button" class="btn btn-sm btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCreateLocation">
                        <i class="fas fa-plus me-1"></i> Tambah Lokasi Sekarang
                    </button>
                </div>
            @endforelse

            <div class="mt-3">
                {{ $locations->links() }}
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH LOKASI -->
<div class="modal fade" id="modalCreateLocation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-map-marked-alt me-2"></i> Tambah Titik Lokasi Masjid
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('prayer-locations.store') }}" method="POST">
                @csrf
                <div class="modal-body p-3 p-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Nama Tempat / Masjid</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Contoh: Masjid Utama Sekolah" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-secondary">Latitude</label>
                            <input type="text" name="latitude" id="create_lat" class="form-control form-control-sm" placeholder="-0.305xxx" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-secondary">Longitude</label>
                            <input type="text" name="longitude" id="create_lng" class="form-control form-control-sm" placeholder="100.369xxx" required>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-info btn-sm w-100 mb-3 fw-semibold" onclick="getDeviceLocation()">
                        <i class="fas fa-crosshairs me-1"></i> Gunakan Lokasi Perangkat Saat Ini
                    </button>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Toleransi Radius (Meter)</label>
                        <input type="number" name="radius_meters" class="form-control form-control-sm" value="100" required>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="create_is_active" value="1" checked>
                        <label class="form-check-label fw-bold small text-dark" for="create_is_active">Set Sebagai Lokasi Utama</label>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Lokasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteLocationForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

<script>
    function getDeviceLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('create_lat').value = position.coords.latitude;
                document.getElementById('create_lng').value = position.coords.longitude;
                alert('Koordinat GPS berhasil diambil!');
            }, function(error) {
                alert('Gagal mengambil lokasi GPS perangkan.');
            });
        }
    }

    function deleteLocation(id) {
        if (confirm('Yakin ingin menghapus lokasi masjid ini?')) {
            const form = document.getElementById('deleteLocationForm');
            form.action = `/prayer-locations/${id}`;
            form.submit();
        }
    }
</script>
@endsection