@section('title', 'Edit Data Guru')

<x-app-layout>
    <div class="page-content">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Data Guru</h5>
                <a href="{{ route('teachers.index') }}" class="btn btn-sm btn-light text-primary fw-bold">Kembali</a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- INFORMASI AKUN -->
                    <h6 class="mb-3 fw-bold text-primary border-bottom pb-2">
                        <i class="fas fa-user-circle me-1"></i>Informasi Akun
                    </h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $teacher->user->name ?? '') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Email (Login)</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $teacher->user->email ?? '') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Password Baru <small class="text-muted">(Kosongkan jika tidak ubah)</small></label>
                            <input type="password" name="password" class="form-control" placeholder="******">
                        </div>
                    </div>

                    <!-- PROFIL & KEPEGAWAIAN -->
                    <h6 class="mt-4 mb-3 fw-bold text-primary border-bottom pb-2">
                        <i class="fas fa-id-card me-1"></i>Data Profil & Kepegawaian
                    </h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">NIP</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip', $teacher->nip) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">No. HP / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $teacher->phone) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Jenis Kelamin</label>
                            <select name="gender" class="form-select">
                                <option value="L" {{ old('gender', $teacher->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $teacher->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Tempat Lahir</label>
                            <input type="text" name="place_of_birth" class="form-control" value="{{ old('place_of_birth', $teacher->place_of_birth) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Tanggal Lahir</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $teacher->date_of_birth) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Pendidikan Terakhir</label>
                            <input type="text" name="education_level" class="form-control" value="{{ old('education_level', $teacher->education_level) }}" placeholder="Contoh: S1 Pendidikan">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Pangkat</label>
                            <input type="text" name="pangkat" class="form-control" value="{{ old('pangkat', $teacher->pangkat) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Golongan</label>
                            <input type="text" name="golongan" class="form-control" value="{{ old('golongan', $teacher->golongan) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Tugas Tambahan (Master Data)</label>
                            <select name="additional_task_id" class="form-select">
                                <option value="">-- Tidak Ada Tugas Tambahan --</option>
                                @foreach(\App\Models\AdditionalTask::orderBy('type')->orderBy('name')->get() as $task)
                                    <option value="{{ $task->id }}" {{ old('additional_task_id', $teacher->additional_task_id) == $task->id ? 'selected' : '' }}>
                                        {{ $task->name }} ({{ $task->hours }} Jam - {{ $task->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold small">Alamat Lengkap</label>
                            <textarea name="address" class="form-control" rows="3">{{ old('address', $teacher->address) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('teachers.index') }}" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>