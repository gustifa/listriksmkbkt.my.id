@extends('layouts.app') <!-- Sesuaikan dengan nama layout master Anda -->

@section('content')
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Daftar Kegiatan</h2>
        </div>
        <div class="col-md-6 text-end">
            <!-- Tombol untuk memunculkan Modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKegiatan">
                + Tambah Kegiatan
            </button>
        </div>
    </div>

    <!-- Alert Sukses -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Alert Error Validasi -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabel Data Kegiatan -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Kegiatan</th>
                            <th>Tanggal</th>
                            <th>Kode Unik (QR)</th>
                            <th>Jumlah Hadir</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kegiatans as $index => $kegiatan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $kegiatan->nama_kegiatan }}</td>
                                <td>{{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d-m-Y') }}</td>
                                <td><span class="badge bg-info text-dark">{{ $kegiatan->kode_unik }}</span></td>
                                <td class="text-center">
                                    <span class="badge bg-primary total-hadir" data-id="{{ $kegiatan->id }}">
                                        <i class="fas fa-spinner fa-spin"></i> </span>
                                </td>
                                <td>{{ $kegiatan->deskripsi ?? '-' }}</td>
                                <td>
                                    <a href="{{route('kegiatan.show', $kegiatan->id)}}" class="btn btn-sm btn-success">Lihat Absensi</a>
                                    <a href="{{route('kegiatan.scan', $kegiatan->kode_unik)}}" class="btn btn-sm btn-info">Scan</a>
                                </td>
                            </tr>
                        @empty
                            @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL TAMBAH KEGIATAN -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambahKegiatan" tabindex="-1" aria-labelledby="modalTambahKegiatanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahKegiatanLabel">Tambah Kegiatan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('kegiatan.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    
                    <div class="mb-3">
                        <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required placeholder="Contoh: Rapat Paripurna">
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal Kegiatan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ old('tanggal') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Tambahkan keterangan jika perlu">{{ old('deskripsi') }}</textarea>
                    </div>

                    <div class="alert alert-info py-2" role="alert">
                        <small><b>Info:</b> Kode Unik untuk QR Code akan di-generate secara otomatis oleh sistem.</small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function refreshTotalHadir() {
        // Ambil semua ID kegiatan yang tampil di tabel
        let ids = [];
        $('.total-hadir').each(function() {
            ids.push($(this).data('id'));
        });

        if (ids.length > 0) {
            $.ajax({
                url: "{{ url('/api/kegiatan/total-hadir') }}",
                type: "GET",
                data: { ids: ids },
                success: function(response) {
                    // Update masing-masing baris berdasarkan response
                    $('.total-hadir').each(function() {
                        let id = $(this).data('id');
                        let total = response[id] ? response[id] : 0;
                        $(this).html('<i class="fas fa-users me-1"></i> ' + total);
                    });
                }
            });
        }
    }

    $(document).ready(function() {
        // Jalankan saat halaman pertama kali dimuat
        refreshTotalHadir();

        // Jalankan otomatis setiap 5 detik (5000ms)
        setInterval(refreshTotalHadir, 5000);
    });
</script>
@endsection