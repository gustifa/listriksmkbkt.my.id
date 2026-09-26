@section('title', 'Jadwal Mengajar')

<x-app-layout>
    <div class="page-content">
        <div class="container py-4">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-calendar-alt me-2"></i> Jadwal Mengajar Saya</h4>
                    <p class="text-muted small mb-0">Kelola jadwal pelajaran dan pantau absensi harian.</p>
                </div>
                <a href="{{ route('schedule.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Buat Jadwal Baru
                </a>
            </div>

            <!-- Alert Success -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- List Jadwal -->
            <div class="row">
                @forelse($schedules as $schedule)
                    <!-- Highlight jika hari ini dan jam sekarang -->
                    @php
                        $now = \Carbon\Carbon::now();
                        $isToday = $schedule->day == $now->translatedFormat('l');
                        
                        // Parse jam mulai dan selesai menjadi objek Carbon hari ini untuk perbandingan
                        $startTime = \Carbon\Carbon::parse($schedule->start_time)->setDate($now->year, $now->month, $now->day);
                        $endTime = \Carbon\Carbon::parse($schedule->end_time)->setDate($now->year, $now->month, $now->day);
                        
                        // Cek apakah waktu sekarang berada dalam rentang jadwal (Jadwal Aktif)
                        $isActive = $isToday && $now->between($startTime, $endTime);

                        // Tentukan style berdasarkan status
                        if ($isActive) {
                            $cardBorder = 'border-warning'; // Kuning untuk aktif
                            $badgeBg = 'bg-warning text-dark';
                            $activeClass = 'shadow-lg bg-white'; // Tambahan shadow
                        } elseif ($isToday) {
                            $cardBorder = 'border-success'; // Hijau untuk hari ini (tapi belum/sudah lewat jam)
                            $badgeBg = 'bg-success';
                            $activeClass = '';
                        } else {
                            $cardBorder = 'border-primary'; // Biru untuk hari lain
                            $badgeBg = 'bg-primary';
                            $activeClass = '';
                        }
                    @endphp

                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm h-100 border-start border-4 {{ $cardBorder }} {{ $activeClass }} hover-scale" style="{{ $isActive ? 'transform: scale(1.02);' : '' }}">
                            <div class="card-body">
                                <!-- Baris Atas: Hari & Jam -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge {{ $badgeBg }} rounded-pill px-3">
                                        {{ $schedule->day }}
                                        @if($isActive) 
                                            <i class="fas fa-circle ms-1 text-danger small animate-pulse"></i> LIVE 
                                        @endif
                                    </span>
                                    <span class="fw-bold text-dark font-monospace">
                                        {{ $startTime->format('H:i') }} - 
                                        {{ $endTime->format('H:i') }}
                                    </span>
                                </div>
                                
                                <!-- Info Mapel & Kelas -->
                                <h5 class="card-title fw-bold text-dark mb-1 text-truncate" title="{{ $schedule->subject->name ?? 'Mapel Dihapus' }}">
                                    {{ $schedule->subject->name ?? 'Mapel Dihapus' }}
                                </h5>
                                <p class="card-text text-muted mb-3">
                                    <i class="fas fa-door-open me-1"></i> {{ $schedule->classroom->name ?? 'Kelas Dihapus' }}
                                </p>

                                <hr class="my-2 border-light">

                                <!-- Footer Card: Statistik & Aksi -->
                                <div class="d-flex justify-content-between align-items-end mt-3">
                                    <!-- Info Kehadiran Hari Ini -->
                                    <div class="small text-muted mb-1" title="Jumlah siswa yang sudah absen hari ini">
                                        <i class="fas fa-user-check text-success"></i> Hadir: 
                                        <span class="fw-bold text-dark">{{ $schedule->attendances_count ?? 0 }}</span>
                                    </div>
                                    
                                    <!-- Tombol Aksi -->
                                    <div class="d-flex gap-1">
                                        <!-- MENU ABSENSI -->
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm {{ $isActive ? 'btn-warning text-dark fw-bold' : 'btn-success' }} dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-camera me-1"></i> Absen
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><h6 class="dropdown-header">Metode Absensi</h6></li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ url('/schedule/manual', ['schedule_id' => $schedule->id]) }}">
                                                        <i class="fas fa-clipboard-list me-2 text-secondary"></i> Input Manual
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('scan.index', ['schedule_id' => $schedule->id]) }}">
                                                        <i class="fas fa-qrcode me-2 text-dark"></i> Scan QR Code
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('scan.face', ['schedule_id' => $schedule->id]) }}">
                                                        <i class="fas fa-user-circle me-2 text-primary"></i> Face ID
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="btn-group">
                                            <a href="{{ route('schedule.show', $schedule->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail Absensi">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('schedule.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jadwal ini? Data absensi terkait mungkin ikut terhapus.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus Jadwal" onclick="confirmDelete(this.form)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border text-center py-5">
                            <img src="https://img.icons8.com/ios/100/cccccc/calendar.png" width="60" class="mb-3 opacity-50">
                            <h5 class="text-muted fw-bold">Belum Ada Jadwal</h5>
                            <p class="text-muted mb-3">Anda belum membuat jadwal pelajaran. Silakan buat jadwal untuk mulai mengabsen.</p>
                            <a href="{{ route('schedule.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Buat Jadwal Sekarang
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <style>
        .hover-scale { transition: transform 0.2s, box-shadow 0.2s; }
        .hover-scale:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15)!important; }
        
        /* Animasi Pulse untuk Badge Live */
        @keyframes pulse-red {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .animate-pulse {
            animation: pulse-red 1.5s infinite;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(form) {
            Swal.fire({
                title: 'Hapus Jadwal?',
                text: "Jadwal yang dihapus tidak bisa dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        }
    </script>
</x-app-layout>