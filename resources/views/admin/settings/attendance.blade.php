@section('title')
   Pengaturan Jam Masuk, Pulang, Geofencing & Notifikasi
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
                        <li class="breadcrumb-item active" aria-current="page">Jadwal, Geofencing & Notifikasi</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i> Pengaturan Jam Operasional, Lokasi & Notifikasi</h5>
                    </div>
                    <div class="card-body">

                        <form action="{{ route('settings.update.attendance') }}" method="POST">
                            @csrf
                            <div class="card-body p-0">

                                {{-- SEKSI 1: JAM OPERASIONAL --}}
                                <h6 class="text-primary fw-bold mb-3"><i class="fas fa-clock me-2"></i> Jam Operasional Scan</h6>
                                <div class="row">
                                    <div class="mb-3 col-md-3 form-group">
                                        <label class="form-label fw-bold">Jam Buka Scan Masuk</label>
                                        <input type="time" name="start_check_in_time" class="form-control"
                                            value="{{ $setting->start_check_in_time ?? '06:00' }}" required>
                                        <div class="form-text">Cegah siswa absen terlalu pagi.</div>
                                    </div>

                                    <div class="mb-3 col-md-3 form-group">
                                        <label class="form-label fw-bold">Batas Jam Masuk (Batas Terlambat)</label>
                                        <input type="time" name="late_limit_time" class="form-control"
                                            value="{{ $setting->late_limit_time ?? '07:00' }}" required>
                                        <div class="form-text">Lewat dari jam ini dianggap Terlambat.</div>
                                    </div>

                                    <div class="mb-3 col-md-3 form-group">
                                        <label class="form-label fw-bold">Batas Awal Jam Pulang</label>
                                        <input type="time" name="early_departure_time" class="form-control"
                                            value="{{ $setting->early_departure_time ?? '10:00' }}" required>
                                        <div class="form-text">Sebelum jam ini siswa tidak bisa scan pulang.</div>
                                    </div>

                                    <div class="mb-3 col-md-3 form-group">
                                        <label class="form-label fw-bold text-danger">Jam Eksekusi Otomatis Alpa</label>
                                        <input type="time" name="auto_absent_time" class="form-control border-danger"
                                            value="{{ $setting->auto_absent_time ?? '13:00' }}" required>
                                        <div class="form-text text-danger">Mencatat alpa otomatis bagi siswa yang tidak absen.</div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                {{-- SEKSI 2: FITUR TELEGRAM, WA & MODE BELAJAR --}}
                                <h6 class="text-primary fw-bold mb-3"><i class="fab fa-telegram me-2"></i> Pengaturan Notifikasi Telegram & WhatsApp</h6>
                                
                                <!-- SAKELAR (TOGGLE) ON/OFF NOTIFIKASI -->
                                <div class="row mb-3 bg-light p-3 rounded border mx-0">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_telegram_notification" name="enable_telegram_notification" value="1"
                                                {{ ($setting->enable_telegram_notification ?? true) ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
                                            <label class="form-check-label fw-bold text-dark ms-2 cursor-pointer" for="enable_telegram_notification">
                                                🤖 Aktifkan Notifikasi Telegram
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-1">Jika dimatikan, seluruh pengiriman pesan & laporan otomatis ke Telegram dihentikan.</small>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_wa_notification" name="enable_wa_notification" value="1"
                                                {{ ($setting->enable_wa_notification ?? true) ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
                                            <label class="form-check-label fw-bold text-dark ms-2 cursor-pointer" for="enable_wa_notification">
                                                💬 Aktifkan Notifikasi WhatsApp
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-1">Jika dimatikan, pesan broadcast dan notifikasi presensi ke WhatsApp dihentikan.</small>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="mb-3 col-md-6 form-group">
                                        <label class="form-label fw-bold">Lampiran Foto Wajah di Telegram</label>
                                        <select name="send_photo_telegram" class="form-select">
                                            <option value="1" {{ ($setting->send_photo_telegram ?? true) ? 'selected' : '' }}>
                                                ✅ Ya (Lampirkan Foto Wajah + Pesan Laporan)
                                            </option>
                                            <option value="0" {{ !($setting->send_photo_telegram ?? true) ? 'selected' : '' }}>
                                                ❌ Tidak (Kirim Pesan Teks Laporan Saja)
                                            </option>
                                        </select>
                                        <div class="form-text">Sertakan foto hasil scan siswa pada pesan Telegram.</div>
                                    </div>

                                    <div class="mb-3 col-md-6 form-group">
                                        <label class="form-label fw-bold">Mode Pembelajaran Sekolah</label>
                                        <select name="learning_mode" class="form-select">
                                            <option value="PTM" {{ ($setting->learning_mode ?? 'PTM') == 'PTM' ? 'selected' : '' }}>
                                                🏫 Tatap Muka (PTM / Gerbang)
                                            </option>
                                            <option value="PJJ" {{ ($setting->learning_mode ?? 'PTM') == 'PJJ' ? 'selected' : '' }}>
                                                💻 Pembelajaran Jarak Jauh (PJJ / HP Mandiri)
                                            </option>
                                        </select>
                                        <div class="form-text">Memperbarui judul laporan notifikasi menjadi <strong>(GERBANG)</strong> atau <strong>(PJJ)</strong>.</div>
                                    </div>
                                </div>

                                <!-- PARAMETER BOT TELEGRAM DINAMIS -->
                                <div class="row bg-light p-3 rounded border mx-0 mt-2">
                                    <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-key me-2"></i> Parameter Akses Telegram Bot</h6>
                                    
                                    <div class="mb-3 col-md-6 form-group">
                                        <label class="form-label fw-bold">Telegram Bot Token</label>
                                        <input type="text" name="telegram_bot_token" class="form-control"
                                            value="{{ $setting->telegram_bot_token ?? '' }}" placeholder="Contoh: 8420994424:AAEU728v0p...">
                                        <div class="form-text">Kosongkan jika ingin menggunakan token default dari `.env`.</div>
                                    </div>

                                    <div class="mb-3 col-md-6 form-group">
                                        <label class="form-label fw-bold">Telegram Admin Group Chat ID</label>
                                        <input type="text" name="telegram_admin_chat_id" class="form-control"
                                            value="{{ $setting->telegram_admin_chat_id ?? '' }}" placeholder="Contoh: -1003906615619">
                                        <div class="form-text">ID Grup Telegram Admin/Guru untuk menerima pengajuan izin.</div>
                                    </div>

                                    <div class="mb-3 col-md-6 form-group">
                                        <label class="form-label fw-bold">Telegram Bot Username</label>
                                        <input type="text" name="telegram_bot_username" class="form-control"
                                            value="{{ $setting->telegram_bot_username ?? '' }}" placeholder="Contoh: listrikbk_bot">
                                    </div>

                                    <div class="mb-3 col-md-6 form-group">
                                        <label class="form-label fw-bold">Link Group Telegram Admin</label>
                                        <input type="text" name="telegram_admin_group_link" class="form-control"
                                            value="{{ $setting->telegram_admin_group_link ?? '' }}" placeholder="Contoh: https://t.me/+p_yx7_McCC5jN2U1">
                                    </div>
                                </div>

                                <hr class="my-4">

                                {{-- SEKSI 3: PENGATURAN GEOFENCING / PETA --}}
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h6 class="text-primary fw-bold mb-0"><i class="fas fa-map-marked-alt me-2"></i> Lokasi Absensi (Geofencing)</h6>
                                    
                                    <!-- TOGGLE ON/OFF GEOFENCING -->
                                    <div class="form-check form-switch bg-light p-2 rounded border">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="is_geofence_active" name="is_geofence_active" value="1" 
                                               {{ ($setting->is_geofence_active ?? true) ? 'checked' : '' }} style="transform: scale(1.3); cursor: pointer;">
                                        <label class="form-check-label fw-bold text-dark cursor-pointer" for="is_geofence_active">
                                            Aktifkan Validasi Lokasi GPS (Mode PTM / Sekolah)
                                        </label>
                                    </div>
                                </div>

                                <!-- SEARCH BOX PETA -->
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

            map = L.map('map').setView([defaultLat, defaultLng], 17);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; Leaflet | OpenStreetMap'
            }).addTo(map);

            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            circle = L.circle([defaultLat, defaultLng], {
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.2,
                radius: defaultRadius
            }).addTo(map);

            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });

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