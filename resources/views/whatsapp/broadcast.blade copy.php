@section('title')
   Pesan Broadcast WA
@endsection
<x-app-layout>
    <div class="page-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="shadow card">
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
