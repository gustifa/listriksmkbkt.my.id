@section('title')
   Pengaturan Kop Surat & Sekolah
@endsection
<x-app-layout>
    <div class="page-content">
        <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Setting</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Jadwal Masuk & Pulang</li>
                </ol>
            </nav>
        </div>
       
    </div>
    <!--end breadcrumb-->
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i> Pengaturan Aplikasi</h5>
                    </div>
                    <div class="card-body">
                        
                       

                    <form action="{{ route('settings.update.attendance') }}" method="POST">
                        @csrf
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

                                <!-- <button type="submit" class="btn btn-primary">Simpan Pengaturan</button> -->
                                 <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-2"></i> Simpan
                                    </button>
                                </div>
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
