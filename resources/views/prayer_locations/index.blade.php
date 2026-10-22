@extends('layouts.app')

@section('title', 'Pengaturan Lokasi Masjid Sekolah')

@section('content')
<style>
    #admin-map-canvas {
        height: 380px !important;
        width: 100% !important;
        border-radius: 12px;
        border: 2px solid #dee2e6;
        z-index: 1;
    }
</style>

<div class="container-fluid py-4 px-2 px-md-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Pengaturan Lokasi Masjid & Mode Absensi</h3>
            <p class="text-muted small mb-0">Atur titik koordinat GPS, toleransi radius, serta kebijakan pelaksanaan sholat siswa.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- FORM PENGATURAN -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-sliders-h text-primary me-2"></i>Parameter Titik & Kebijakan
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form id="form-prayer-location" action="{{ route('prayer-location.store') }}" method="POST">
                        @csrf

                        <!-- Pilihan Kebijakan Mode Absensi Global (is_active) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small">Mode Kebijakan Absensi Sholat (Global)</label>
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="is_active" id="mode_masjid" value="1" {{ old('is_active', $location->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark small" for="mode_masjid">
                                        <i class="fas fa-mosque text-primary me-1"></i> Wajib di Masjid Sekolah (is_active = TRUE)
                                    </label>
                                    <small class="text-muted d-block ms-4">Siswa wajib berada dalam radius GPS masjid sekolah untuk melakukan absensi.</small>
                                </div>

                                <hr class="my-2">

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_active" id="mode_rumah" value="0" {{ !old('is_active', $location->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark small" for="mode_rumah">
                                        <i class="fas fa-home text-success me-1"></i> Diizinkan Sholat di Rumah / PJJ (is_active = FALSE)
                                    </label>
                                    <small class="text-muted d-block ms-4">Validasi radius GPS dinonaktifkan, siswa diperbolehkan absen dari luar sekolah.</small>
                                </div>
                            </div>
                        </div>

                        <!-- FITUR BARU: PENGATURAN LOKASI PER WAKTU SHOLAT -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small">Kebijakan Lokasi per Waktu Sholat</label>
                            <div class="p-3 border rounded-3 bg-light">
                                <p class="text-muted small mb-2">Centang waktu sholat yang <strong>WAJIB di Masjid Sekolah</strong>. Jika tidak dicentang, siswa diizinkan sholat di Rumah/PJJ.</p>
                                
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_must_at_masjid_subuh" id="subuh" value="1" {{ old('is_must_at_masjid_subuh', $location->is_must_at_masjid_subuh ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="subuh">Subuh</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_must_at_masjid_zuhur" id="zuhur" value="1" {{ old('is_must_at_masjid_zuhur', $location->is_must_at_masjid_zuhur ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-primary small" for="zuhur">Zuhur (Masjid Sekolah)</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_must_at_masjid_ashar" id="ashar" value="1" {{ old('is_must_at_masjid_ashar', $location->is_must_at_masjid_ashar ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="ashar">Ashar</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_must_at_masjid_maghrib" id="maghrib" value="1" {{ old('is_must_at_masjid_maghrib', $location->is_must_at_masjid_maghrib ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="maghrib">Maghrib</label>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_must_at_masjid_isya" id="isya" value="1" {{ old('is_must_at_masjid_isya', $location->is_must_at_masjid_isya ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="isya">Isya</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Nama Masjid / Musholla</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $location->name ?? 'Masjid Sekolah') }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary small">Latitude</label>
                                <input type="text" name="latitude" id="lat-input" class="form-control font-monospace" value="{{ old('latitude', $location->latitude ?? -0.312221) }}" required readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary small">Longitude</label>
                                <input type="text" name="longitude" id="lng-input" class="form-control font-monospace" value="{{ old('longitude', $location->longitude ?? 100.383403) }}" required readonly>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small">Toleransi Radius Max (Meter)</label>
                            <div class="input-group">
                                <input type="number" name="radius_meters" id="radius-input" class="form-control fw-bold" value="{{ old('radius_meters', $location->radius_meters ?? 10) }}" min="1" max="10000" required>
                                <span class="input-group-text bg-light fw-semibold">Meter</span>
                            </div>
                            <small class="text-muted d-block mt-1">Gunakan angka terukur (contoh: 10 atau 15 meter).</small>
                        </div>

                        <button type="button" onclick="getCurrentAdminLocation()" class="btn btn-outline-info btn-sm w-100 mb-3 fw-semibold">
                            <i class="fas fa-crosshairs me-1"></i> Ambil Koordinat Perangkat Ini
                        </button>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                            <i class="fas fa-save me-1"></i> Simpan Pengaturan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PETA INTERAKTIF -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-map-marked-alt text-success me-2"></i>Prinjau Peta Lokasi
                    </h6>
                    <small class="text-muted">Klik/geser marker pada peta</small>
                </div>
                <div class="card-body p-3">
                    <div id="admin-map-canvas"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LEAFLET & SWEETALERT2 TERBARU -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let adminMap = null;
    let mosqueMarker = null;
    let radiusCircle = null;

    document.addEventListener('DOMContentLoaded', function() {
        initAdminMap();

        document.getElementById('radius-input').addEventListener('input', function() {
            updateCircleRadius(Number(this.value));
        });

        // SWEETALERT2 NOTIFIKASI DARI SESSION LARAVEL (CENTER DISPLAY)
        @if(session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                position: 'center',
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0d6efd',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: 'Gagal!',
                text: "{{ session('error') }}",
                icon: 'error',
                position: 'center',
                showConfirmButton: true,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#dc3545'
            });
        @endif

        @if($errors->any())
            Swal.fire({
                title: 'Terjadi Kesalahan!',
                text: "{{ $errors->first() }}",
                icon: 'warning',
                position: 'center',
                showConfirmButton: true,
                confirmButtonColor: '#ffc107'
            });
        @endif
    });

    function initAdminMap() {
        const defaultLat = Number(document.getElementById('lat-input').value) || -0.312221;
        const defaultLng = Number(document.getElementById('lng-input').value) || 100.383403;
        const defaultRadius = Number(document.getElementById('radius-input').value) || 10;

        adminMap = L.map('admin-map-canvas', { zoomControl: true }).setView([defaultLat, defaultLng], 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(adminMap);

        mosqueMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(adminMap);

        radiusCircle = L.circle([defaultLat, defaultLng], {
            color: '#198754',
            fillColor: '#198754',
            fillOpacity: 0.2,
            radius: defaultRadius
        }).addTo(adminMap);

        mosqueMarker.on('dragend', function(e) {
            const position = mosqueMarker.getLatLng();
            updateInputsAndCircle(position.lat, position.lng);
        });

        adminMap.on('click', function(e) {
            mosqueMarker.setLatLng(e.latlng);
            updateInputsAndCircle(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateInputsAndCircle(lat, lng) {
        document.getElementById('lat-input').value = lat.toFixed(6);
        document.getElementById('lng-input').value = lng.toFixed(6);
        radiusCircle.setLatLng([lat, lng]);
    }

    function updateCircleRadius(radiusVal) {
        if (radiusCircle && radiusVal > 0) {
            radiusCircle.setRadius(radiusVal);
        }
    }

    function getCurrentAdminLocation() {
        if (navigator.geolocation) {
            Swal.fire({
                title: 'Mendeteksi GPS...',
                text: 'Sedang mengambil lokasi presisi perangkat Anda',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;

                mosqueMarker.setLatLng([lat, lng]);
                adminMap.setView([lat, lng], 18);
                updateInputsAndCircle(lat, lng);

                Swal.fire({
                    icon: 'success',
                    title: 'GPS Terkunci',
                    text: 'Koordinat lokasi perangkat berhasil diperbarui!',
                    position: 'center',
                    timer: 1500,
                    showConfirmButton: false
                });
            }, function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal GPS',
                    text: 'Tidak dapat mengakses lokasi GPS perangkat.',
                    position: 'center'
                });
            });
        }
    }
</script>
@endsection