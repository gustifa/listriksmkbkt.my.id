@section('title', 'Manajemen Perangkat Scanner & CCTV')

<x-app-layout>
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-video me-2"></i> Scanner & CCTV Manager</h4>
                    <p class="text-muted">Kelola Kiosk, QR Scanner, dan CCTV Face Recognition.</p>
                </div>
                <div>
                    <!-- Menu Scan Camera -->
                    <a href="{{ url('/scan-camera') }}" class="shadow-sm btn btn-success me-2" target="_blank">
                        <i class="fas fa-camera me-1"></i> Scan Camera
                    </a>

                    <!-- Tombol Tambah Perangkat -->
                    <button class="shadow-sm btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="fas fa-plus me-1"></i> Tambah Perangkat Baru
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- List Devices -->
            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th>Nama Perangkat</th>
                                    <th>Token (ID)</th>
                                    <th>Mode</th>
                                    <th>Config CCTV</th>
                                    <th>Terakhir Aktif</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devices as $device)
                                    <tr>
                                        <td>
                                            @php
                                                // Cek jika aktif dalam 5 menit terakhir
                                                $isOnline = $device->last_active_at && \Carbon\Carbon::parse($device->last_active_at)->diffInMinutes(now()) < 5;
                                            @endphp
                                            @if($isOnline)
                                                <span class="badge bg-success">ONLINE</span>
                                            @else
                                                <span class="badge bg-secondary">OFFLINE</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $device->device_name }}</td>
                                        <td><code class="text-primary">{{ $device->device_token }}</code></td>
                                        <td>
                                            @if($device->mode == 'qr') <span class="badge bg-info">QR Only</span>
                                            @elseif($device->mode == 'face') <span class="badge bg-warning text-dark">Face Only</span>
                                            @else <span class="badge bg-primary">Hybrid</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($device->rtsp_url)
                                                <i class="fas fa-check-circle text-success" title="{{ $device->rtsp_url }}"></i> Terkonfigurasi
                                            @else
                                                <i class="fas fa-times-circle text-muted"></i> Kosong
                                            @endif
                                        </td>
                                        <td>
                                            {{ $device->last_active_at ? \Carbon\Carbon::parse($device->last_active_at)->diffForHumans() : '-' }}
                                        </td>
                                        <td>
                                            <button class="text-white btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $device->id }}">
                                                <i class="fas fa-cog"></i> Config
                                            </button>
                                            <form action="{{ route('scanner-devices.destroy', $device->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus perangkat ini? Token di Kiosk akan invalid.');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- MODAL CONFIG (EDIT) -->
                                    <div class="modal fade" id="editModal{{ $device->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="text-white modal-header bg-dark">
                                                    <h5 class="modal-title">Konfigurasi: {{ $device->device_name }}</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('scanner-devices.update', $device->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Nama Perangkat</label>
                                                            <input type="text" name="device_name" class="form-control" value="{{ $device->device_name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Mode Operasi</label>
                                                            <select name="mode" class="form-select">
                                                                <option value="qr" {{ $device->mode == 'qr' ? 'selected' : '' }}>QR Code Scanner</option>
                                                                <option value="face" {{ $device->mode == 'face' ? 'selected' : '' }}>Face Recognition (CCTV)</option>
                                                                <option value="hybrid" {{ $device->mode == 'hybrid' ? 'selected' : '' }}>Hybrid (QR + Face)</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">RTSP URL (CCTV)</label>
                                                            <input type="text" name="rtsp_url" class="form-control" value="{{ $device->rtsp_url }}" placeholder="rtsp://admin:pass@ip:554/stream">
                                                            <small class="text-muted">Isi jika menggunakan mode Face Recognition.</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Keterangan Lokasi</label>
                                                            <textarea name="description" class="form-control" rows="2">{{ $device->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-5 text-center">
                                            <img src="https://img.icons8.com/ios/100/cccccc/web-camera.png" width="60" class="mb-3 opacity-50">
                                            <p class="text-muted">Belum ada perangkat yang terdaftar.</p>
                                            <p class="small text-muted">Buka halaman Kiosk di React dan lakukan Registrasi Perangkat.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MODAL CREATE (TAMBAH BARU) -->
            <div class="modal fade" id="createModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="text-white modal-header bg-primary">
                            <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Tambah Perangkat Baru</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('scanner-devices.store') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Perangkat</label>
                                    <input type="text" name="device_name" class="form-control" placeholder="Contoh: CCTV Gerbang Utama" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mode Operasi</label>
                                    <select name="mode" class="form-select">
                                        <option value="qr">QR Code Scanner</option>
                                        <option value="face">Face Recognition (CCTV)</option>
                                        <option value="hybrid">Hybrid (QR + Face)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">RTSP URL (CCTV)</label>
                                    <input type="text" name="rtsp_url" class="form-control" placeholder="rtsp://admin:pass@ip:554/stream">
                                    <small class="text-muted">Opsional. Wajib diisi jika mode Face Recognition.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keterangan Lokasi</label>
                                    <textarea name="description" class="form-control" rows="2" placeholder="Lokasi pemasangan..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan & Generate Token</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
