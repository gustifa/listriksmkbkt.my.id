@section('title', 'Jadwal & Absensi Sholat')

<x-app-layout>
    <style>
        #map-container {
            height: 200px;
            width: 100%;
            border-radius: 12px;
            display: none; /* Muncul hanya saat tombol absen ditekan */
            margin-bottom: 15px;
            border: 2px solid #e2e8f0;
        }
        .location-status {
            font-size: 0.75rem;
            margin-bottom: 10px;
        }
    </style>

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <!-- HEADER WAKTU -->
                <div class="mb-4 text-white shadow-lg card bg-primary">
                    <div class="p-4 text-center card-body">
                        <h5 class="mb-1 text-white-50">Jadwal Sholat Hari Ini</h5>
                        <h2 class="mb-0 fw-bold">Bukittinggi & Sekitarnya</h2>
                        <div class="px-3 py-2 mt-3 bg-white badge text-primary fs-6">
                            <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
                        </div>
                    </div>
                </div>

                <!-- DAFTAR JADWAL SHOLAT -->
                <div class="shadow-sm card">
                    <div class="list-group list-group-flush">
                        @php
                            $prayers = [
                                'subuh'   => 'Subuh',
                                'dhuha'   => 'Dhuha (Sunnah)',
                                'dzuhur'  => 'Dzuhur',
                                'ashar'   => 'Ashar',
                                'maghrib' => 'Maghrib',
                                'isya'    => 'Isya',
                            ];
                            $currentTime = \Carbon\Carbon::now()->format('H:i');
                        @endphp

                        @foreach($prayers as $key => $label)
                            @php
                                $time = $schedule[$key] ?? '-';
                                $isDone = isset($attendances[$key]);
                                $isActive = ($currentTime >= $time) || $key == 'dhuha';
                            @endphp

                            <div class="list-group-item p-3 d-flex justify-content-between align-items-center {{ $isDone ? 'bg-light' : '' }}">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box me-3 {{ $isDone ? 'text-success' : 'text-primary' }}">
                                        @if($key == 'subuh' || $key == 'isya') <i class="fas fa-moon fa-2x"></i>
                                        @elseif($key == 'maghrib') <i class="fas fa-cloud-sun fa-2x"></i>
                                        @else <i class="fas fa-sun fa-2x"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold {{ $isDone ? 'text-decoration-line-through text-muted' : '' }}">
                                            {{ $label }}
                                        </h5>
                                        <span class="text-muted small">
                                            <i class="far fa-clock"></i> {{ $time }} WIB
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    @if($isDone)
                                        <button class="px-3 btn btn-sm btn-success disabled rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i> Selesai
                                        </button>
                                        <div class="text-end" style="font-size: 0.7rem; color: #888;">
                                            {{ \Carbon\Carbon::parse($attendances[$key])->format('H:i') }}
                                        </div>
                                    @else
                                        @if($isActive)
                                            <button onclick="handleAbsensi('{{ $key }}', '{{ $label }}')" class="px-3 btn btn-sm btn-outline-primary rounded-pill">
                                                <i class="fas fa-map-marker-alt me-1"></i> Absen
                                            </button>
                                        @else
                                            <button class="px-3 btn btn-sm btn-light text-muted disabled rounded-pill">
                                                Belum Waktu
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 text-center text-muted small">
                    <p>Sumber Jadwal: <strong>myquran.com</strong></p>
                </div>

            </div>
        </div>
    </div>

    <!-- Container Peta (Hidden by default) -->
    <template id="map-template">
        <div id="location-info" class="mb-2 text-start">
            <div id="status-text" class="p-2 mb-2 badge bg-info w-100 text-wrap">
                <i class="fas fa-spinner fa-spin me-2"></i>Mencari lokasi presisi GPS...
            </div>
            <div id="map-canvas" style="height: 200px; width: 100%; border-radius: 8px;"></div>
        </div>
    </template>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let map;
        let marker;

        function handleAbsensi(prayerKey, label) {
            Swal.fire({
                title: 'Absen Sholat ' + label,
                html: document.getElementById('map-template').innerHTML,
                showCancelButton: true,
                confirmButtonText: 'Kirim Absen',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                allowOutsideClick: false,
                didOpen: () => {
                    initMap();
                },
                preConfirm: () => {
                    const lat = document.getElementById('lat_val')?.value;
                    const lng = document.getElementById('lng_val')?.value;

                    if (!lat || !lng) {
                        Swal.showValidationMessage('Tunggu hingga lokasi ditemukan!');
                        return false;
                    }
                    return { lat, lng };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitPrayer(prayerKey, result.value.lat, result.value.lng);
                }
            });
        }

        function initMap() {
            // Default center ke Bukittinggi jika GPS gagal
            const defaultLoc = [-0.3051, 100.3688];

            map = L.map('map-canvas').setView(defaultLoc, 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const userLoc = [lat, lng];

                        map.setView(userLoc, 17);
                        if (marker) map.removeLayer(marker);
                        marker = L.marker(userLoc).addTo(map).bindPopup("Lokasi Anda").openPopup();

                        // Simpan koordinat di element temporary
                        const statusBox = document.getElementById('status-text');
                        statusBox.className = "badge bg-success w-100 p-2 mb-2";
                        statusBox.innerHTML = `<i class="fas fa-check-circle me-2"></i>Lokasi Terkunci: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

                        // Inject hidden inputs ke swal
                        const container = document.getElementById('location-info');
                        container.innerHTML += `<input type="hidden" id="lat_val" value="${lat}"><input type="hidden" id="lng_val" value="${lng}">`;
                    },
                    (error) => {
                        document.getElementById('status-text').className = "badge bg-danger w-100 p-2 mb-2";
                        document.getElementById('status-text').innerHTML = `<i class="fas fa-times-circle me-2"></i>Gagal deteksi: ${error.message}. Pastikan GPS aktif.`;
                    },
                    { enableHighAccuracy: true }
                );
            } else {
                Swal.showValidationMessage('Browser Anda tidak mendukung Geolocation');
            }
        }

        function submitPrayer(prayerName, lat, lng) {
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ route('student.prayer.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    prayer_name: prayerName,
                    latitude: lat,
                    longitude: lng
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Alhamdulillah!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                    Swal.fire('Gagal', msg, 'error');
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
