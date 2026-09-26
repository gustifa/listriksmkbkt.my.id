@section('title', 'Jadwal & Absensi Sholat')

<x-app-layout>
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

        /* Map */
        .leaflet-container { font-family: inherit; }
    </style>

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <!-- HEADER WAKTU -->
                <div class="mb-3 text-white shadow card bg-gradient-to-r from-primary to-green-600" style="background: linear-gradient(45deg, #15803d, #16a34a);">
                    <div class="p-4 text-center card-body">
                        <h5 class="mb-1 text-white-50">Jadwal Ibadah Hari Ini</h5>
                        <h2 class="mb-0 fw-bold">Masjid Sekolah</h2>
                        <div class="px-3 py-1 mt-2 bg-white badge text-primary rounded-pill shadow-sm">
                            <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
                        </div>
                    </div>
                </div>

                <!-- NOTIFIKASI -->
                @if(session('success'))
                    <div class="mb-3 alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-3 alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-danger border-4">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- 1. SHOLAT FARDHU & RAWATIB -->
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <h6 class="text-xs font-bold uppercase text-secondary mb-0">Sholat Wajib & Rawatib</h6>
                    <span class="badge bg-light text-dark border">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
                </div>
                
                <div class="mb-4 shadow-sm card border-0">
                    <div class="list-group list-group-flush">
                        @php
                            $fardhu = [
                                'subuh'   => ['label' => 'Subuh',   'rawatib' => ['qobliyah_subuh' => 'Qobliyah']],
                                'dzuhur'  => ['label' => 'Dzuhur',  'rawatib' => ['qobliyah_dzuhur' => 'Qobliyah', 'badiyah_dzuhur' => 'Ba\'diyah']],
                                'ashar'   => ['label' => 'Ashar',   'rawatib' => ['qobliyah_ashar' => 'Qobliyah']],
                                'maghrib' => ['label' => 'Maghrib', 'rawatib' => ['qobliyah_maghrib' => 'Qobliyah', 'badiyah_maghrib' => 'Ba\'diyah']],
                                'isya'    => ['label' => 'Isya',    'rawatib' => ['qobliyah_isya' => 'Qobliyah', 'badiyah_isya' => 'Ba\'diyah']],
                            ];
                            
                            // Handle Jumat
                            if(\Carbon\Carbon::now()->isFriday()) {
                                $fardhu['dzuhur']['label'] = 'Jumat';
                                unset($fardhu['dzuhur']['rawatib']); // Jumat biasanya qobliyah berbeda/khutbah
                                $fardhu['dzuhur']['rawatib'] = ['qobliyah_dzuhur' => 'Sunnah Jumat']; // Opsional
                            }

                            $currentTime = \Carbon\Carbon::now()->format('H:i');
                        @endphp

                        @foreach($fardhu as $key => $data)
                            @php
                                $time = $schedule[$key] ?? '00:00'; 
                                $isDone = isset($attendances[$key]);
                                $isActive = $currentTime >= $time;
                                
                                // Deteksi sholat aktif untuk highlight
                                $isCurrent = false; // Logic highlight bisa ditambahkan jika perlu
                            @endphp

                            <div class="list-group-item p-3 {{ $isDone ? 'bg-light' : '' }}">
                                <!-- Baris Atas: Info & Tombol Utama -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
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
                                    
                                    <!-- Tombol Absen Fardhu -->
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
                                                <span class="badge bg-light text-secondary border">Belum Waktu</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Baris Bawah: Tombol Rawatib -->
                                <div class="d-flex align-items-center border-top pt-2 mt-1">
                                    <span class="text-[10px] text-muted me-2 uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Rawatib:</span>
                                    <div class="d-flex flex-wrap gap-1">
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
                <h6 class="px-2 mb-2 text-xs font-bold uppercase text-warning mt-4">Ibadah Tambahan (Ramadhan)</h6>
                <div class="mb-4 shadow-sm card border-0">
                    <div class="list-group list-group-flush">
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
                            <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
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
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i></span>
                                @else
                                    <button onclick="handleAbsensi('{{ $sKey }}', '{{ $sData['label'] }}')" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 fw-bold">Absen</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="text-center mt-4 mb-5">
                    <p class="text-muted small mb-1"><i class="fas fa-info-circle me-1"></i> Pastikan GPS aktif dan berada di area masjid.</p>
                    <small class="text-secondary" style="font-size: 0.7rem;">Jarak Maksimal: {{ $radius }} meter</small>
                </div>

            </div>
        </div>
    </div>

    <!-- Template Map untuk SweetAlert -->
    <template id="map-template">
        <div id="location-info" class="mb-2 text-start">
            <div id="status-text" class="p-2 mb-2 badge bg-warning text-dark w-100 text-wrap" style="font-size: 0.85rem;">
                <i class="fas fa-spinner fa-spin me-2"></i>Mencari lokasi...
            </div>
            <div id="map-canvas" style="height: 250px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
            <div class="mt-2 text-center text-muted fst-italic" style="font-size: 0.75rem;">
                *Pastikan berada di lingkaran hijau ({{ $radius }}m)
            </div>
        </div>
    </template>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const masjidLat = {{ $masjidLat }}; 
        const masjidLng = {{ $masjidLng }};
        const maxRadius = {{ $radius }}; 

        let map, marker, circle;

        function handleAbsensi(prayerKey, label) {
            Swal.fire({
                title: label,
                text: 'Konfirmasi kehadiran ibadah?',
                html: document.getElementById('map-template').innerHTML,
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim Absen',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0d6efd',
                allowOutsideClick: false,
                didOpen: () => { initMap(); },
                preConfirm: () => {
                    const lat = document.getElementById('lat_val')?.value;
                    const lng = document.getElementById('lng_val')?.value;
                    const dist = document.getElementById('dist_val')?.value;

                    if (!lat || !lng) { Swal.showValidationMessage('Tunggu lokasi ditemukan!'); return false; }
                    if (parseInt(dist) > maxRadius) { Swal.showValidationMessage(`Terlalu jauh (${dist}m). Mendekatlah ke Masjid.`); return false; }
                    return { lat, lng };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitPrayer(prayerKey, result.value.lat, result.value.lng);
                }
            });
        }

        function initMap() {
            setTimeout(() => {
                map = L.map('map-canvas').setView([masjidLat, masjidLng], 18);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'OSM' }).addTo(map);

                L.marker([masjidLat, masjidLng]).addTo(map).bindPopup("Masjid Sekolah").openPopup();
                circle = L.circle([masjidLat, masjidLng], { color: 'green', fillColor: '#2ecc71', fillOpacity: 0.2, radius: maxRadius }).addTo(map);

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition((pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        L.marker([lat, lng]).addTo(map).bindPopup("Anda").openPopup();
                        
                        const dist = calculateDistance(lat, lng, masjidLat, masjidLng);
                        updateStatusUI(dist, lat, lng);
                        
                        const group = new L.featureGroup([L.marker([masjidLat, masjidLng]), L.marker([lat, lng])]);
                        map.fitBounds(group.getBounds().pad(0.2));
                    }, (err) => {
                        document.getElementById('status-text').innerHTML = "GPS Error: " + err.message;
                    }, { enableHighAccuracy: true });
                }
            }, 300);
        }

        function updateStatusUI(dist, lat, lng) {
            const statusBox = document.getElementById('status-text');
            const container = document.getElementById('location-info');
            
            // Reset hidden inputs
            const oldLat = document.getElementById('lat_val'); if(oldLat) oldLat.remove();
            const oldLng = document.getElementById('lng_val'); if(oldLng) oldLng.remove();
            const oldDist = document.getElementById('dist_val'); if(oldDist) oldDist.remove();

            if (dist <= maxRadius) {
                statusBox.className = "badge bg-success w-100 p-2 mb-2";
                statusBox.innerHTML = `<i class="fas fa-check-circle me-1"></i> Dalam Radius: ${dist}m`;
            } else {
                statusBox.className = "badge bg-danger w-100 p-2 mb-2";
                statusBox.innerHTML = `<i class="fas fa-times-circle me-1"></i> Luar Radius: ${dist}m`;
            }
            
            container.innerHTML += `<input type="hidden" id="lat_val" value="${lat}"><input type="hidden" id="lng_val" value="${lng}"><input type="hidden" id="dist_val" value="${dist}">`;
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000; 
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2); 
            return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
        }

        function submitPrayer(name, lat, lng) {
            Swal.fire({ title: 'Menyimpan...', didOpen: () => Swal.showLoading() });
            $.ajax({
                url: "{{ route('student.prayer.store') }}",
                type: "POST",
                data: { _token: "{{ csrf_token() }}", prayer_name: name, latitude: lat, longitude: lng },
                success: function(res) {
                    Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                },
                error: function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Error', 'error');
                }
            });
        }
    </script>
    @endpush
</x-app-layout>