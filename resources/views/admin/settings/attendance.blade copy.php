@section('title')
   Pengaturan Kop Surat & Sekolah
@endsection
<x-app-layout>
    <div class="page-content">
            <div class="col-md-12">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary">
                        <h5 class="mb-0"><i class="fas fa-school me-2"></i> Pengaturan Jam Absensi</h5>
                    </div>
                    <form action="{{ route('update.attendance') }}" method="POST">
                        @csrf
                        <div class="card">
                            <div class="card-body">

                                <div class="mb-3 form-group">
                                    <label>Batas Jam Masuk (Lewat ini dianggap Terlambat)</label>
                                    <input type="time" name="late_limit_time" class="form-control"
                                        value="{{ $setting->late_limit_time ?? '07:00' }}">
                                </div>

                                <div class="mb-3 form-group">
                                    <label>Batas Awal Jam Pulang (Sebelum ini tidak bisa scan pulang)</label>
                                    <input type="time" name="early_departure_time" class="form-control"
                                        value="{{ $setting->early_departure_time ?? '10:00' }}">
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            </div>
                        </div>
                    </form>
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
