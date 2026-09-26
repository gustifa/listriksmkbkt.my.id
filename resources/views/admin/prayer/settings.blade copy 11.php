@section('title', 'Pengaturan Lokasi & Sinkronisasi')

<x-app-layout>
    <!-- LOAD CSS LANGSUNG DI BODY -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <style>
        #map { z-index: 1; }
        .map-wrapper { position: relative; z-index: 1; }
        /* Gradients */
        .bg-gradient-primary { background: linear-gradient(45deg, #4e73df, #224abe); }
        .bg-gradient-success { background: linear-gradient(45deg, #1cc88a, #13855c); }
        .bg-gradient-info { background: linear-gradient(45deg, #36b9cc, #258391); }
        .bg-gradient-purple { background: linear-gradient(45deg, #6f42c1, #59359a); }
        .bg-gradient-orange { background: linear-gradient(45deg, #fd7e14, #d66408); }
        .bg-gradient-teal { background: linear-gradient(45deg, #20c997, #1aa179); }
        .bg-gradient-dark { background: linear-gradient(45deg, #343a40, #23272b); }
        .bg-gradient-red { background: linear-gradient(45deg, #e74a3b, #c0392b); }

        /* Custom Buttons */
        .btn-purple { background-color: #6f42c1; color: white; }
        .btn-purple:hover { background-color: #59359a; color: white; }
        
        .btn-orange { background-color: #fd7e14; color: white; }
        .btn-orange:hover { background-color: #d66408; color: white; }
        
        .btn-teal { background-color: #20c997; color: white; }
        .btn-teal:hover { background-color: #1aa179; color: white; }
        
        .btn-indigo { background-color: #6610f2; color: white; }
        .btn-indigo:hover { background-color: #520dc2; color: white; }
        
        .btn-dark-custom { background-color: #343a40; color: white; }
        .btn-dark-custom:hover { background-color: #23272b; color: white; }

        .btn-pink { background-color: #e83e8c; color: white; }
        .btn-pink:hover { background-color: #d63384; color: white; }

        .btn-red-custom { background-color: #e74a3b; color: white; }
        .btn-red-custom:hover { background-color: #c0392b; color: white; }
    </style>

    <div class="page-content">

        @if(session('success'))
            <div class="mb-3 alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-3 alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="mb-3 alert alert-warning alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row justify-content-center">

            <!-- KOLOM KIRI -->
            <div class="mb-4 col-lg-7">
                <div class="border-0 shadow-lg card h-100">
                    <div class="text-white card-header bg-gradient-primary">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Konfigurasi Sistem</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.prayer.settings.update') }}" method="POST">
                            @csrf @method('PUT')
                            
                            <!-- 1. PENGATURAN LOKASI (MAPS) -->
                            <h6 class="pb-2 mb-3 fw-bold text-primary border-bottom"><i class="fas fa-map-marked-alt me-2"></i>Lokasi Absensi (Geofencing)</h6>

                            <div class="mb-3 map-wrapper">
                                <div class="mb-2 input-group">
                                    <input type="text" id="searchPlace" class="form-control" placeholder="Cari lokasi masjid/sekolah..." onkeypress="handleEnter(event)">
                                    <button class="btn btn-outline-primary" type="button" onclick="searchLocation()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div id="map" style="height: 250px; border-radius: 8px; border: 1px solid #ddd;"></div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold small">Latitude</label>
                                    <input type="text" id="lat" name="masjid_lat" class="form-control" value="{{ $lat }}" required oninput="manualInputUpdate()">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold small">Longitude</label>
                                    <input type="text" id="lng" name="masjid_lng" class="form-control" value="{{ $lng }}" required oninput="manualInputUpdate()">
                                </div>
                            </div>

                            <div class="mb-4 row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Radius (Meter)</label>
                                    <input type="number" id="radiusInput" name="masjid_radius" class="form-control" value="{{ $radius }}" min="5" max="1000" oninput="updateRadius(this.value)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">ID Kota</label>
                                    <input type="text" name="prayer_city_id" class="form-control" value="{{ $cityId ?? '0306' }}">
                                </div>
                            </div>

                            <!-- 2. PENGATURAN API -->
                            <h6 class="pb-2 mb-3 fw-bold text-primary border-bottom"><i class="fas fa-network-wired me-2"></i>Koneksi Antar Server</h6>

                            <div class="p-3 mb-3 border rounded bg-light">
                                <label class="mb-1 form-label fw-bold text-dark">Server Key (Lokal)</label>
                                <div class="input-group">
                                    <span class="bg-white input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="text" name="server_sync_key" class="bg-white form-control" value="{{ $myServerKey }}" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyKey()"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">URL Server Target</label>
                                <input type="url" name="target_sync_url" class="form-control" value="{{ $targetUrl }}" placeholder="Contoh: https://sekolahpusat.sch.id">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">API Key Server Target</label>
                                <input type="text" name="target_sync_key" class="form-control" value="{{ $targetKey }}">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="shadow-sm btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Simpan Semua Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: PANEL AKSI -->
            <div class="col-lg-5">

                <!-- 1. SYNC JADWAL SHOLAT -->
                <div class="mb-4 border-0 shadow-lg card">
                    <div class="text-white card-header bg-gradient-success">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Update Jadwal Sholat</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.prayer.sync') }}" method="POST">
                            @csrf
                            <div class="mb-3 row g-2">
                                <div class="col-7">
                                    <select name="month" class="form-select">
                                        @foreach($months as $k => $v) <option value="{{ $k }}" {{ date('n') == $k ? 'selected' : '' }}>{{ $v }}</option> @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <select name="year" class="form-select">
                                        @foreach($years as $y) <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option> @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-sync me-2"></i>Tarik Jadwal</button>
                        </form>
                    </div>
                </div>

                <!-- 2. SINKRONISASI DATA SERVER -->
                <div class="mb-4 border-0 shadow-lg card">
                    <div class="text-white card-header bg-gradient-info">
                        <h5 class="mb-0"><i class="fas fa-cloud-download-alt me-2"></i>Sinkronisasi Absensi</h5>
                    </div>
                    <div class="card-body">
                        <div class="p-2 mb-3 border rounded bg-light">
                            <p class="mb-1 small fw-bold text-primary"><i class="fas fa-info-circle me-1"></i> Data Server Target:</p>
                            <div class="mb-1 text-truncate small text-muted"><i class="fas fa-link me-1"></i> {{ $targetUrl ?? 'Belum disetting' }}</div>
                        </div>

                        <form action="{{ route('admin.prayer.pull_attendance') }}" method="POST" id="syncForm">
                            @csrf
                            <div class="mb-3 row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Dari Tanggal</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Sampai Tanggal</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            <p class="mb-2 fw-bold small text-dark">Pilih Data yang akan disinkron:</p>

                            <!-- GROUP 1: MASTER DATA -->
                            <div class="mb-2">
                                <label class="small text-muted fw-bold">1. Data Master (Jalankan Pertama Kali)</label>
                                
                                <button type="submit" name="type" value="master" class="mb-1 shadow-sm btn btn-dark-custom w-100 text-start" onclick="return confirmSync('master')">
                                    <i class="fas fa-database me-2"></i> Master Data (Guru, Kelas, Mapel)
                                </button>
                                
                                <button type="submit" name="type" value="student" class="mb-1 shadow-sm btn btn-indigo w-100 text-start" onclick="return confirmSync('student')">
                                    <i class="fas fa-users me-2"></i> Data Siswa
                                </button>
                                
                                <button type="submit" name="type" value="schedule" class="mb-1 shadow-sm btn btn-secondary w-100 text-start" onclick="return confirmSync('schedule')">
                                    <i class="fas fa-calendar-week me-2"></i> Jadwal Pelajaran
                                </button>
                            </div>

                            <!-- GROUP 2: ABSENSI HARIAN -->
                            <div class="mb-2">
                                <label class="small text-muted fw-bold">2. Data Harian</label>
                                
                                <button type="submit" name="type" value="prayer" class="mb-1 shadow-sm btn btn-info w-100 text-start" onclick="return confirmSync('prayer')">
                                    <i class="fas fa-mosque me-2"></i> Absensi Sholat
                                </button>
                                
                                <button type="submit" name="type" value="gate" class="mb-1 text-white shadow-sm btn btn-orange w-100 text-start" onclick="return confirmSync('gate')">
                                    <i class="fas fa-torii-gate me-2"></i> Absensi Gerbang
                                </button>
                                
                                <button type="submit" name="type" value="learning" class="mb-1 shadow-sm btn btn-purple w-100 text-start" onclick="return confirmSync('learning')">
                                    <i class="fas fa-book-reader me-2"></i> Absensi Pembelajaran
                                </button>
                                
                                <button type="submit" name="type" value="journal" class="mb-1 shadow-sm btn btn-teal w-100 text-start" onclick="return confirmSync('journal')">
                                    <i class="fas fa-book-open me-2"></i> Jurnal Guru
                                </button>
                                
                                <!-- MBG BUTTON -->
                                <button type="submit" name="type" value="mbg" class="mb-1 shadow-sm btn btn-pink w-100 text-start" onclick="return confirmSync('mbg')">
                                    <i class="fas fa-utensils me-2"></i> Makan Bergizi Gratis (MBG)
                                </button>
                                
                                <!-- PERMIT BUTTON -->
                                <button type="submit" name="type" value="permit" class="mb-1 shadow-sm btn btn-red-custom w-100 text-start" onclick="return confirmSync('permit')">
                                    <i class="fas fa-id-card-alt me-2"></i> Izin Keluar/Masuk (Permit)
                                </button>
                            </div>
                            
                            <!-- Tombol Semua -->
                            <hr class="my-3">
                            <button type="submit" name="type" value="all" class="btn btn-success w-100" onclick="return confirmSync('all')">
                                <i class="fas fa-sync-alt me-2"></i> Sinkron SEMUA Data
                            </button>

                            <div id="syncLoader" class="mt-3 text-center d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                <span class="ms-2 small text-muted">Menghubungi server target...</span>
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
        function copyKey() {
            const copyText = document.querySelector("input[name='server_sync_key']");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value).then(() => alert("Key berhasil disalin!"));
        }

        function confirmSync(type) {
            const url = document.querySelector("input[name='target_sync_url']").value;
            const key = document.querySelector("input[name='target_sync_key']").value;
            if(!url || !key) { alert("URL dan API Key belum diisi!"); return false; }

            let msg = 'Sinkronisasi data?';
            if(type === 'master') msg = 'Tarik Master Data (Guru, Mapel, Kelas, Ruangan)?\nJalankan ini SEBELUM menarik jadwal!';
            if(type === 'student') msg = 'Tarik Data Siswa?';
            if(type === 'schedule') msg = 'Tarik Jadwal Pelajaran?\nPastikan Master Data sudah ditarik.';
            if(type === 'prayer') msg = 'Tarik data Absensi Sholat?';
            if(type === 'gate') msg = 'Tarik data Absensi Gerbang?';
            if(type === 'learning') msg = 'Tarik data Absensi Pembelajaran?';
            if(type === 'journal') msg = 'Tarik data Jurnal Guru?';
            if(type === 'mbg') msg = 'Tarik data Absensi MBG (Makan Bergizi)?';
            if(type === 'permit') msg = 'Tarik data Izin Siswa (Permit)?';
            if(type === 'all') msg = 'Tarik SEMUA data? (Proses ini mungkin memakan waktu lama)';

            if(confirm(msg)) {
                document.getElementById('syncLoader').classList.remove('d-none');
                setTimeout(() => {
                     document.querySelectorAll('#syncForm button[type="submit"]').forEach(btn => {
                        btn.style.opacity = '0.6'; btn.style.pointerEvents = 'none';
                     });
                }, 100);
                return true;
            }
            return false;
        }

        // Logic Peta
        let map, marker, circle;
        document.addEventListener("DOMContentLoaded", function() {
            let curLat = {{ $lat }}; let curLng = {{ $lng }}; let curRadius = {{ $radius }};
            setTimeout(() => {
                if (L.DomUtil.get('map') && L.DomUtil.get('map')._leaflet_id) L.DomUtil.get('map')._leaflet_id = null;
                map = L.map('map', { center: [curLat, curLng], zoom: 18, scrollWheelZoom: true });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
                marker = L.marker([curLat, curLng], { draggable: true, autoPan: true }).addTo(map);
                circle = L.circle([curLat, curLng], { color: 'green', fillColor: '#2ecc71', fillOpacity: 0.2, radius: curRadius }).addTo(map);
                marker.on('dragend', function (e) { const pos = marker.getLatLng(); updateFormInputs(pos.lat, pos.lng); });
                map.on('click', function(e) { updateMarkerPosition(e.latlng.lat, e.latlng.lng); });
                window.updateRadius = function(val) { if(circle) circle.setRadius(val); }
                window.manualInputUpdate = function() {
                    const lat = parseFloat(document.getElementById('lat').value);
                    const lng = parseFloat(document.getElementById('lng').value);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const newLatLng = new L.LatLng(lat, lng);
                        marker.setLatLng(newLatLng); circle.setLatLng(newLatLng); map.panTo(newLatLng);
                    }
                }
            }, 300);
        });

        function updateMarkerPosition(lat, lng) {
            const newLatLng = new L.LatLng(lat, lng);
            marker.setLatLng(newLatLng); circle.setLatLng(newLatLng); map.panTo(newLatLng);
            updateFormInputs(lat, lng);
        }
        function updateFormInputs(lat, lng) {
            document.getElementById('lat').value = lat; document.getElementById('lng').value = lng;
        }
        function searchLocation() {
            const query = document.getElementById('searchPlace').value;
            if (!query) return;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) updateMarkerPosition(parseFloat(data[0].lat), parseFloat(data[0].lon));
                    else alert('Lokasi tidak ditemukan.');
                });
        }
        function handleEnter(e) { if(e.key === 'Enter'){ e.preventDefault(); searchLocation(); } }
    </script>
    @endpush
</x-app-layout>