@section('title')
   Registrasi Biometrik Wajah
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="col-md-12">
            <div class="mb-4 shadow card">
                <div class="text-white card-header bg-primary">
                    <h5 class="mb-0">Registrasi Biometrik Wajah</h5>
                </div>
                <div class="card-body">
                    <!-- Filter Kelas -->
                    <form action="{{ route('face.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-12">
                                <select name="classroom_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classrooms as $class)
                                        <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    <!-- Tabel Siswa -->
                    @if(request('classroom_id'))
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Status Wajah</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                <tr>
                                    <td>{{ $student->nis }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>
                                        @if($student->face_descriptor)
                                            <span class="badge bg-success">Terdaftar</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Ada</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('face.register', $student->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-camera"></i> Scan Wajah
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
