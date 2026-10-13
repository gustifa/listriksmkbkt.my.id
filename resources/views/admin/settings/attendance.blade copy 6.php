@section('title')
   Pengaturan Jam Masuk, Pulang & Geofencing
@endsection
<x-app-layout>
    <!-- CSS Leaflet Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Setting</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Jadwal & Geofencing</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i> Pengaturan Jam Operasional & Titik Lokasi Absensi</h5>
                    </div>
                    <div class="card-body">

                        <form action="{{ route('settings.update.attendance') }}" method="POST">
                            @csrf
                            <div class="card-body p-0">

                                <h6 class="text-primary fw-bold mb-3"><i class="fas fa-clock me-2"></i> Jam Operasional Scan</h6>
                                <div class="row">
                                    <div class="mb-3 col-md-4 form-group">
                                        <label class="form-label fw-bold">Jam Buka Scan Masuk</label>
                                        <input type="time" name="start_check_in_time" class="form-control"
                                            value="{{ $setting->start_check_in_time ?? '06:00' }}" required>
                                        <div class="form-text">Cegah siswa absen terlalu pagi.</div>
                                    </div>

                                    <div class="mb-3 col-md-4 form-group">
                                        <label class="form-label fw-bold">Batas Jam Masuk (Batas Terlambat)</label>
                                        <input type="time" name="late_limit_time" class="form-control"
                                            value="{{ $setting->late_limit_time ?? '07:00' }}" required>
                                        <div class="form-text">Lewat dari jam ini dianggap Terlambat.</div>
                                    </div>

                                    <div class="mb-3 col-md-4 form-group">
                                        <label class="form-label fw-bold">Batas Awal Jam Pulang</label>
                                        <input type="time" name="early_departure_time" class="form-control"
                                            value="{{ $setting->early_departure_time ?? '10:00' }}" required>
                                        <div class="form-text">Sebelum jam ini siswa tidak bisa scan pulang.</div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- PENGATURAN GEOFENCING / PETA -->
                                <h6 class="text-primary fw-bold mb-3"><i class="fas fa-map-marked-alt me-2"></i> Lokasi Absensi (Geofencing)</h6>

                                <!-- SEARCH BOX -->
                                <div class="mb-3 input-group">
                                    <input type="text" id="map_search_input" class="form-control form-control-lg" placeholder="Cari lokasi sekolah/masjid...">
                                    <button class="btn btn-outline-primary" type="button" onclick="searchLocation()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>

                                <!-- CONTAINER MAP -->
                                <div id="map" style="height: 380px; width: 100%; border-radius: 12px; border: 1px solid #ddd;" class="mb-3"></div>

                                <div class="row">
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label fw-bold">Latitude</label>
                                        <input type="text" id="latitude_input" name="latitude" class="form-control"
                                            value="{{ $setting->latitude ?? '-0.30512300' }}" required readonly>
                                    </div>

                                    <div class="mb-3 col-md-4">
                                        <label class="form-label fw-bold">Longitude</label>
                                        <input type="text" id="longitude_input" name="longitude" class="form-control"
                                            value="{{ $setting->longitude ?? '100.36912300' }}" required readonly>
                                    </div>

                                    <div class="mb-3 col-md-4">
                                        <label class="form-label fw-bold">Radius Toleransi (Meter)</label>
                                        <div class="input-group">
                                            <input type="number" id="radius_input" name="radius_meters" class="form-control"
                                                value="{{ $setting->radius_meters ?? '100' }}" min="10" required>
                                            <span class="input-group-text">Meter</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="getCurrentLocation()">
                                        <i class="fas fa-crosshairs me-1"></i> Deteksi GPS HP/Laptop Saya
                                    </button>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bx bx-save me-2"></i> Simpan Pengaturan
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT DEPENDENCIES -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Mohon periksa kembali inputan Anda.',
            });
        @endif

        // LEAFLET MAP LOGIC
        let map, marker, circle;

        document.addEventListener("DOMContentLoaded", function () {
            const defaultLat = parseFloat(document.getElementById('latitude_input').value) || -0.30512300;
            const defaultLng = parseFloat(document.getElementById('longitude_input').value) || 100.36912300;
            const defaultRadius = parseInt(document.getElementById('radius_input').value) || 100;

            // Inisialisasi Peta
            map = L.map('map').setView([defaultLat, defaultLng], 17);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; Leaflet | OpenStreetMap'
            }).addTo(map);

            // Marker Lokasi
            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            // Lingkaran Radius Geofence
            circle = L.circle([defaultLat, defaultLng], {
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.2,
                radius: defaultRadius
            }).addTo(map);

            // Event Marker Dragged
            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            // Event Klik di Map
            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });

            // Event Ubah Input Radius
            document.getElementById('radius_input').addEventListener('input', function () {
                const newRadius = parseInt(this.value) || 10;
                circle.setRadius(newRadius);
            });
        });

        function updateCoordinates(lat, lng) {
            document.getElementById('latitude_input').value = lat.toFixed(8);
            document.getElementById('longitude_input').value = lng.toFixed(8);
            const latLng = new L.LatLng(lat, lng);
            marker.setLatLng(latLng);
            circle.setLatLng(latLng);
            map.panTo(latLng);
        }

        // Cari Lokasi
        function searchLocation() {
            const query = document.getElementById('map_search_input').value;
            if (!query) return;

            Swal.fire({
                title: 'Mencari Lokasi...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        updateCoordinates(lat, lon);
                        map.setView([lat, lon], 17);
                    } else {
                        Swal.fire('Tidak Ditemukan', 'Lokasi tidak dapat ditemukan.', 'warning');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Gagal terhubung ke layanan peta.', 'error');
                });
        }

        // Ambil GPS Device
        function getCurrentLocation() {
            if (navigator.geolocation) {
                Swal.fire({
                    title: 'Mendapatkan Lokasi...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        Swal.close();
                        updateCoordinates(position.coords.latitude, position.coords.longitude);
                        map.setView([position.coords.latitude, position.coords.longitude], 18);
                    },
                    function (error) {
                        Swal.fire('Gagal!', 'Tidak dapat mengambil GPS: ' + error.message, 'error');
                    },
                    { enableHighAccuracy: true }
                );
            } else {
                Swal.fire('Error!', 'Browser tidak mendukung Geolocation.', 'error');
            }
        }
    </script>
</x-app-layout>
