@extends('layouts.app')

@section('title', 'Jadwal & Absensi Sholat')

@section('content')
<style>
    .icon-box { width: 45px; text-align: center; }
    .list-group-item { border-left: 4px solid transparent; transition: all 0.2s; }
    .list-group-item:hover { background-color: #f8f9fa; }
    .active-prayer { border-left-color: #0d6efd; background-color: #f0f7ff; }
    
    /* Tombol Rawatib */
    .btn-rawatib { 
        font-size: 0.7rem; 
        padding: 2px 8px; 
        border-radius: 20px; 
        border: 1px solid #dee2e6; 
        color: #6c757d; 
        background: white; 
        margin-right: 4px;
        transition: all 0.2s;
    }
    .btn-rawatib:hover { background-color: #e9ecef; }
    .btn-rawatib.done { background-color: #198754; color: white; border-color: #198754; }
    .btn-rawatib.disabled { opacity: 0.5; cursor: not-allowed; }

    /* Container Peta Utama yang Terkunci */
    #main-map-canvas {
        height: 220px !important;
        width: 100% !important;
        border-radius: 15px;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
        border: 2px solid #dee2e6;
        position: relative !important;
        overflow: hidden !important;
        z-index: 1;
    }

    #main-map-canvas img.leaflet-tile {
        max-width: none !important;
        max-height: none !important;
    }
</style>

<div class="page-content py-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <!-- HEADER WAKTU -->
            <div class="mb-3 text-white shadow card border-0 rounded-4" style="background: linear-gradient(45deg, #15803d, #16a34a);">
                <div class="p-4 text-center card-body">
                    <h5 class="mb-1 text-white-50">Jadwal Ibadah Hari Ini</h5>
                    <h2 class="mb-0 fw-bold">{{ $isMosqueMode ? 'Masjid Sekolah' : 'Sholat Mandiri / Rumah' }}</h2>
                    <div class="px-3 py-1 mt-2 bg-white badge text-success rounded-pill shadow-sm">
                        <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
                    </div>
                </div>
            </div>

            <!-- CARD PETA & LOKASI (HANYA MUNCUL JIKA MODE MASJID / $isMosqueMode = TRUE) -->
            @if($isMosqueMode)
                <div class="mb-3 shadow-sm card border-0 rounded-4">
                    <div class="p-3 card-body">
                        <div id="status-box" class="p-2 mb-2 badge bg-warning text-dark w-100 text-wrap" style="font-size: 0.85rem;">
                            <i class="fas fa-spinner fa-spin me-2"></i>Mendeteksi posisi lokasi Anda...
                        </div>
                        
                        <!-- Peta Utama -->
                        <div id="main-map-canvas" class="mb-2"></div>

                        <!-- TOMBOL REFRESH LOKASI -->
                        <div class="mb-2 text-end">
                            <button id="btn-refresh-location" onclick="fetchUserLocation(true)" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" style="font-size: 0.75rem;">
                                <i class="fas fa-crosshairs me-1"></i> Lokasi Saya Sekarang
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center text-muted small px-1 mb-1">
                            <span><i class="fas fa-map-marker-alt me-1 text-danger"></i> Jarak Anda: <strong id="distance-text">-</strong></span>
                            <span>Maksimal: <strong>{{ $radius }} meter</strong></span>
                        </div>
                        
                        <!-- Info Koordinat Siswa -->
                        <div class="text-start text-secondary" style="font-size: 0.72rem;">
                            <i class="fas fa-location-arrow me-1"></i> Posisi Anda: <span id="user-coords-text" class="fw-bold font-monospace">Mencari GPS...</span>
                        </div>
                    </div>
                </div>
            @else
                <!-- INFORMASI MODE RUMAH -->
                <div class="p-3 mb-3 text-center border-0 alert alert-info rounded-4 shadow-sm">
                    <i class="mb-1 fas fa-home fa-2x text-info"></i>
                    <h6 class="mb-1 fw-bold text-dark">Mode Sholat Mandiri / Rumah Active</h6>
                    <small class="text-muted d-block">Pengecekan radius lokasi masjid disiapkan off. Anda dapat mencatat absensi ibadah dari rumah.</small>
                </div>
            @endif

            <!-- 1. SHOLAT FARDHU & RAWATIB -->
            <div class="px-1 mb-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-bold text-xs uppercase text-secondary">Sholat Wajib & Rawatib</h6>
                <span class="border badge bg-light text-dark">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
            </div>
            
            <div class="mb-4 border-0 shadow-sm card rounded-3">
                <div class="list-group list-group-flush rounded-3">
                    @php
                        $fardhu = [
                            'subuh'   => ['label' => 'Subuh',   'rawatib' => ['qobliyah_subuh' => 'Qobliyah']],
                            'dzuhur'  => ['label' => 'Dzuhur',  'rawatib' => ['qobliyah_dzuhur' => 'Qobliyah', 'badiyah_dzuhur' => 'Ba\'diyah']],
                            'ashar'   => ['label' => 'Ashar',   'rawatib' => ['qobliyah_ashar' => 'Qobliyah']],
                            'maghrib' => ['label' => 'Maghrib', 'rawatib' => ['qobliyah_maghrib' => 'Qobliyah', 'badiyah_maghrib' => 'Ba\'diyah']],
                            'isya'    => ['label' => 'Isya',    'rawatib' => ['qobliyah_isya' => 'Qobliyah', 'badiyah_isya' => 'Ba\'diyah']],
                        ];
                        
                        if(\Carbon\Carbon::now()->isFriday()) {
                            $fardhu['dzuhur']['label'] = 'Jumat';
                            unset($fardhu['dzuhur']['rawatib']);
                            $fardhu['dzuhur']['rawatib'] = ['qobliyah_dzuhur' => 'Sunnah Jumat'];
                        }

                        $currentTime = \Carbon\Carbon::now()->format('H:i');
                    @endphp

                    @foreach($fardhu as $key => $data)
                        @php
                            $time = $schedule[$key] ?? '00:00'; 
                            $isDone = isset($attendances[$key]);
                            $isActive = $currentTime >= $time;
                        @endphp

                        <div class="list-group-item p-3 {{ $isDone ? 'bg-light' : '' }}">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box me-3 {{ $isDone ? 'text-success' : 'text-primary' }}">
                                        @if($key == 'subuh' || $key == 'isya') <i class="fas fa-moon fa-lg"></i>
                                        @elseif($key == 'maghrib') <i class="fas fa-cloud-sun fa-lg"></i>
                                        @else <i class="fas fa-sun fa-lg"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold {{ $isDone ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                            {{ $data['label'] }}
                                        </h6>
                                        <span class="text-muted small font-monospace"><i class="far fa-clock me-1"></i>{{ $time }}</span>
                                    </div>
                                </div>
                                
                                <div>
                                    @if($isDone)
                                        <button class="btn btn-sm btn-success disabled rounded-pill px-3 fw-bold" style="opacity: 0.8">
                                            <i class="fas fa-check me-1"></i> Selesai
                                        </button>
                                    @else
                                        @if($isActive)
                                            <button onclick="handleAbsensi('{{ $key }}', '{{ $data['label'] }}')" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm fw-bold">
                                                Absen
                                            </button>
                                        @else
                                            <span class="border badge bg-light text-secondary">Belum Waktu</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="pt-2 mt-1 border-top d-flex align-items-center">
                                <span class="text-muted me-2 uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Rawatib:</span>
                                <div class="flex-wrap gap-1 d-flex">
                                    @foreach($data['rawatib'] as $rKey => $rLabel)
                                        @php $rDone = isset($attendances[$rKey]); @endphp
                                        
                                        @if($rDone)
                                            <span class="btn-rawatib done"><i class="fas fa-check me-1"></i> {{ $rLabel }}</span>
                                        @else
                                            <button onclick="handleAbsensi('{{ $rKey }}', 'Rawatib {{ $rLabel }} - {{ $data['label'] }}')" 
                                                    class="btn-rawatib {{ !$isActive ? 'disabled' : '' }}"
                                                    {{ !$isActive ? 'disabled' : '' }}>
                                                {{ $rLabel }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 2. IBADAH RAMADHAN & SUNNAH -->
            <h6 class="px-2 mt-4 mb-2 font-bold uppercase text-xs text-warning">Ibadah Tambahan (Ramadhan / Sunnah)</h6>
            <div class="mb-4 border-0 shadow-sm card rounded-3">
                <div class="list-group list-group-flush rounded-3">
                    @php
                        $sunnah = [
                            'tarawih'  => ['label' => 'Sholat Tarawih', 'desc' => 'Qiyamul Lail Ramadhan', 'icon' => 'fa-star-and-crescent'],
                            'witir'    => ['label' => 'Sholat Witir',   'desc' => 'Penutup Sholat Malam',  'icon' => 'fa-star'],
                            'tahajjud' => ['label' => 'Sholat Tahajjud','desc' => 'Sepertiga Malam',       'icon' => 'fa-bed'],
                            'dhuha'    => ['label' => 'Sholat Dhuha',   'desc' => 'Pagi Hari',             'icon' => 'fa-sun'],
                        ];
                    @endphp

                    @foreach($sunnah as $sKey => $sData)
                        @php $sDone = isset($attendances[$sKey]); @endphp
                        <div class="p-3 list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-box me-3 text-warning">
                                    <i class="fas {{ $sData['icon'] }} fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark {{ $sDone ? 'text-decoration-line-through text-muted' : '' }}">{{ $sData['label'] }}</h6>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $sData['desc'] }}</small>
                                </div>
                            </div>
                            @if($sDone)
                                <span class="badge bg-success rounded-pill"><i class="fas fa-check me-1"></i> Selesai</span>
                            @else
                                <button onclick="handleAbsensi('{{ $sKey }}', '{{ $sData['label'] }}')" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 fw-bold">Absen</button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 mb-5 text-center">
                <p class="mb-1 text-muted small"><i class="fas fa-info-circle me-1"></i> Pastikan GPS/Lokasi HP dalam keadaan aktif saat melakukan absensi.</p>
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    window.prayerConfig = {
        masjidLat: Number("{{ $masjidLat }}"),
        masjidLng: Number("{{ $masjidLng }}"),
        maxRadius: Number("{{ $radius }}"),
        isMosqueMode: {{ $isMosqueMode ? 'true' : 'false' }},
        storeUrl: "{{ route('student.prayer-attendances.store') }}",
        csrfToken: "{{ csrf_token() }}"
    };

    let mainMap = null;
    let userMarker = null;
    let userCoords = null;
    let currentDistance = null;

    document.addEventListener('DOMContentLoaded', function() {
        if (window.prayerConfig.isMosqueMode) {
            setTimeout(function() {
                initMainMap();
            }, 200);
        } else {
            // Ambil GPS latar belakang jika di rumah
            fetchSilentLocation();
        }
    });

    function initMainMap() {
        const lat = window.prayerConfig.masjidLat;
        const lng = window.prayerConfig.masjidLng;
        const radius = window.prayerConfig.maxRadius;

        if (mainMap !== null) {
            mainMap.remove();
            mainMap = null;
        }

        mainMap = L.map('main-map-canvas', { zoomControl: false }).setView([lat, lng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mainMap);

        L.circle([lat, lng], { color: '#198754', fillColor: '#198754', fillOpacity: 0.2, radius: radius }).addTo(mainMap);

        L.marker([lat, lng]).addTo(mainMap).bindPopup("<b>Masjid Sekolah</b>").openPopup();

        setTimeout(function() { if (mainMap) mainMap.invalidateSize(); }, 300);

        fetchUserLocation(false);
    }

    function fetchSilentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                userCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            }, null, { enableHighAccuracy: true });
        }
    }

    function fetchUserLocation(isManualClick) {
        const statusBox = document.getElementById('status-box');
        const distanceText = document.getElementById('distance-text');
        const userCoordsText = document.getElementById('user-coords-text');
        const btnRefresh = document.getElementById('btn-refresh-location');

        if (btnRefresh && isManualClick) {
            btnRefresh.disabled = true;
            btnRefresh.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memperbarui...';
        }

        if (!navigator.geolocation) {
            if (statusBox) {
                statusBox.className = "badge bg-danger w-100 p-2 mb-2";
                statusBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Browser tidak mendukung GPS';
            }
            return;
        }

        const lat = window.prayerConfig.masjidLat;
        const lng = window.prayerConfig.masjidLng;
        const radius = window.prayerConfig.maxRadius;

        navigator.geolocation.getCurrentPosition(function(pos) {
            userCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude };

            if (userCoordsText) {
                userCoordsText.innerText = userCoords.lat.toFixed(6) + ", " + userCoords.lng.toFixed(6);
            }

            if (mainMap) {
                if (!userMarker) {
                    userMarker = L.circleMarker([userCoords.lat, userCoords.lng], { radius: 8, fillColor: '#0d6efd', color: '#ffffff', weight: 2, fillOpacity: 0.9 }).addTo(mainMap).bindPopup("<b>Lokasi Anda</b>");
                } else {
                    userMarker.setLatLng([userCoords.lat, userCoords.lng]);
                }
                mainMap.fitBounds(L.latLngBounds([[lat, lng], [userCoords.lat, userCoords.lng]]), { padding: [30, 30] });
            }

            currentDistance = calculateDistance(userCoords.lat, userCoords.lng, lat, lng);
            if (distanceText) distanceText.innerText = currentDistance + " meter";

            if (statusBox) {
                if (currentDistance <= radius) {
                    statusBox.className = "badge bg-success w-100 p-2 mb-2";
                    statusBox.innerHTML = '<i class="fas fa-check-circle me-1"></i> Dalam Radius Masjid (' + currentDistance + 'm)';
                } else {
                    statusBox.className = "badge bg-danger w-100 p-2 mb-2";
                    statusBox.innerHTML = '<i class="fas fa-times-circle me-1"></i> Di Luar Radius Masjid (' + currentDistance + 'm)';
                }
            }

            if (btnRefresh) {
                btnRefresh.disabled = false;
                btnRefresh.innerHTML = '<i class="fas fa-crosshairs me-1"></i> Lokasi Saya Sekarang';
            }

        }, function(err) {
            if (statusBox) {
                statusBox.className = "badge bg-danger w-100 p-2 mb-2";
                statusBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Gagal GPS: ' + err.message;
            }
            if (btnRefresh) {
                btnRefresh.disabled = false;
                btnRefresh.innerHTML = '<i class="fas fa-crosshairs me-1"></i> Lokasi Saya Sekarang';
            }
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
    }

    function handleAbsensi(prayerKey, label) {
        if (!userCoords) {
            Swal.fire({
                icon: 'warning',
                title: 'Mencari GPS...',
                text: 'Silakan aktifkan GPS dan klik tombol "Lokasi Saya Sekarang".',
                position: 'center'
            });
            return;
        }

        // 1. JIKA MODE WAJIB MASJID & DI LUAR RADIUS
        if (window.prayerConfig.isMosqueMode && currentDistance > window.prayerConfig.maxRadius) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Absen',
                text: 'Jarak Anda (' + currentDistance + 'm) melebihi radius maksimal masjid (' + window.prayerConfig.maxRadius + 'm).',
                position: 'center'
            });
            return;
        }

        // 2. JIKA MODE RUMAH / PJJ (DENGAN VALIDASI UKURAN FOTO MAKSIMAL 2MB)
        if (!window.prayerConfig.isMosqueMode) {
            Swal.fire({
                title: label,
                text: 'Upload Foto Bukti Ibadah dari Rumah / PJJ (Maksimal 2 MB)',
                input: 'file',
                inputAttributes: { 'accept': 'image/*', 'aria-label': 'Upload foto bukti' },
                showCancelButton: true,
                confirmButtonText: 'Kirim Absensi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#198754',
                position: 'center',
                inputValidator: (file) => {
                    if (!file) {
                        return 'Anda wajib mengunggah foto bukti sholat!';
                    }
                    // Validasi Ukuran File Maksimal 2 MB (2 * 1024 * 1024 bytes)
                    if (file.size > 2 * 1024 * 1024) {
                        return 'Ukuran foto terlalu besar (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB). Maksimal ukuran foto adalah 2 MB!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    submitPrayerWithPhoto(prayerKey, userCoords.lat, userCoords.lng, result.value);
                }
            });
            return;
        }

        // 3. JIKA MODE MASJID (KONFIRMASI BIASA)
        Swal.fire({
            title: label,
            text: 'Konfirmasi kehadiran ibadah di Masjid Sekolah? (Jarak Anda: ' + currentDistance + 'm)',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim Absen',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0d6efd',
            position: 'center'
        }).then(function(result) {
            if (result.isConfirmed) {
                submitPrayer(prayerKey, userCoords.lat, userCoords.lng);
            }
        });
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; 
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2); 
        return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
    }

    function submitPrayer(name, lat, lng) {
        Swal.fire({ title: 'Menyimpan...', position: 'center', didOpen: function() { Swal.showLoading(); } });

        $.ajax({
            url: window.prayerConfig.storeUrl,
            type: "POST",
            data: { 
                _token: window.prayerConfig.csrfToken, 
                prayer_name: name, 
                latitude: lat, 
                longitude: lng 
            },
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    position: 'center',
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() { location.reload(); });
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg, position: 'center' });
            }
        });
    }

    function submitPrayerWithPhoto(name, lat, lng, photoFile) {
        Swal.fire({ title: 'Mengunggah Foto & Menyimpan...', position: 'center', didOpen: function() { Swal.showLoading(); } });

        let formData = new FormData();
        formData.append('_token', window.prayerConfig.csrfToken);
        formData.append('prayer_name', name);
        formData.append('latitude', lat);
        formData.append('longitude', lng);
        formData.append('photo_evidence', photoFile);

        $.ajax({
            url: window.prayerConfig.storeUrl,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    position: 'center',
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() { location.reload(); });
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan pada sistem.';

                // Tangkap error validasi dari controller Laravel
                if (xhr.status === 422 && xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        // Ambil pesan error validasi pertama (misal: photo_evidence max)
                        let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                        msg = xhr.responseJSON.errors[firstKey][0];
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }

                Swal.fire({ 
                    icon: 'error', 
                    title: 'Gagal Menyimpan', 
                    text: msg, 
                    position: 'center' 
                });
            }
        });
    }
</script>
@endsection