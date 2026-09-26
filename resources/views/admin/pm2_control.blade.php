@section('title', 'Server Process Manager (PM2)')

<x-app-layout>
    <div class="page-content">
        <div class="container-fluid">
            
            <div class="mb-4">
                <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-server me-2"></i> Server Process Manager</h4>
                <p class="text-muted small">Mengelola Queue Worker & WhatsApp Bot via PM2.</p>
            </div>

            <!-- Notifikasi Sukses -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2 fs-4"></i>
                        <div>
                            <strong>Berhasil!</strong><br>
                            {{ session('success') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Notifikasi Error -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle me-2 mt-1 fs-4"></i>
                        <div>
                            <strong>Terjadi Kesalahan:</strong><br>
                            {!! nl2br(e(session('error'))) !!}
                            
                            @if(str_contains(session('error'), '1060'))
                                <div class="mt-2 small bg-white p-2 rounded text-danger border border-danger">
                                    <strong>PENTING - Mengenai Error 1060 (Service Not Found):</strong><br>
                                    <ul class="mb-0 ps-3">
                                        <li>Library <code>pm2-windows-startup</code> mendaftarkan PM2 di <strong>Registry Startup</strong>, bukan sebagai <em>Windows Service</em> asli.</li>
                                        <li>Oleh karena itu, PM2 <strong>TIDAK AKAN MUNCUL</strong> di <code>services.msc</code>, sehingga perintah rename service gagal.</li>
                                        <li>Namun, PM2 <strong>SUDAH BERHASIL</strong> diatur untuk jalan otomatis saat Login.</li>
                                        <li>Untuk mengeceknya: Buka <strong>Task Manager</strong> &gt; Tab <strong>Startup</strong> &gt; Cari <strong>pm2-init.sh</strong> atau node.</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Panel Kontrol Utama -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow border-0 h-100">
                        <div class="card-header bg-dark text-white fw-bold">
                            <i class="fas fa-gamepad me-2"></i> Kontrol Aksi
                        </div>
                        <div class="card-body d-grid gap-3">
                            <p class="small text-muted mb-0">Lokasi Project: <code>{{ base_path() }}</code></p>
                            
                            <form action="{{ route('pm2.start') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 text-start">
                                    <i class="fas fa-play me-2"></i> Start Ecosystem
                                </button>
                            </form>

                            <form action="{{ route('pm2.restart') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100 text-start text-dark">
                                    <i class="fas fa-sync me-2"></i> Restart All
                                </button>
                            </form>

                            <form action="{{ route('pm2.stop') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100 text-start">
                                    <i class="fas fa-stop me-2"></i> Stop All
                                </button>
                            </form>
                            
                            <hr>
                            
                            <form action="{{ route('pm2.delete') }}" method="POST" onsubmit="return confirm('Hapus semua proses dari list PM2?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 text-start">
                                    <i class="fas fa-trash me-2"></i> Delete All Process
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Panel Status Terminal -->
                <div class="col-md-8 mb-4">
                    <div class="card shadow border-0 h-100">
                        <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-terminal me-2"></i> Status Output</span>
                            <a href="{{ route('pm2.index') }}" class="btn btn-sm btn-light"><i class="fas fa-sync"></i> Refresh</a>
                        </div>
                        <div class="card-body bg-black p-0">
                            <pre class="text-success p-3 mb-0" style="font-family: 'Consolas', monospace; font-size: 0.85rem; height: 350px; overflow-y: auto;">
@if($status)
{{ $status }}
@else
PM2 belum berjalan atau tidak terdeteksi. Klik 'Start Ecosystem' atau cek konfigurasi Path PM2.
@endif
                            </pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Konfigurasi Lanjutan (Service Windows) -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow border-0 border-top border-4 border-info">
                        <div class="card-header bg-white fw-bold text-primary">
                            <i class="fas fa-cogs me-2"></i> Konfigurasi Startup (Otomatis Jalan)
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 border-end">
                                    <h6 class="fw-bold mb-3">Otomatisasi Startup</h6>
                                    
                                    <div class="alert alert-light border border-info p-2 mb-3 small">
                                        <i class="fas fa-info-circle text-info me-1"></i>
                                        Fitur ini mendaftarkan PM2 ke <strong>Registry Startup</strong> Windows.
                                        Aplikasi akan jalan otomatis saat user <strong>Login</strong> ke Windows.
                                    </div>

                                    <div class="d-grid gap-2">
                                        <!-- Tombol Save -->
                                        <form action="{{ route('pm2.save') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary w-100 text-start d-flex justify-content-between align-items-center" title="Simpan daftar proses saat ini">
                                                <span><i class="fas fa-save me-2"></i> 1. Simpan Proses (PM2 Save)</span>
                                                <i class="fas fa-chevron-right small"></i>
                                            </button>
                                        </form>

                                        <!-- Form Install Service -->
                                        <form action="{{ route('pm2.install_service') }}" method="POST" class="border p-2 rounded bg-light">
                                            @csrf
                                            <label class="small fw-bold mb-1">2. Install Startup:</label>
                                            <div class="input-group input-group-sm mb-1">
                                                <!-- Input nama service disembunyikan/disabled karena tidak relevan untuk registry startup -->
                                                <input type="text" name="service_name" class="form-control" placeholder="Nama Service (Tidak dipakai)" value="" disabled>
                                                <button type="submit" class="btn btn-info text-white" onclick="return confirm('Install PM2 Startup? Pastikan PHP memiliki hak akses Administrator.');">
                                                    Install
                                                </button>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.7rem;">*Cek di Task Manager > Startup setelah install.</small>
                                        </form>

                                        <!-- Tombol Uninstall Service -->
                                        <form action="{{ route('pm2.uninstall_service') }}" method="POST" onsubmit="return confirm('Hapus Startup?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-times me-2"></i> 3. Hapus Startup</span>
                                                <i class="fas fa-chevron-right small"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 ps-md-4 mt-3 mt-md-0">
                                    <h6 class="fw-bold text-danger mb-3"><i class="fas fa-tools me-1"></i> Mode Manual (Terminal)</h6>
                                    <p class="small text-muted mb-2">
                                        Jika tombol di samping gagal, jalankan perintah ini di <strong>CMD (Administrator)</strong>:
                                    </p>
                                    
                                    <div class="bg-dark text-light p-3 rounded small font-monospace position-relative">
                                        <div class="mb-2">
                                            <span class="text-muted">// 1. Pindah ke folder project</span><br>
                                            <span class="text-warning">cd</span> {{ base_path() }}
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">// 2. Simpan konfigurasi saat ini</span><br>
                                            <span class="text-warning">pm2</span> save
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">// 3. Install Library</span><br>
                                            <span class="text-warning">npm</span> install -g pm2-windows-startup
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">// 4. Install Startup (Registry)</span><br>
                                            <span class="text-warning">pm2-startup</span> install
                                        </div>
                                        <div>
                                            <span class="text-muted">// 5. Cek Keberhasilan</span><br>
                                            Buka <strong>Task Manager</strong> > Tab <strong>Startup</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>