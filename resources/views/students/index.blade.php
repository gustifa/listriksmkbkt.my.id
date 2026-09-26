@section('title', 'Data Seluruh Murid & Biometrik')

<x-app-layout>
    <div class="page-content">
        <div class="col-md-12">
            <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Maping</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="fas fa-users me-2"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Murid & Registrasi Wajah</li>
                </ol>
            </nav>
        </div>
       
    </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <!-- <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-users me-2"></i> Data Murid & Registrasi Wajah</h5> -->
                
                <div>
                    <a href="{{ route('students.import') }}" class="btn btn-success me-2">
                        <i class="bx bx-import"></i> Import Excel
                    </a>

                    <a href="{{ route('students.export') }}" class="shadow-sm btn btn-warning">
                        <i class="bx bx-export"></i> Export
                    </a>
                </div>
            </div>

            <div class="border-0 shadow card mb-4">
                <div class="card-body">
                    <form action="{{ route('students.index') }}" method="GET">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari Nama atau NIS..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="classroom_id" class="form-select">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($classrooms as $class)
                                        <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search"></i> Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>No. HP Ortu</th>
                                    <th class="text-center">Status Wajah</th>
                                    <th class="text-center" width="20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    <tr>
                                        <td class="text-center">
                                            <span class="border badge bg-light text-dark">{{ $student->nis }}</span>
                                        </td>
                                        <td class="fw-bold">{{ $student->name }}</td>
                                        <td>{{ $student->classroom->name ?? '-' }}</td>
                                        <td>{{ $student->phone ?? '-' }}</td>
                                        
                                        <td class="text-center">
                                            @if($student->face_descriptor)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i> Terdaftar
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times-circle me-1"></i> Belum Ada
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('face.register', $student->id) }}" class="btn btn-sm btn-primary" title="Scan Biometrik Wajah">
                                                    <i class="fas fa-camera"></i>
                                                </a>

                                                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-warning text-white" title="Edit Data">
                                                    <i class="bx bx-edit"></i>
                                                </a>

                                                <a href="{{ route('students.print_id', $student->id) }}" class="btn btn-sm btn-info text-white" title="Cetak Kartu">
                                                    <i class="bx bx-id-card"></i>
                                                </a>

                                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus siswa ini? Data absensi & wajah juga akan terhapus.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus Siswa">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-muted">
                                            <i class="bx bx-folder-open fs-4 mb-2 d-block"></i>
                                            Data siswa tidak ditemukan. Silakan tambah data atau import Excel.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>