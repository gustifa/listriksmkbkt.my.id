@section('title')
   Edit Setting Kelas
@endsection

<x-app-layout>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="border-0 shadow card">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Data Kelas</h5>
                    </div>
                    <div class="card-body">

                        <form action="{{ route('classrooms.update', $classroom->id) }}" method="POST">
    @csrf
    @method('PUT')

    <!-- Nama Kelas -->
    <div class="mb-3">
        <label class="form-label">Nama Kelas</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $classroom->name) }}" required>
    </div>

    <!-- Pilih Wali Kelas -->
    <div class="mb-3">
        <label class="form-label">Wali Kelas</label>
        <select name="homeroom_teacher_id" class="form-select">
            <option value="">-- Pilih Wali Kelas --</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" {{ $classroom->homeroom_teacher_id == $teacher->id ? 'selected' : '' }}>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Pilih Guru BK -->
    <div class="mb-3">
        <label class="form-label">Guru BK</label>
        <select name="counseling_teacher_id" class="form-select">
            <option value="">-- Pilih Guru BK --</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" {{ $classroom->counseling_teacher_id == $teacher->id ? 'selected' : '' }}>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Pilih Ketua Kelas -->
    <div class="mb-3">
        <label class="form-label">Ketua Kelas</label>
        <select name="class_leader_id" class="form-select">
            <option value="">-- Pilih Ketua Kelas --</option>
            @foreach($classroom->students as $student)
                <option value="{{ $student->id }}" {{ $classroom->class_leader_id == $student->id ? 'selected' : '' }}>
                    {{ $student->name }}
                </option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="{{ route('classrooms.index') }}" class="btn btn-secondary">Batal</a>
</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
