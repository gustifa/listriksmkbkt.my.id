@section('title', 'Pengaturan Lokasi Masjid')

<x-app-layout>
    <!-- LOAD CSS LANGSUNG DI BODY (Solusi Peta Pecah) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <style>
        /* Fix Z-Index agar peta tidak menutupi dropdown/navbar */
        /* Ubah ke 1 agar bisa menerima klik/scroll, namun tetap di bawah modal (biasanya 1050+) */
        #map {
            z-index: 1;
        }
        /* Pastikan container peta memiliki relative positioning */
        .map-wrapper {
            position: relative;
            z-index: 1;
        }
    </style>

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="shadow-lg card">
                    <div class="text-white card-header bg-primary">
                        <h4 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Pengaturan Lokasi Absensi Sholat</h4>
                    </div>
                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.prayer.settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Latitude (Garis Lintang)</label>
                                    <input type="text" id="lat" name="masjid_lat" class="form-control" value="{{ $lat }}" readonly required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Longitude (Garis Bujur)</label>
                                    <input type="text" id="lng" name="masjid_lng" class="form-control" value="{{ $lng }}" readonly required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Radius Absensi (Meter)</label>
                                <input type="number" id="radiusInput" name="masjid_radius" class="form-control" value="{{ $radius }}" min="10" max="1000" oninput="updateRadius(this.value)">
                                <small class="text-muted">Jarak maksimal siswa dari titik masjid yang diperbolehkan untuk absen.</small>
                            </div>

                            <div class="mb-4 map-wrapper">
                                <label class="form-label fw-bold">Peta Lokasi</label>

                                <!-- FITUR PENCARIAN TEMPAT -->
                                <div class="mb-2 input-group">
                                    <input type="text" id="searchPlace" class="form-control" placeholder="Cari nama tempat / masjid (contoh: Masjid Raya Bukittinggi)..." onkeypress="handleEnter(event)">
                                    <button class="btn btn-outline-primary" type="button" onclick="searchLocation()">
                                        <i class="fas fa-search"></i> Cari Lokasi
                                    </button>
                                </div>

                                <div id="map" style="height: 400px; border-radius: 10px; border: 2px solid #ddd;"></div>
                                <small class="mt-1 text-muted fst-italic d-block">
                                    <i class="fas fa-info-circle"></i> Geser marker merah untuk menyesuaikan titik presisi. Gunakan tombol +/- atau scroll mouse untuk zoom.
                                </small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Simpan Pengaturan Lokasi
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Variabel Global agar bisa diakses fungsi search
        let map, marker, circle;

        document.addEventListener("DOMContentLoaded", function() {
            // Init Data dari Controller
            let curLat = {{ $lat }};
            let curLng = {{ $lng }};
            let curRadius = {{ $radius }};

            // Init Map dengan Timeout kecil untuk memastikan container sudah siap
            setTimeout(() => {
                // Cek agar tidak init double
                if (L.DomUtil.get('map') && L.DomUtil.get('map')._leaflet_id) {
                     L.DomUtil.get('map')._leaflet_id = null;
                }

                // Inisialisasi Peta dengan Opsi Zoom Aktif
                map = L.map('map', {
                    center: [curLat, curLng],
                    zoom: 18,
                    scrollWheelZoom: true, // Pastikan scroll mouse aktif
                    zoomControl: true,      // Tampilkan tombol +/-
                    dragging: true,         // Izinkan geser peta
                    touchZoom: true,        // Support layar sentuh
                    doubleClickZoom: true
                });

                // Tile Layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap',
                    maxZoom: 19 // Set max zoom level
                }).addTo(map);

                // FIX: Paksa refresh ukuran map agar tile muncul sempurna
                setTimeout(() => { map.invalidateSize(); }, 100);

                // Marker (Draggable)
                marker = L.marker([curLat, curLng], {
                    draggable: true,
                    autoPan: true // Geser peta otomatis jika marker di pinggir
                }).addTo(map)
                .bindPopup("<b>Lokasi Masjid</b><br>Geser marker ini ke posisi masjid sekolah.")
                .openPopup();

                // Circle (Radius)
                circle = L.circle([curLat, curLng], {
                    color: 'green',
                    fillColor: '#2ecc71',
                    fillOpacity: 0.2,
                    radius: curRadius
                }).addTo(map);

                // Event: Saat marker digeser
                marker.on('dragend', function (e) {
                    const position = marker.getLatLng();
                    updateFormInputs(position.lat, position.lng);
                });

                // Event: Klik Peta untuk memindahkan marker
                map.on('click', function(e) {
                    updateMarkerPosition(e.latlng.lat, e.latlng.lng);
                });

                // Expose updateRadius to global scope
                window.updateRadius = function(val) {
                    if(circle) circle.setRadius(val);
                }
            }, 500); // Delay 500ms
        });

        // Fungsi Update Posisi Marker & Form
        function updateMarkerPosition(lat, lng) {
            const newLatLng = new L.LatLng(lat, lng);
            marker.setLatLng(newLatLng);
            circle.setLatLng(newLatLng);
            map.panTo(newLatLng);
            updateFormInputs(lat, lng);
        }

        // Fungsi Update Input Text
        function updateFormInputs(lat, lng) {
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
        }

        // Fungsi Cari Lokasi (Nominatim OpenStreetMap)
        function searchLocation() {
            const query = document.getElementById('searchPlace').value;
            if (!query) return;

            const btn = document.querySelector('button[onclick="searchLocation()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari...';
            btn.disabled = true;

            // Tambahkan parameter limit=1 dan format=json
            const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(query)}`;

            fetch(url)
                .then(response => {
                    // Cek status HTTP terlebih dahulu
                    if (!response.ok) {
                        throw new Error(`HTTP Error: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);

                        // Pindahkan marker ke lokasi hasil pencarian
                        updateMarkerPosition(lat, lon);
                    } else {
                        alert('Lokasi tidak ditemukan. Coba kata kunci lain atau nama kota.');
                    }
                })
                .catch(err => {
                    console.error("Search Error:", err);
                    alert('Gagal mengambil data lokasi. Periksa koneksi internet atau coba lagi nanti.');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        // Handle Enter di Search Box
        function handleEnter(e) {
            if(e.key === 'Enter'){
                e.preventDefault(); // Cegah submit form
                searchLocation();
            }
        }
    </script>
    @endpush

</x-app-layout>
