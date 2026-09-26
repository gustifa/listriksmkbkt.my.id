@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark"><i class="fas fa-tasks text-primary me-2"></i>Manajemen Kegiatan</h2>
            <p class="text-muted mb-0">Bengkel Teknik Instalasi Tenaga Listrik - SMK N 1 Bukittinggi</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-primary btn-lg rounded-pill shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#modalTambahKegiatan">
                <i class="fas fa-plus-circle me-2"></i>Tambah Kegiatan
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">No</th>
                            <th>Nama Kegiatan</th>
                            <th>Tanggal</th>
                            <th>Kode QR</th>
                            <th class="text-center">Total Hadir</th>
                            <th>Status Lokasi</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kegiatans as $index => $kegiatan)
                            <tr>
                                <td class="ps-4">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $kegiatan->nama_kegiatan }}</div>
                                    <small class="text-muted">{{ Str::limit($kegiatan->deskripsi, 40) ?? 'Tidak ada deskripsi' }}</small>
                                </td>
                                <td><i class="far fa-calendar-alt me-1 text-primary"></i> {{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d/m/Y') }}</td>
                                <td><span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">{{ $kegiatan->kode_unik }}</span></td>
                                <td class="text-center">
                                    <div class="badge bg-primary rounded-pill px-3 py-2 total-hadir" data-id="{{ $kegiatan->id }}">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                </td>
                                <td>
                                    @if($kegiatan->latitude && $kegiatan->longitude)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="fas fa-lock me-1"></i> Terkunci ({{ $kegiatan->radius }}m)
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Bebas</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('kegiatan.show', $kegiatan->id) }}" class="btn btn-sm btn-outline-dark" title="Lihat Barcode & Log">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                        <a href="{{ route('kegiatan.scan', $kegiatan->kode_unik) }}" class="btn btn-sm btn-outline-primary" title="Halaman Scan">
                                            <i class="fas fa-camera"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                                    Belum ada kegiatan yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahKegiatan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-primary text-white p-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Buat Kegiatan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="{{ route('kegiatan.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-muted">NAMA KEGIATAN</label>
                            <input type="text" class="form-control bg-light border-0 py-2" name="nama_kegiatan" required placeholder="Contoh: Praktek Motor Listrik">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">TANGGAL</label>
                            <input type="date" class="form-control bg-light border-0 py-2" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="p-3 bg-light rounded-3 border-dashed border-2">
                                <h6 class="fw-bold mb-3"><i class="fas fa-map-marked-alt text-primary me-2"></i>Pengaturan Lokasi Absensi</h6>
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <input type="text" name="latitude" id="admin_lat" class="form-control form-control-sm bg-white" placeholder="Latitude" readonly>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="longitude" id="admin_lng" class="form-control form-control-sm bg-white" placeholder="Longitude" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="radius" class="form-control form-control-sm" value="50" title="Radius (Meter)">
                                    </div>
                                </div>
                                <button type="button" onclick="getAdminLoc()" id="btn-loc" class="btn btn-dark btn-sm w-100 mt-3 py-2 shadow-sm">
                                    <i class="fas fa-crosshairs me-2"></i> Gunakan Lokasi Saya Sekarang
                                </button>
                                <small class="text-muted mt-1 d-block text-center italic">*Kosongkan jika absen bisa dilakukan di mana saja.</small>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold small text-muted">DESKRIPSI</label>
                            <textarea class="form-control bg-light border-0" name="deskripsi" rows="3" placeholder="Opsional..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5 shadow">Simpan Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // 1. Fungsi Ambil Lokasi Admin (Di dalam Modal)
    function getAdminLoc() {
        const btn = document.getElementById('btn-loc');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mengunci GPS...';
        btn.disabled = true;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    document.getElementById('admin_lat').value = pos.coords.latitude;
                    document.getElementById('admin_lng').value = pos.coords.longitude;
                    btn.className = "btn btn-success btn-sm w-100 mt-3 py-2 shadow-sm";
                    btn.innerHTML = '<i class="fas fa-check-circle me-2"></i> Lokasi Terkunci';
                    btn.disabled = false;
                    alert("Berhasil mengambil titik koordinat!");
                },
                (err) => {
                    alert("Gagal mengambil lokasi. Pastikan GPS aktif.");
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-crosshairs me-2"></i> Gunakan Lokasi Saya Sekarang';
                },
                { enableHighAccuracy: true }
            );
        }
    }

    // 2. Fungsi Update Total Hadir Secara Real-time (AJAX)
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
                        $(this).html('<i class="fas fa-users me-2"></i>' + total);
                    });
                }
            });
        }
    }

    $(document).ready(function() {
        refreshTotalHadir();
        setInterval(refreshTotalHadir, 5000); // Update tiap 5 detik
    });
</script>

<style>
    .border-dashed { border-style: dashed !important; }
    .card-body td { font-size: 0.95rem; }
    .btn-group .btn { transition: all 0.2s; }
    .btn-group .btn:hover { transform: translateY(-2px); }
</style>
@endsection