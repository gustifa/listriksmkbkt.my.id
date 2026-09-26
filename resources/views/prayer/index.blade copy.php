@section('title', 'Jadwal & Absensi Sholat')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <!-- HEADER WAKTU -->
                <div class="card bg-primary text-white shadow-lg mb-4">
                    <div class="card-body text-center p-4">
                        <h5 class="text-white-50 mb-1">Jadwal Sholat Hari Ini</h5>
                        <h2 class="fw-bold mb-0">Bukittinggi & Sekitarnya</h2>
                        <div class="mt-3 badge bg-white text-primary px-3 py-2 fs-6">
                            <i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
                        </div>
                    </div>
                </div>

                <!-- DAFTAR JADWAL SHOLAT -->
                <div class="card shadow-sm">
                    <div class="list-group list-group-flush">
                        @php
                            // Mapping nama sholat API ke Display
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
                                // Logika sederhana: Tombol aktif jika waktu sekarang >= waktu sholat
                                // (Kecuali Dhuha yang fleksibel)
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
                                        <button class="btn btn-sm btn-success disabled rounded-pill px-3">
                                            <i class="fas fa-check-circle me-1"></i> Selesai
                                        </button>
                                        <div class="text-end" style="font-size: 0.7rem; color: #888;">
                                            {{ \Carbon\Carbon::parse($attendances[$key])->format('H:i') }}
                                        </div>
                                    @else
                                        @if($isActive)
                                            <button onclick="submitPrayer('{{ $key }}', '{{ $label }}')" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="fas fa-praying-hands me-1"></i> Absen
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-light text-muted disabled rounded-pill px-3">
                                                Belum Waktu
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="text-center mt-4 text-muted small">
                    <p>Sumber Jadwal: <strong>equran.id</strong></p>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function submitPrayer(prayerName, label) {
            Swal.fire({
                title: 'Absen Sholat ' + label + '?',
                text: "Pastikan Anda sudah melaksanakan sholat.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Sudah Sholat!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan Loading
                    Swal.fire({
                        title: 'Menyimpan...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // Kirim Request
                    $.ajax({
                        url: "{{ route('prayer.store') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            prayer_name: prayerName
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Alhamdulillah!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload(); // Refresh halaman
                            });
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                            Swal.fire('Gagal', msg, 'error');
                        }
                    });
                }
            });
        }
    </script>
    @endpush
</x-app-layout>