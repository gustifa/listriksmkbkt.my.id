
@section('title')
   Tambaah User
@endsection
<x-app-layout>
    <div class="page-content">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="shadow card">
                            <div class="text-white card-header bg-primary">
                                <h5 class="mb-0">Tambah User Manual</h5>
                            </div>
                            <div class="card-body">

                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                <form action="{{ route('store.user') }}" method="POST">
                                    @csrf

                                    <!-- Pilihan Role -->
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role / Peran</label>
                                        <select name="role" id="role" class="form-select" required>
                                            <option value="siswa">Siswa</option>
                                            <option value="guru">Guru</option>
                                            <option value="piket">Guru Piket</option>
                                            <option value="admin">Administrator</option>
                                        </select>
                                    </div>

                                    <!-- Nama -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama Lengkap</label>
                                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Budi Santoso">
                                    </div>

                                    <!-- Nama -->
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" required placeholder="Contoh: fauzan">
                                    </div>

                                    <!-- Nomor Induk (Dinamis Label) -->
                                    <div class="mb-3">
                                        <label for="nomor_induk" class="form-label" id="label_nomor_induk">NISN (Nomor Induk Siswa)</label>
                                        <input type="text" name="nomor_induk" class="form-control" required placeholder="Masukkan NIP atau NISN">
                                        @error('nomor_induk')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <!-- Opsi Lanjutan -->
                                    <hr>
                                    <p class="text-muted small">Opsi Login (Boleh dikosongkan, default password = Nomor Induk)</p>

                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="email" class="form-label">Email (Opsional)</label>
                                            <input type="email" name="email" class="form-control">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="password" class="form-label">Password (Opsional)</label>
                                            <input type="password" name="password" class="form-control" placeholder="Default: sama dengan NISN/NIP">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Simpan Data User</button>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
    </div>

        <!-- Script Sederhana untuk Ubah Label NISN/NIP -->
        <script>
            document.getElementById('role').addEventListener('change', function() {
                const label = document.getElementById('label_nomor_induk');
                if (this.value === 'siswa') {
                    label.innerText = 'NISN (Nomor Induk Siswa)';
                } else {
                    label.innerText = 'NIP / ID Pegawai';
                }
            });
        </script>
</x-app-layout>
