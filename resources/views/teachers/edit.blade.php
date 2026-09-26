<x-app-layout>
    <div class="page-content">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Data Guru</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ $teacher->user->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $teacher->user->email }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control" value="{{ $teacher->nip }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="phone" class="form-control" value="{{ $teacher->phone }}">
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>