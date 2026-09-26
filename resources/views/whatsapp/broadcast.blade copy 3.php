@section('title')
    Pesan Broadcast WA
@endsection
<x-app-layout>
    <div class="page-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    
                    <!-- KARTU FORM BROADCAST -->
                    <div class="shadow card mb-4">
                        <div class="text-white card-header bg-primary">
                            <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i> Broadcast Informasi Sekolah</h5>
                        </div>
                        <div class="card-body">

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <div class="border alert alert-light">
                                <i class="fas fa-info-circle text-primary"></i>
                                Pesan akan dikirim ke <strong>WhatsApp Orang Tua</strong> sesuai kelas yang dipilih.
                            </div>

                            <form action="{{ route('whatsapp.broadcast.send') }}" method="POST" enctype="multipart/form-data">
                                @csrf

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

                                <!-- Pilih Kelas -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih Kelas Tujuan <span class="text-danger">*</span></label>
                                    <select name="classroom_id" class="form-select select2" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($classrooms as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- <div class="mb-3">
                                    <label class="form-label fw-bold">NIS/ID Siswa <span class="text-danger">*</span></label>
                                    <input type="text" name="student_id_manual" class="form-control" placeholder="Masukkan NIS atau ID Siswa">
                                    <small class="form-text text-muted">Isi ID siswa yang ingin diabsen manual.</small>
                                </div> -->

                                <!-- Isi Pesan -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Isi Pesan <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control" rows="6" placeholder="Tulis pengumuman di sini..." required></textarea>
                                    <div class="form-text">
                                        Tips: Gunakan tanda bintang (*) untuk menebalkan teks. Contoh: *PENGUMUMAN PENTING*
                                    </div>
                                </div>

                                <!-- Lampiran File -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Lampiran File (Opsional)</label>
                                    <input type="file" name="attachment" class="form-control">
                                    <div class="form-text">
                                        Bisa berupa Gambar (JPG/PNG) atau Dokumen (PDF/DOC). Maksimal 5MB.
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fab fa-whatsapp me-2"></i> Kirim Siaran
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>

                    <!-- KARTU LOG PESAN TERKIRIM (FITUR BARU) -->
                    <div class="shadow card border-top border-4 border-info">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-info"><i class="fas fa-history me-2"></i> Riwayat Pesan Terkirim</h5>
                            
                            @if(isset($logs) && $logs->count() > 0)
                                <form action="{{ route('whatsapp.logs.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA riwayat pesan?');">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash me-1"></i> Bersihkan Semua
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Waktu</th>
                                            <th>Tujuan</th>
                                            <th>Pesan</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($logs) && $logs->count() > 0)
                                            @foreach($logs as $log)
                                                <tr>
                                                    <td class="ps-4 small text-muted">{{ $log->created_at->format('d M H:i') }}</td>
                                                    <td class="fw-bold">{{ $log->recipient_number }}</td>
                                                    <td>
                                                        <div class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $log->message }}">
                                                            {{ $log->message }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($log->status == 'success')
                                                            <span class="badge bg-success"><i class="fas fa-check"></i> Sukses</span>
                                                        @elseif($log->status == 'failed' || $log->status == 'error')
                                                            <span class="badge bg-danger"><i class="fas fa-times"></i> Gagal</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $log->status }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <form action="{{ route('whatsapp.logs.delete', $log->id) }}" method="POST" onsubmit="return confirm('Hapus log pesan ini?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Log">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                                    Belum ada riwayat pesan broadcast.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.select2').select2({ theme: "bootstrap-5" });
            });
        </script>
    </div>
</x-app-layout>