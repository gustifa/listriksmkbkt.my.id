@section('title', 'Jadwal & Absensi Sholat')

<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map-container {
            height: 200px;
            width: 100%;
            border-radius: 12px;
            display: none;
            margin-bottom: 15px;
            border: 2px solid #e2e8f0;
        }
        .location-status {
            font-size: 0.75rem;
            margin-bottom: 10px;
        }
    </style>
    @endpush

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">

                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold text-primary"><i class="fas fa-mosque me-2"></i> Jadwal Sholat</h4>
                        <p class="mb-0 text-muted small"><i class="fas fa-map-marker-alt me-1"></i> Bukittinggi & Sekitarnya</p>
                    </div>
                    <div class="text-end">
                        <span class="px-3 py-2 shadow-sm badge bg-primary rounded-pill fs-6">
                            <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($today)->translatedFormat('d M Y') }}
                        </span>
                    </div>
                </div>

                @if(session('success'))
                    <div class="shadow-sm alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="border-0 shadow-lg card">
                    <div class="p-4 card-body">
                        <div class="mt-2 timeline-container ps-2">
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

                                    // Penentuan warna status timeline
                                    if ($isDone) {
                                        $statusColor = 'success';
                                    } elseif ($isActive) {
                                        $statusColor = 'primary';
                                    } else {
                                        $statusColor = 'secondary';
                                    }
                                @endphp

                                <div class="timeline-item position-relative pb-4 ps-4 border-start border-2 border-{{ $statusColor }}">
                                    <div class="position-absolute top-0 start-0 translate-middle rounded-circle border border-white shadow-sm bg-{{ $statusColor }}" style="width: 16px; height: 16px;"></div>

                                    <div class="border-0 shadow-sm card bg-light">
                                        <div class="flex-wrap gap-2 p-3 card-body d-flex justify-content-between align-items-center">

                                            <div class="d-flex align-items-center">
                                                <div class="icon-box me-3 text-{{ $statusColor }}">
                                                    @if($key == 'subuh' || $key == 'isya') <i class="fas fa-moon fa-2x"></i>
                                                    @elseif($key == 'maghrib') <i class="fas fa-cloud-sun fa-2x"></i>
                                                    @else <i class="fas fa-sun fa-2x"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-1 {{ $isDone ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                                        {{ $label }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="far fa-clock me-1"></i> {{ $time }} WIB
                                                    </small>
                                                </div>
                                            </div>

                                            <div>
                                                @if($isDone)
                                                    <div class="text-end">
                                                        <span class="px-3 py-2 badge bg-success rounded-pill">
                                                            <i class="fas fa-check-circle me-1"></i> Selesai
                                                        </span>
                                                        <div class="mt-1" style="font-size: 0.7rem; color: #888;">
                                                            Absen: {{ \Carbon\Carbon::parse($attendances[$key])->format('H:i') }}
                                                        </div>
                                                    </div>
                                                @else
                                                    @if($isActive)
                                                        <button onclick="handleAbsensi('{{ $key }}', '{{ $label }}')" class="px-3 shadow-sm btn btn-sm btn-primary rounded-pill">
                                                            <i class="fas fa-map-marker-alt me-1"></i> Absen
                                                        </button>
                                                    @else
                                                        <button class="px-3 btn btn-sm btn-outline-secondary disabled rounded-pill">
                                                            <i class="fas fa-lock me-1"></i> Belum Waktu
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 mt-4 text-center text-muted small border-top">
                            <p class="mb-0">Sumber Jadwal: <strong>myquran.com</strong></p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <template id="map-template">
        <div id="location-info" class="mb-2 text-start">
            <div id="status-text" class="p-2 mb-2 badge bg-info w-100 text-wrap">
                <i class="fas fa-spinner fa-spin me-2"></i>Mencari lokasi presisi GPS...
            </div>
            <div id="map-canvas" style="height: 200px; width: 100%; border-radius: 8px;"></div>
        </div>
    </template>

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

                        const statusBox = document.getElementById('status-text');
                        statusBox.className = "badge bg-success w-100 p-2 mb-2";
                        statusBox.innerHTML = `<i class="fas fa-check-circle me-2"></i>Lokasi Terkunci: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;

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
