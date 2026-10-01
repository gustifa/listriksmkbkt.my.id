@section('title', 'Absensi PKL')

<x-app-layout>
    <div class="page-content">

        <!-- HEADER INFO -->
        <div class="mb-4">
            <h4 class="fw-bold text-primary"><i class="fas fa-map-marker-alt me-2"></i>Absensi & Jurnal PKL</h4>
            <p class="mb-0 text-muted">{{ $internship->industry->name }}</p>
            <small class="text-secondary">{{ $internship->industry->address }}</small>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="row">
            <!-- KOLOM KIRI: FORM ABSENSI -->
            <div class="mb-4 col-md-5">
                <div class="border-0 shadow-sm card h-100">
                    <div class="py-3 bg-white card-header">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-clock me-2"></i>Input Absensi Hari Ini</h6>
                        <small class="text-muted">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</small>
                    </div>

                    <div class="card-body">

                        {{-- LOGIKA TAMPILAN BERDASARKAN STATUS ABSENSI --}}

                        @if(!$todayAttendance)
                            <!-- 1. FORM ABSEN DATANG (BELUM ABSEN) -->
                            <div class="text-start">
                                <h5 class="mb-3 fw-bold text-primary">👋 Selamat Pagi! Silakan Absen Datang.</h5>
                                <form action="{{ route('student.internships.attendance.store') }}" method="POST" enctype="multipart/form-data" id="checkInForm">
                                    @csrf
                                    <input type="hidden" name="type" value="check_in">
                                    <input type="hidden" name="latitude" id="lat_in">
                                    <input type="hidden" name="longitude" id="long_in">

                                    <!-- Pilihan Status -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Status Kehadiran</label>
                                        <select name="status" class="form-select" id="statusSelect" required onchange="toggleForm()">
                                            <option value="present">Hadir (PKL)</option>
                                            <option value="sick">Sakit</option>
                                            <option value="permit">Izin</option>
                                        </select>
                                    </div>

                                    <!-- Foto Selfie Masuk -->
                                    <div class="mb-3" id="photoSection">
                                        <label class="form-label fw-bold small">Foto Selfie Datang <span class="text-danger">*</span></label>
                                        <input type="file" name="photo" class="form-control" accept="image/*" capture="user">
                                        <div class="form-text small">Ambil foto selfie di lokasi PKL.</div>
                                    </div>

                                    <!-- Info Lokasi -->
                                    <div class="mb-3 location-status" style="display:none;">
                                        <div class="d-flex align-items-center text-success small">
                                            <i class="fas fa-map-marked-alt me-2"></i> <span>Lokasi terdeteksi</span>
                                        </div>
                                    </div>

                                    <button type="submit" class="py-2 btn btn-primary w-100 fw-bold">
                                        <i class="fas fa-sign-in-alt me-2"></i> Absen Datang
                                    </button>
                                </form>
                            </div>

                        @elseif($todayAttendance && !$todayAttendance->check_out_time && $todayAttendance->status == 'present')
                            <!-- 2. FORM ABSEN PULANG (SUDAH DATANG, BELUM PULANG) -->
                            <div class="text-start">
                                <!-- Info Datang -->
                                <div class="mb-4 border-0 shadow-sm alert alert-success">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-check-circle fs-1 text-success"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted fw-bold text-uppercase">Sudah Absen Datang</small>
                                            <h4 class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($todayAttendance->time)->format('H:i') }} WIB</h4>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-3 fw-bold text-warning">🕒 Saatnya Pulang? Isi Jurnal Dulu.</h5>

                                <form action="{{ route('student.internships.attendance.store') }}" method="POST" enctype="multipart/form-data" id="checkOutForm">
                                    @csrf
                                    <input type="hidden" name="type" value="check_out">
                                    <input type="hidden" name="attendance_id" value="{{ $todayAttendance->id }}">
                                    <input type="hidden" name="latitude" id="lat_out">
                                    <input type="hidden" name="longitude" id="long_out">

                                    <!-- Jurnal Kegiatan -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Jurnal Kegiatan Hari Ini <span class="text-danger">*</span></label>
                                        <textarea name="activity_log" class="form-control" rows="4" placeholder="Jelaskan detail pekerjaan/kegiatan yang Anda lakukan hari ini..." required minlength="10"></textarea>
                                    </div>

                                    <!-- Foto Pulang -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Foto Kegiatan/Pulang (Opsional)</label>
                                        <input type="file" name="photo_out" class="form-control" accept="image/*" capture="user">
                                    </div>

                                    <!-- Info Lokasi -->
                                    <div class="mb-3 location-status" style="display:none;">
                                        <div class="d-flex align-items-center text-success small">
                                            <i class="fas fa-map-marked-alt me-2"></i> <span>Lokasi terdeteksi</span>
                                        </div>
                                    </div>

                                    <button type="submit" class="py-2 btn btn-warning w-100 fw-bold text-dark">
                                        <i class="fas fa-sign-out-alt me-2"></i> Absen Pulang & Simpan Jurnal
                                    </button>
                                </form>
                            </div>

                        @else
                            <!-- 3. SELESAI (SUDAH PULANG ATAU IZIN/SAKIT) -->
                            <div class="py-4 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-clipboard-check text-primary" style="font-size: 4rem;"></i>
                                </div>
                                <h5 class="fw-bold">Absensi Hari Ini Selesai</h5>
                                <p class="text-muted">Data kehadiran dan jurnal telah tersimpan.</p>

                                <div class="mt-4 row g-2 text-start">
                                    <div class="col-6">
                                        <div class="p-3 border rounded bg-light h-100">
                                            <small class="d-block text-muted fw-bold">JAM DATANG</small>
                                            <span class="fs-5 fw-bold text-success">{{ \Carbon\Carbon::parse($todayAttendance->time)->format('H:i') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 border rounded bg-light h-100">
                                            <small class="d-block text-muted fw-bold">JAM PULANG</small>
                                            <span class="fs-5 fw-bold text-danger">
                                                {{ $todayAttendance->check_out_time ? \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i') : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 border rounded bg-light">
                                            <small class="mb-1 d-block text-muted fw-bold">JURNAL KEGIATAN</small>
                                            <p class="mb-0 small fst-italic">"{{ $todayAttendance->activity_log ?? 'Tidak ada jurnal (Sakit/Izin)' }}"</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: RIWAYAT + PENCARIAN -->
            <div class="col-md-7">
                <div class="border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header">
                        <div class="row align-items-center g-2">
                            <div class="col-md-5">
                                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-1"></i> Riwayat Kehadiran</h6>
                            </div>
                            <!-- INPUT PENCARIAN -->
                            <div class="col-md-7">
                                <div class="input-group input-group-sm">
                                    <span class="bg-light input-group-text"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" id="searchAttendance" class="form-control" placeholder="Cari tanggal, tahun, atau jurnal...">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-0 card-body">
                        <!-- Scrollable Container jika data sangat banyak -->
                        <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                            <table class="table mb-0 align-middle table-hover" id="attendanceTable">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Tanggal & Tahun</th>
                                        <th class="text-center">Jam</th>
                                        <th>Jurnal</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($history as $item)
                                    <tr class="attendance-row">
                                        <!-- TANGGAL LENGKAP DENGAN TAHUN -->
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">
                                                {{ \Carbon\Carbon::parse($item->date)->translatedFormat('d F Y') }}
                                            </div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('l') }}</small>
                                        </td>
                                        
                                        <!-- JAM MASUK & PULANG -->
                                        <td class="text-center small">
                                            <span class="d-block text-success fw-semibold"><i class="fas fa-arrow-down fs-7 me-1"></i>{{ \Carbon\Carbon::parse($item->time)->format('H:i') }}</span>
                                            <span class="d-block text-danger fw-semibold"><i class="fas fa-arrow-up fs-7 me-1"></i>{{ $item->check_out_time ? \Carbon\Carbon::parse($item->check_out_time)->format('H:i') : '-' }}</span>
                                        </td>

                                        <!-- JURNAL -->
                                        <td>
                                            <span class="d-inline-block text-truncate small text-muted search-jurnal" style="max-width: 180px;" title="{{ $item->activity_log }}">
                                                {{ $item->activity_log ?? '-' }}
                                            </span>
                                        </td>

                                        <!-- STATUS -->
                                        <td class="text-center">
                                            @if($item->status == 'present')
                                                <span class="badge bg-success">Hadir</span>
                                            @elseif($item->status == 'sick')
                                                <span class="badge bg-warning text-dark">Sakit</span>
                                            @elseif($item->status == 'permit')
                                                <span class="badge bg-info">Izin</span>
                                            @else
                                                <span class="badge bg-danger">Alpa</span>
                                            @endif

                                            @if($item->photo_path)
                                            <button class="p-0 btn btn-sm btn-link text-secondary ms-1" onclick="showPhoto('{{ asset('storage/'.$item->photo_path) }}')">
                                                <i class="fas fa-image"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="emptyRow">
                                        <td colspan="4" class="py-4 text-center text-muted">Belum ada riwayat absensi.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Lihat Foto -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="p-0 modal-body">
                    <img src="" id="modalImage" class="img-fluid w-100">
                </div>
                <div class="p-2 modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary w-100" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // 1. Fitur Pencarian Realtime
        document.getElementById('searchAttendance')?.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#attendanceTable tbody tr.attendance-row');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // 2. Geolocator Logic
        document.addEventListener('DOMContentLoaded', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const latIn = document.getElementById('lat_in');
                    const longIn = document.getElementById('long_in');
                    if(latIn) latIn.value = position.coords.latitude;
                    if(longIn) longIn.value = position.coords.longitude;

                    const latOut = document.getElementById('lat_out');
                    const longOut = document.getElementById('long_out');
                    if(latOut) latOut.value = position.coords.latitude;
                    if(longOut) longOut.value = position.coords.longitude;

                    document.querySelectorAll('.location-status').forEach(el => el.style.display = 'block');
                }, function(error) {
                    console.log("GPS Error: " + error.message);
                });
            }
        });

        // 3. Toggle Form berdasarkan Status
        function toggleForm() {
            const status = document.getElementById('statusSelect');
            if(!status) return;

            const photoInput = document.querySelector('input[name="photo"]');

            if (status.value === 'present') {
                document.getElementById('photoSection').style.display = 'block';
                if(photoInput) photoInput.required = true;
            } else {
                document.getElementById('photoSection').style.display = 'none';
                if(photoInput) photoInput.required = false;
            }
        }

        // 4. Show Photo Modal
        function showPhoto(url) {
            document.getElementById('modalImage').src = url;
            new bootstrap.Modal(document.getElementById('photoModal')).show();
        }
    </script>
    @endpush
</x-app-layout>