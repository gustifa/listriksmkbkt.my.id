@section('title', 'Lokasi Tempat PKL Bimbingan')

<x-app-layout>
    <!-- CSS Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 350px; width: 100%; border-radius: 8px; border: 2px solid #ddd; z-index: 1; }
        .map-container { position: relative; }
    </style>

    <div class="page-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-primary"><i class="fas fa-map-marked-alt me-2"></i>Lokasi Tempat PKL Bimbingan</h4>
                <p class="text-muted mb-0">Atur titik koordinat absensi untuk siswa bimbingan Anda.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" width="5%">No</th>
                                <th width="25%">Nama DU/DI</th>
                                <th width="30%">Alamat</th>
                                <th class="text-center" width="15%">Status Lokasi</th>
                                <th class="text-center" width="10%">Radius</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($industries as $index => $item)
                            <tr>
                                <td class="ps-4">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->name }}</div>
                                    <small class="text-muted">{{ $item->sector ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <i class="fas fa-map-pin me-1"></i> {{ \Illuminate\Support\Str::limit($item->address, 50) }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($item->latitude && $item->longitude)
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i> Ter-set</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-times me-1"></i> Belum</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">{{ $item->radius }} m</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary fw-bold shadow-sm" onclick="setLocation({{ json_encode($item) }})">
                                        <i class="fas fa-map-marker-alt me-1"></i> Set Lokasi
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3"></i><br>
                                    Anda belum memiliki siswa bimbingan yang aktif di tempat PKL.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL SET LOKASI -->
    <div class="modal fade" id="locationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="" method="POST" id="formLocation">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-map-location-dot me-2"></i> Atur Titik Absensi</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis small mb-3">
                            <i class="fas fa-info-circle me-1"></i> Geser penanda merah (marker) di peta ke lokasi tepat pintu masuk/kantor DU/DI.
                        </div>

                        <!-- Info Industri -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Industri</label>
                            <input type="text" id="view_name" class="form-control bg-light" readonly>
                        </div>

                        <!-- Peta -->
                        <div class="mb-3 map-container">
                            <label class="form-label fw-bold">Peta Lokasi</label>
                            
                            <!-- Search Map -->
                            <div class="input-group mb-2">
                                <input type="text" id="searchMapInput" class="form-control" placeholder="Cari nama tempat / jalan...">
                                
                                <!-- Tombol Lokasi Saya (Fitur Baru) -->
                                <button class="btn btn-outline-success" type="button" onclick="getCurrentLocation()" title="Gunakan Lokasi Saya Saat Ini">
                                    <i class="fas fa-location-crosshairs"></i> Lokasi Saya
                                </button>
                                
                                <button type="button" class="btn btn-outline-primary" onclick="searchLocation()">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                            </div>

                            <div id="map"></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Latitude</label>
                                <input type="text" name="latitude" id="inp_lat" class="form-control bg-light" readonly required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Longitude</label>
                                <input type="text" name="longitude" id="inp_lng" class="form-control bg-light" readonly required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Radius Toleransi (Meter)</label>
                                <input type="number" name="radius" id="inp_radius" class="form-control" value="100" min="10" max="500" oninput="updateRadiusCircle(this.value)">
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold">Simpan Koordinat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map, marker, circle;
        const defaultLat = -0.305123; // Default Bukittinggi
        const defaultLng = 100.369456;

        function setLocation(data) {
            // Set Form URL
            const url = "{{ route('teacher.industries.update_location', ':id') }}";
            document.getElementById('formLocation').action = url.replace(':id', data.id);

            // Set Data Tampilan
            document.getElementById('view_name').value = data.name;
            document.getElementById('inp_radius').value = data.radius || 100;

            // Buka Modal
            const myModal = new bootstrap.Modal(document.getElementById('locationModal'));
            myModal.show();

            // Init Map setelah modal terbuka (agar ukuran render benar)
            setTimeout(() => {
                initMap(data);
            }, 500);
        }

        function initMap(data) {
            const startLat = data.latitude || defaultLat;
            const startLng = data.longitude || defaultLng;
            const startZoom = data.latitude ? 17 : 13;

            if (!map) {
                map = L.map('map').setView([startLat, startLng], startZoom);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                // Event Klik Peta
                map.on('click', function(e) {
                    moveMarker(e.latlng.lat, e.latlng.lng);
                });
            } else {
                map.setView([startLat, startLng], startZoom);
                map.invalidateSize(); // Fix grey tiles
            }

            // Reset Layer Lama
            if (marker) map.removeLayer(marker);
            if (circle) map.removeLayer(circle);

            // Buat Marker & Circle Baru
            marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);
            circle = L.circle([startLat, startLng], { 
                radius: data.radius || 100, 
                color: 'green', 
                fillOpacity: 0.2 
            }).addTo(map);

            // Update Input
            document.getElementById('inp_lat').value = startLat;
            document.getElementById('inp_lng').value = startLng;

            // Event Drag Marker
            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                moveMarker(pos.lat, pos.lng);
            });
        }

        function moveMarker(lat, lng) {
            const newPos = new L.LatLng(lat, lng);
            marker.setLatLng(newPos);
            circle.setLatLng(newPos);
            // map.panTo(newPos);
            
            document.getElementById('inp_lat').value = lat;
            document.getElementById('inp_lng').value = lng;
        }

        function updateRadiusCircle(val) {
            if (circle) circle.setRadius(val);
        }

        // --- FITUR BARU: Get Current Location ---
        function getCurrentLocation() {
            if (navigator.geolocation) {
                // Tampilkan loading di tombol
                const btn = document.querySelector('button[onclick="getCurrentLocation()"]');
                const originalIcon = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        // Pindahkan marker dan peta
                        moveMarker(lat, lng);
                        map.setView([lat, lng], 18);
                        
                        // Kembalikan tombol
                        btn.innerHTML = originalIcon;
                        btn.disabled = false;
                    },
                    (error) => {
                        alert('Gagal mendeteksi lokasi: ' + error.message);
                        btn.innerHTML = originalIcon;
                        btn.disabled = false;
                    },
                    { enableHighAccuracy: true }
                );
            } else {
                alert("Browser ini tidak mendukung Geolocation.");
            }
        }

        function searchLocation() {
            const query = document.getElementById('searchMapInput').value;
            if (!query) return;

            // Loading state
            const btn = document.querySelector('button[onclick="searchLocation()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        moveMarker(lat, lon);
                        map.setView([lat, lon], 16);
                    } else {
                        alert('Lokasi tidak ditemukan');
                    }
                })
                .catch(err => alert('Gagal mencari lokasi.'))
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    </script>
    @endpush
</x-app-layout>