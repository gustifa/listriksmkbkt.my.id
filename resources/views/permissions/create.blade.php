<x-app-layout>
    <div class="page-content">
        <div class="container mt-5">
            <div class="mx-auto col-md-6">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary">Tambah Permission Baru</div>
                    <div class="card-body">
                        <form action="{{ route('permissions.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="fw-bold">Nama Permission</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: delete_siswa" required>
                                <small class="text-muted">Gunakan huruf kecil dan underscore ( _ ) sebagai pemisah.</small>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
