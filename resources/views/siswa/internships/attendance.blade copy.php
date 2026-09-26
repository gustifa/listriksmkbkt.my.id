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
                        @if($todayAttendance)
                            <!-- TAMPILAN JIKA SUDAH ABSEN -->
                            <div class="py-4 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h5 class="fw-bold">Anda Sudah Absen Hari Ini</h5>
                                <p class="text-muted">Terima kasih telah mengisi jurnal kegiatan.</p>

                                <div class="mt-3 border alert alert-light text-start">
                                    <small class="d-block fw-bold text-muted">Status:</small>
                                    <span class="badge bg-{{ $todayAttendance->status == 'present' ? 'success' : 'warning' }} mb-2">
                                        {{ ucfirst($todayAttendance->status == 'present' ? 'Hadir' : $todayAttendance->status) }}
                                    </span>

                                    <small class="mt-2 d-block fw-bold text-muted">Jam:</small>
                                    <span>{{ \Carbon\Carbon::parse($todayAttendance->time)->format('H:i') }} WIB</span>

                                    <small class="mt-2 d-block fw-bold text-muted">Jurnal:</small>
                                    <p class="mb-0 small fst-italic">"{{ $todayAttendance->activity_log }}"</p>
                                </div>
                            </div>
                        @else
                            <!-- FORM ABSENSI -->
                            <form action="{{ route('student.internships.attendance.store') }}" method="POST" enctype="multipart/form-data" id="attendanceForm">
                                @csrf
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">

                                <!-- Pilihan Status -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Status Kehadiran</label>
                                    <select name="status" class="form-select" id="statusSelect" required onchange="toggleForm()">
                                        <option value="present">Hadir</option>
                                        <option value="sick">Sakit</option>
                                        <option value="permit">Izin</option>
                                    </select>
                                </div>

                                <!-- Foto Selfie (Hanya jika Hadir) -->
                                <div class="mb-3" id="photoSection">
                                    <label class="form-label fw-bold small">Foto Selfie di Lokasi <span class="text-danger">*</span></label>
                                    <input type="file" name="photo" class="form-control" accept="image/*" capture="user">
                                    <div class="form-text small">Ambil foto selfie menggunakan seragam/wearpack.</div>
                                </div>

                                <!-- Jurnal Kegiatan -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Jurnal Kegiatan Harian <span class="text-danger">*</span></label>
                                    <textarea name="activity_log" class="form-control" rows="4" placeholder="Ceritakan apa yang Anda kerjakan hari ini..." required minlength="10"></textarea>
                                </div>

                                <!-- Info Lokasi -->
                                <div class="mb-3" id="locationInfo" style="display:none;">
                                    <div class="d-flex align-items-center text-success small">
                                        <i class="fas fa-map-marked-alt me-2"></i>
                                        <span>Lokasi terdeteksi</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 fw-bold" id="btnSubmit">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Absensi
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: RIWAYAT -->
            <div class="col-md-7">
                <div class="border-0 shadow-sm card">
                    <div class="py-3 bg-white card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark">Riwayat Kehadiran (Terakhir)</h6>
                        <a href="#" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Tanggal</th>
                                        <th>Status</th>
                                        <th>Jurnal Kegiatan</th>
                                        <th class="text-center">Bukti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($history as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($item->time)->format('H:i') }} WIB</small>
                                        </td>
                                        <td>
                                            @if($item->status == 'present')
                                                <span class="badge bg-success">Hadir</span>
                                            @elseif($item->status == 'sick')
                                                <span class="badge bg-warning text-dark">Sakit</span>
                                            @else
                                                <span class="badge bg-info">Izin</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                                {{ $item->activity_log }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($item->photo_path)
                                                <button class="border btn btn-sm btn-light" onclick="showPhoto('{{ asset('storage/'.$item->photo_path) }}')">
                                                    <i class="fas fa-image"></i>
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
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
        // 1. Geolocator Logic
        document.addEventListener('DOMContentLoaded', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    document.getElementById('locationInfo').style.display = 'block';
                }, function(error) {
                    console.log("GPS Error: " + error.message);
                    // Opsional: Beritahu user GPS perlu aktif
                });
            }
        });

        // 2. Toggle Form berdasarkan Status (Jika Sakit/Izin, tidak wajib foto)
        function toggleForm() {
            const status = document.getElementById('statusSelect').value;
            const photoInput = document.querySelector('input[name="photo"]');

            if (status === 'present') {
                document.getElementById('photoSection').style.display = 'block';
                photoInput.required = true;
            } else {
                document.getElementById('photoSection').style.display = 'none';
                photoInput.required = false;
            }
        }

        // 3. Show Photo Modal
        function showPhoto(url) {
            document.getElementById('modalImage').src = url;
            new bootstrap.Modal(document.getElementById('photoModal')).show();
        }
    </script>
    @endpush

</x-app-layout>
