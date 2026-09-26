@section('title')
    Pesan Broadcast WA
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    
                    <div class="shadow card mb-4">
                        <div class="text-white card-header bg-primary py-3">
                            <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i> Broadcast Informasi Sekolah</h5>
                        </div>
                        <div class="card-body">

                            @if(session('success'))
                                <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                                    <div class="text-white">{{ session('success') }}</div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                                    <div class="text-white">{{ session('error') }}</div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('whatsapp.broadcast.send') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Pilih Gateway (Pengirim)</label>
                                    <select name="session_id" class="form-select border-primary">
                                        <option value="">-- Otomatis (Acak / Load Balancing) --</option>
                                        @foreach($gateways as $gw)
                                            <option value="{{ $gw->session_id }}" {{ $gw->status != 'connected' ? 'disabled' : '' }}>
                                                {{ $gw->name }} ({{ $gw->number ?? 'Offline' }}) 
                                                @if($gw->status != 'connected') [OFFLINE] @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted italic">Gateway yang offline tidak dapat dipilih.</small>
                                </div>

                                <hr>

                                <div class="mb-3 p-3 bg-light rounded border">
                                    <label class="form-label fw-bold d-block mb-2">Target Penerima <span class="text-danger">*</span></label>
                                    <div class="form-check form-check-inline me-4">
                                        <input class="form-check-input" type="radio" name="target_type" id="target_parents" value="parents" checked>
                                        <label class="form-check-label fw-bold" for="target_parents">Orang Tua Siswa (Per Kelas)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="target_teachers" value="teachers">
                                        <label class="form-check-label fw-bold" for="target_teachers">Seluruh Guru</label>
                                    </div>
                                </div>

                                <div class="mb-3" id="wrapper_classroom">
                                    <label class="form-label fw-bold">Pilih Kelas Tujuan <span class="text-danger">*</span></label>
                                    <select name="classroom_id" id="classroom_id" class="form-select select2 border-primary" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($classrooms as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Isi Pesan <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control border-primary" rows="6" placeholder="Tulis pengumuman di sini..." required></textarea>
                                    <div class="form-text">
                                        Tips: Gunakan <b>*teks*</b> untuk tebal, <b>_teks_</b> untuk miring.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Lampiran File (Opsional)</label>
                                    <input type="file" name="attachment" class="form-control">
                                    <div class="form-text">JPG, PNG, PDF, atau DOC (Max 5MB).</div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg shadow">
                                        <i class="fab fa-whatsapp me-2"></i> Kirim Broadcast Sekarang
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="shadow card">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-history me-2"></i> Riwayat Broadcast Terakhir</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0" id="example">
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
                                        @forelse($logs as $log)
                                        <tr>
                                            <td class="ps-4 small">{{ $log->created_at->format('d/m H:i') }}</td>
                                            <td class="fw-bold">{{ $log->recipient_number }}</td>
                                            <td><small class="text-truncate d-inline-block" style="max-width: 250px;">{{ $log->message }}</small></td>
                                            <td>
                                                @if($log->status == 'success')
                                                    <span class="badge bg-success">Terkirim</span>
                                                @else
                                                    <span class="badge bg-danger">Gagal</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <form action="{{ route('whatsapp.logs.delete', $log->id) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-link text-danger p-0"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted small">Belum ada riwayat pengiriman.</td>
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
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            $('.select2').select2({ theme: "bootstrap-5" });

            // Logika Tampilkan/Sembunyikan Kelas
            $('input[name="target_type"]').on('change', function() {
                if ($(this).val() === 'teachers') {
                    $('#wrapper_classroom').slideUp();
                    $('#classroom_id').removeAttr('required');
                } else {
                    $('#wrapper_classroom').slideDown();
                    $('#classroom_id').attr('required', 'required');
                }
            });
        });
    </script>
</x-app-layout>