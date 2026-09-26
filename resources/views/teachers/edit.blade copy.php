@section('title')
   Pengaturan Kop Surat & Sekolah
@endsection
<x-app-layout>
    <div class="page-content">

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="shadow card">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <i class="fas fa-user-edit me-2"></i> Edit Data Guru
                    </div>
                    <div class="card-body">

                        <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
                            @csrf
                            @method('PUT')


                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $teacher->user->name) }}" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">NIP</label>
                                    <input type="text" name="nip" class="form-control" value="{{ old('nip', $teacher->nip) }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Jenis Kelamin</label>
                                    <select name="gender" class="form-select">
                                        <option value="">- Pilih -</option>
                                        <option value="L" {{ $teacher->gender == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ $teacher->gender == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">No. HP</label>
                                    <input type="number" name="phone" class="form-control" value="{{ old('phone', $teacher->phone) }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label fw-bold">Jurusan Mengajar</label>
                                   <select name="major_id" class="form-select">
                                    <option value="">- Pilih -</option>
                                    @foreach($majors as $major)
                                        <option value="{{$major->id}}" {{ $major->id == $teacher->id ? 'selected' : '' }}>
                                            {{$major->name}}
                                        </option>
                                    @endforeach
                                </select>
                                </div>
                                
                            </div>


                            <div class="mb-3">
                                <label class="form-label fw-bold">Alamat</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $teacher->address) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Alamat</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('tugas_tambahan', $teacher->tugas_tambahan) }}</textarea>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
