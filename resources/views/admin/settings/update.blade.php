@section('title')
    Update Aplikasi
@endsection
<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="page-content">
        <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Update</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Update Aplikasi</li>
                </ol>
            </nav>
        </div>
       
    </div>
    <!--end breadcrumb-->
        <div class="row justify-content-center">
            <div class="col-md-12">
                
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-sync-alt"></i> Update Aplikasi</h4>
                    </div>
                    
                    <div class="card-body">
                        <p class="text-muted">
                            Sumber Repository: <br>
                            <code>https://github.com/gustifafauzan1986/listrik.git</code>
                        </p>
                        
                        <div class="alert alert-info">
                            <strong>Versi Saat Ini (Git Hash):</strong> {{ $currentHash ?? 'Unknown' }}
                        </div>

                        <p>
                            Sistem akan melakukan langkah berikut:
                            <ul>
                                <li>Menarik kode terbaru (Git Pull)</li>
                                <li>Menginstall dependency (Composer)</li>
                                <li>Update Database (Migrate)</li>
                                <li>Membersihkan Cache</li>
                            </ul>
                        </p>

                        <hr>

                        <form action="{{ route('system.update.run') }}" method="POST" id="updateForm">
                            @csrf
                            <button type="button" id="btn-update" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-download"></i> Download & Install Update
                            </button>
                        </form>
                        
                        <div id="loading" class="text-center mt-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-primary">Sedang memproses update... Mohon jangan tutup halaman ini.</p>
                        </div>
                    </div>
                </div>

                @if(session('log'))
                    <div class="card mt-4 bg-dark text-white">
                        <div class="card-header">Log Output</div>
                        <div class="card-body">
                            <pre style="font-size: 0.8rem; color: #0f0;">
                @foreach(session('log') as $line)
                {{ $line }}
                @endforeach
                            </pre>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger mt-3">
                        {{ session('error') }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        document.getElementById('btn-update').addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah submit langsung

            Swal.fire({
                title: 'Konfirmasi Update',
                text: "Apakah Anda yakin ingin mengupdate sistem? Pastikan backup database terlebih dahulu.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754', // Warna hijau success bootstrap
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 1. Sembunyikan Form/Tombol
                    document.getElementById('updateForm').style.display = 'none';
                    
                    // 2. Tampilkan Loading
                    document.getElementById('loading').style.display = 'block';

                    // 3. Submit Form secara manual
                    document.getElementById('updateForm').submit();
                }
            });
        });
    </script>
</x-app-layout>