@section('title', 'Kirim Pesan WhatsApp')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-primary fw-bold"><i class="fab fa-whatsapp me-2"></i> Kirim Pesan Manual</h4>
                    <a href="{{ route('whatsapp.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card shadow border-top border-4 border-success">
                    <div class="card-body p-4">
                        <form action="{{ route('whatsapp.send_process') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Pilih Gateway (Opsional, jika ingin spesifik) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Gateway (Pengirim)</label>
                                <select name="session_id" class="form-select">
                                    <option value="">-- Otomatis (Acak / Load Balancing) --</option>
                                    @foreach($gateways as $gw)
                                        <option value="{{ $gw->session_id }}" {{ $gw->status != 'connected' ? 'disabled' : '' }}>
                                            {{ $gw->name }} ({{ $gw->number ?? 'Offline' }}) 
                                            @if($gw->status != 'connected') [OFFLINE] @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Jika "Otomatis", sistem akan memilih gateway aktif secara acak.</small>
                            </div>

                            <!-- Nomor Tujuan -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nomor Tujuan</label>
                                <input type="text" name="target" class="form-control" placeholder="Contoh: 081234567890" required>
                                <small class="text-muted">Bisa dipisahkan koma untuk banyak nomor (0812xxx, 0813xxx).</small>
                            </div>

                            <!-- Pesan -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Isi Pesan</label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Tulis pesan Anda di sini..." required></textarea>
                            </div>

                            <!-- Tipe Pesan (Media) -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Lampiran Media (Opsional)</label>
                                <input type="file" name="media_file" class="form-control">
                                <small class="text-muted">Format: JPG, PNG, PDF (Max 2MB).</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg shadow">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Pesan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>