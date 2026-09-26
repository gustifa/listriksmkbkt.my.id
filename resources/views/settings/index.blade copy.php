@section('title')
   Pengaturan Kop Surat & Sekolah
@endsection
<x-app-layout>
    <div class="page-content">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i> Pengaturan Aplikasi</h5>
                </div>
                <div class="card-body">
                    
                    <!-- @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif -->

                    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- NAV TABS -->
                        <ul class="nav nav-tabs mb-4" id="settingTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
                                    <i class="fas fa-school me-2"></i> Identitas Sekolah
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold" id="report-tab" data-bs-toggle="tab" data-bs-target="#report" type="button">
                                    <i class="fas fa-file-pdf me-2"></i> Kertas & Tanda Tangan
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="settingTabsContent">
                            
                            <!-- TAB 1: IDENTITAS SEKOLAH -->
                            <div class="tab-pane fade show active" id="general">
                                <div class="row mb-4 border-bottom pb-4">
                                    <!-- Logo Kiri -->
                                    <div class="col-md-6 text-center border-end">
                                        <label class="form-label fw-bold">Logo Kiri (Utama)</label>
                                        <div class="mb-2 d-flex justify-content-center">
                                            @if(isset($settings['logo_left']) && $settings['logo_left'])
                                                <img src="{{ asset('storage/'.$settings['logo_left']) }}" alt="Logo Kiri" class="img-fluid" style="max-height: 80px; border: 1px solid #ddd; padding: 5px;">
                                            @else
                                                <div class="text-muted border p-3 rounded" style="height: 80px; width: 80px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                    <i class="fas fa-image fa-2x text-secondary"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <input type="file" name="logo_left" class="form-control form-control-sm accept-image">
                                        <small class="text-muted">Format: PNG/JPG (Max 2MB)</small>
                                    </div>

                                    <!-- Logo Kanan -->
                                    <div class="col-md-6 text-center">
                                        <label class="form-label fw-bold">Logo Kanan (Opsional)</label>
                                        <div class="mb-2 d-flex justify-content-center">
                                            @if(isset($settings['logo_right']) && $settings['logo_right'])
                                                <img src="{{ asset('storage/'.$settings['logo_right']) }}" alt="Logo Kanan" class="img-fluid" style="max-height: 80px; border: 1px solid #ddd; padding: 5px;">
                                            @else
                                                <div class="text-muted border p-3 rounded" style="height: 80px; width: 80px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                    <i class="fas fa-image fa-2x text-secondary"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <input type="file" name="logo_right" class="form-control form-control-sm accept-image">
                                        <small class="text-muted">Format: PNG/JPG (Max 2MB)</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Sekolah</label>
                                    <input type="text" name="school_name" class="form-control" value="{{ $settings['school_name'] ?? '' }}" placeholder="Contoh: SMK NEGERI 1 JAKARTA" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Alamat Lengkap</label>
                                    <textarea name="school_address" class="form-control" rows="2" placeholder="Contoh: Jl. Budi Utomo No. 1, Jakarta Pusat">{{ $settings['school_address'] ?? '' }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">No. Telepon / Fax</label>
                                        <input type="text" name="school_phone" class="form-control" value="{{ $settings['school_phone'] ?? '' }}" placeholder="(021) 123-4567">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Alamat Email</label>
                                        <input type="email" name="school_email" class="form-control" value="{{ $settings['school_email'] ?? '' }}" placeholder="info@sekolah.sch.id">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Website Sekolah</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                        <input type="text" name="school_web" class="form-control" value="{{ $settings['school_web'] ?? '' }}" placeholder="www.sekolah.sch.id">
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: PENGATURAN LAPORAN -->
                            <div class="tab-pane fade" id="report">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Format Kertas PDF</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Ukuran Kertas</label>
                                        <select name="paper_size" class="form-select">
                                            <option value="a4" {{ ($settings['paper_size'] ?? '') == 'a4' ? 'selected' : '' }}>A4</option>
                                            <option value="f4" {{ ($settings['paper_size'] ?? '') == 'f4' ? 'selected' : '' }}>F4 / Folio</option>
                                            <option value="letter" {{ ($settings['paper_size'] ?? '') == 'letter' ? 'selected' : '' }}>Letter</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Orientasi</label>
                                        <select name="paper_orientation" class="form-select">
                                            <option value="portrait" {{ ($settings['paper_orientation'] ?? '') == 'portrait' ? 'selected' : '' }}>Portrait (Tegak)</option>
                                            <option value="landscape" {{ ($settings['paper_orientation'] ?? '') == 'landscape' ? 'selected' : '' }}>Landscape (Mendatar)</option>
                                        </select>
                                    </div>
                                </div>

                                <label class="form-label fw-bold mt-2">Margin (cm)</label>
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <small>Atas</small>
                                        <input type="number" step="0.1" name="margin_top" class="form-control" value="{{ $settings['margin_top'] ?? '2.5' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <small>Bawah</small>
                                        <input type="number" step="0.1" name="margin_bottom" class="form-control" value="{{ $settings['margin_bottom'] ?? '2.5' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <small>Kiri</small>
                                        <input type="number" step="0.1" name="margin_left" class="form-control" value="{{ $settings['margin_left'] ?? '2.5' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <small>Kanan</small>
                                        <input type="number" step="0.1" name="margin_right" class="form-control" value="{{ $settings['margin_right'] ?? '2.5' }}">
                                    </div>
                                </div>

                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4">Tanda Tangan Laporan</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Kota/Kabupaten</label>
                                        <input type="text" name="signature_city" class="form-control" value="{{ $settings['signature_city'] ?? 'Jakarta' }}" placeholder="Contoh: Jakarta">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Jabatan Penandatangan</label>
                                        <input type="text" name="signature_title" class="form-control" value="{{ $settings['signature_title'] ?? 'Kepala Sekolah' }}" placeholder="Contoh: Kepala Sekolah">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Nama Penandatangan</label>
                                        <input type="text" name="signature_name" class="form-control" value="{{ $settings['signature_name'] ?? '' }}" placeholder="Nama Lengkap & Gelar">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">NIP / NUPTK</label>
                                        <input type="text" name="signature_nip" class="form-control" value="{{ $settings['signature_nip'] ?? '' }}" placeholder="-">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Simpan Semua Pengaturan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Cek apakah ada session 'success' yang dikirim dari controller
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000 // Notifikasi hilang otomatis setelah 2 detik
            });
        @endif

        // Opsional: Cek jika ada error validasi
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Mohon periksa kembali inputan Anda.',
            });
        @endif
    </script>
</x-app-layout>
