@section('title', 'Pengaturan Lokasi & Jadwal')

<x-app-layout>
    <!-- LOAD CSS LANGSUNG DI BODY (Solusi Peta Pecah) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <style>
        #map { z-index: 1; }
        .map-wrapper { position: relative; z-index: 1; }
    </style>

    <div class="page-content">
        <div class="row justify-content-center">

            <!-- KOLOM KIRI: LOKASI MASJID -->
            <div class="col-md-7">
                <div class="shadow-lg card h-100">
                    <div class="text-white card-header bg-primary">
                        <h4 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Pengaturan Lokasi Absensi</h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.prayer.settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Latitude</label>
                                    <input type="text" id="lat" name="masjid_lat" class="form-control" value="{{ $lat }}" required oninput="manualInputUpdate()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Longitude</label>
                                    <input type="text" id="lng" name="masjid_lng" class="form-control" value="{{ $lng }}" required oninput="manualInputUpdate()">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Radius (Meter)</label>
                                    <input type="number" id="radiusInput" name="masjid_radius" class="form-control" value="{{ $radius }}" min="5" max="1000" oninput="updateRadius(this.value)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ID Kota (MyQuran)</label>
                                    <input type="text" name="prayer_city_id" class="form-control" value="{{ $cityId ?? '0306' }}" placeholder="Contoh: 0306">
                                    <small><a href="https://api.myquran.com/v2/sholat/kota/semua" target="_blank">Cek ID Kota Disini</a></small>
                                </div>
                            </div>

                            <div class="mb-4 map-wrapper">
                                <label class="form-label fw-bold">Peta Lokasi</label>
                                <div class="mb-2 input-group">
                                    <input type="text" id="searchPlace" class="form-control" placeholder="Cari lokasi..." onkeypress="handleEnter(event)">
                                    <button class="btn btn-outline-primary" type="button" onclick="searchLocation()">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                                <div id="map" style="height: 350px; border-radius: 10px; border: 2px solid #ddd;"></div>
                                <small class="mt-1 text-muted fst-italic d-block">
                                    <i class="fas fa-info-circle"></i> Geser marker merah atau isi Latitude/Longitude manual untuk menyesuaikan titik presisi.
                                </small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: SINKRONISASI JADWAL -->
            <div class="col-md-5">
                <div class="shadow-lg card">
                    <div class="text-white card-header bg-success">
                        <h4 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Sinkronisasi Jadwal Sholat</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Fitur ini akan mengambil data jadwal sholat dari API MyQuran.com dan menyimpannya ke database lokal. Ini mempercepat loading aplikasi siswa dan mencegah error jika API sedang down.
                        </p>

                        <form action="{{ route('admin.prayer.sync') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Bulan</label>
                                <select name="month" class="form-select">
                                    @foreach($months as $k => $v)
                                        <option value="{{ $k }}" {{ date('n') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tahun</label>
                                <select name="year" class="form-select">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Tarik Data Jadwal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- INFO STATUS -->
                <div class="mt-3 shadow-sm card">
                    <div class="card-body">
                        <h6 class="fw-bold">Status Data Hari Ini ({{ date('d-m-Y') }})</h6>
                        @php
                            $todaySchedule = \App\Models\PrayerSchedule::where('date', date('Y-m-d'))->first();
                        @endphp

                        @if($todaySchedule)
                            <div class="py-2 mb-0 alert alert-success">
                                <i class="fas fa-check-circle me-1"></i> Data Tersedia
                            </div>
                            <ul class="mt-2 list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between"><span>Subuh</span> <strong>{{ $todaySchedule->subuh }}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Dzuhur</span> <strong>{{ $todaySchedule->dzuhur }}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Ashar</span> <strong>{{ $todaySchedule->ashar }}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Maghrib</span> <strong>{{ $todaySchedule->maghrib }}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Isya</span> <strong>{{ $todaySchedule->isya }}</strong></li>
                            </ul>
                        @else
                            <div class="py-2 mb-0 alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i> Data Kosong. Silakan sinkronisasi.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Variabel Global
        let map, marker, circle;

        document.addEventListener("DOMContentLoaded", function() {
            let curLat = {{ $lat }};
            let curLng = {{ $lng }};
            let curRadius = {{ $radius }};

            setTimeout(() => {
                if (L.DomUtil.get('map') && L.DomUtil.get('map')._leaflet_id) {
                     L.DomUtil.get('map')._leaflet_id = null;
                }

                map = L.map('map', {
                    center: [curLat, curLng],
                    zoom: 18,
                    scrollWheelZoom: true,
                    zoomControl: true,
                    dragging: true,
                    touchZoom: true,
                    doubleClickZoom: true
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap',
                    maxZoom: 19
                }).addTo(map);

                setTimeout(() => { map.invalidateSize(); }, 100);

                marker = L.marker([curLat, curLng], { draggable: true, autoPan: true }).addTo(map);
                circle = L.circle([curLat, curLng], { color: 'green', fillColor: '#2ecc71', fillOpacity: 0.2, radius: curRadius }).addTo(map);

                marker.on('dragend', function (e) {
                    const position = marker.getLatLng();
                    updateFormInputs(position.lat, position.lng);
                });

                map.on('click', function(e) {
                    updateMarkerPosition(e.latlng.lat, e.latlng.lng);
                });

                window.updateRadius = function(val) { if(circle) circle.setRadius(val); }

                window.manualInputUpdate = function() {
                    const lat = parseFloat(document.getElementById('lat').value);
                    const lng = parseFloat(document.getElementById('lng').value);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const newLatLng = new L.LatLng(lat, lng);
                        marker.setLatLng(newLatLng);
                        circle.setLatLng(newLatLng);
                        map.panTo(newLatLng);
                    }
                }
            }, 500);
        });

        function updateMarkerPosition(lat, lng) {
            const newLatLng = new L.LatLng(lat, lng);
            marker.setLatLng(newLatLng);
            circle.setLatLng(newLatLng);
            map.panTo(newLatLng);
            updateFormInputs(lat, lng);
        }

        function updateFormInputs(lat, lng) {
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
        }

        function searchLocation() {
            const query = document.getElementById('searchPlace').value;
            if (!query) return;

            const btn = document.querySelector('button[onclick="searchLocation()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(query)}`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        updateMarkerPosition(parseFloat(data[0].lat), parseFloat(data[0].lon));
                    } else {
                        alert('Lokasi tidak ditemukan.');
                    }
                })
                .catch(e => alert('Gagal mencari lokasi.'))
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        function handleEnter(e) {
            if(e.key === 'Enter'){ e.preventDefault(); searchLocation(); }
        }
    </script>
    @endpush
</x-app-layout>
