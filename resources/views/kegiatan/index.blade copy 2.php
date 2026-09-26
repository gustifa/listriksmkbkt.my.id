@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold text-primary"><i class="fas fa-calendar-check me-2"></i>Daftar Kegiatan</h2>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKegiatan">
                <i class="fas fa-plus me-1"></i> Tambah Kegiatan
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-secondary text-uppercase small">
                        <tr>
                            <th class="py-3">No</th>
                            <th>Nama Kegiatan</th>
                            <th>Tanggal</th>
                            <th>Kode QR</th>
                            <th class="text-center">Hadir</th>
                            <th>Lokasi Dikunci</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kegiatans as $index => $kegiatan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $kegiatan->nama_kegiatan }}</td>
                                <td>{{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d-m-Y') }}</td>
                                <td><span class="badge bg-info text-dark">{{ $kegiatan->kode_unik }}</span></td>
                                <td class="text-center">
                                    <span class="badge bg-primary total-hadir" data-id="{{ $kegiatan->id }}">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </td>
                                <td>
                                    @if($kegiatan->latitude && $kegiatan->longitude)
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="fas fa-lock me-1"></i> Aktif ({{ $kegiatan->radius }}m)
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border text-uppercase" style="font-size: 0.7rem;">Bebas</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group shadow-sm">
                                        <a href="{{route('kegiatan.show', $kegiatan->id)}}" class="btn btn-sm btn-outline-success">Lihat</a>
                                        <a href="{{route('kegiatan.scan', $kegiatan->kode_unik)}}" class="btn btn-sm btn-outline-info">Scan</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data kegiatan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahKegiatan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-map-marked-alt me-2"></i>Tambah Kegiatan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="{{ route('kegiatan.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nama Kegiatan</label>
                            <input type="text" class="form-control" name="nama_kegiatan" required placeholder="Contoh: Praktek Instalasi Motor Listrik">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="fas fa-lock me-1 text-danger"></i> Pengaturan Kunci Lokasi (Geofencing)</h6>
                            <div class="row g-2 p-3 bg-light rounded-3 border">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Latitude</label>
                                    <input type="text" class="form-control" name="latitude" id="lat_input" placeholder="Otomatis..." readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Longitude</label>
                                    <input type="text" class="form-control" name="longitude" id="lng_input" placeholder="Otomatis..." readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Radius (Meter)</label>
                                    <input type="number" class="form-control" name="radius" value="50" min="10">
                                </div>
                                <div class="col-12 mt-3">
                                    <button type="button" onclick="getCurrentLocation()" class="btn btn-sm btn-dark w-100 py-2">
                                        <i class="fas fa-crosshairs me-2"></i> Set Lokasi ke Posisi Saya Saat Ini
                                    </button>
                                    <small class="text-muted d-block mt-1 text-center italic">*Kosongkan jika ingin kegiatan bisa diabsen dari mana saja.</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="2" placeholder="Detail kegiatan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // FUNGSI AMBIL LOKASI UNTUK MODAL
    function getCurrentLocation() {
        if (navigator.geolocation) {
            Swal.fire({
                title: 'Sedang melacak posisi...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });

            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('lat_input').value = position.coords.latitude;
                document.getElementById('lng_input').value = position.coords.longitude;
                Swal.fire({
                    icon: 'success',
                    title: 'Lokasi Terkunci!',
                    text: 'Koordinat berhasil diambil.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }, function(error) {
                Swal.fire('Gagal!', 'Pastikan GPS aktif dan izin lokasi diberikan.', 'error');
            });
        }
    }

    // FUNGSI REFRESH TOTAL HADIR (AJAX)
    function refreshTotalHadir() {
        let ids = [];
        $('.total-hadir').each(function() {
            ids.push($(this).data('id'));
        });

        if (ids.length > 0) {
            $.ajax({
                url: "{{ url('/api/kegiatan/total-hadir') }}",
                type: "GET",
                data: { ids: ids },
                success: function(response) {
                    $('.total-hadir').each(function() {
                        let id = $(this).data('id');
                        let total = response[id] ? response[id] : 0;
                        $(this).html('<i class="fas fa-users me-1"></i> ' + total);
                    });
                }
            });
        }
    }

    $(document).ready(function() {
        refreshTotalHadir();
        setInterval(refreshTotalHadir, 5000);
    });
</script>
@endsection