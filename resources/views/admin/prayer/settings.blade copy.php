@section('title', 'Pengaturan Lokasi Masjid')

<x-app-layout>
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

                            <div class="mb-4">
                                <label class="form-label fw-bold">Peta Lokasi (Geser Marker untuk Mengubah)</label>
                                <div id="map" style="height: 400px; border-radius: 10px; border: 2px solid #ddd;"></div>
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

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Init Data dari Controller
        let curLat = {{ $lat }};
        let curLng = {{ $lng }};
        let curRadius = {{ $radius }};

        // Init Map
        const map = L.map('map').setView([curLat, curLng], 18);

        // Tile Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Marker (Draggable)
        const marker = L.marker([curLat, curLng], {
            draggable: true
        }).addTo(map)
        .bindPopup("<b>Lokasi Masjid</b><br>Geser marker ini ke posisi masjid sekolah.")
        .openPopup();

        // Circle (Radius)
        const circle = L.circle([curLat, curLng], {
            color: 'green',
            fillColor: '#2ecc71',
            fillOpacity: 0.2,
            radius: curRadius
        }).addTo(map);

        // Event: Saat marker digeser
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();

            // Update Form Inputs
            document.getElementById('lat').value = position.lat;
            document.getElementById('lng').value = position.lng;

            // Pindahkan Circle mengikuti Marker
            circle.setLatLng(position);

            // Center Map
            map.panTo(position);
        });

        // Event: Saat Radius diubah manual
        function updateRadius(val) {
            circle.setRadius(val);
        }

        // Event: Klik Peta untuk memindahkan marker
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);

            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
        });

    </script>
    @endpush

</x-app-layout>
