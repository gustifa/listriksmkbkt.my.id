@section('title')
    Data Guru
@endsection

<x-app-layout>
    <div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Maping</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="bx bx-user"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Guru</li>
                </ol>
            </nav>
        </div>

    </div>
    <!--end breadcrumb-->

            <div class="mb-4 d-flex justify-content-between align-items-center">

                <div>
                    <a href="{{ route('teachers.import') }}" class="shadow-sm btn btn-success">
                        <i class="bx bx-import"></i> Import
                    </a>

                    <a href="{{ route('teachers.export') }}" class="shadow-sm btn btn-warning">
                        <i class="bx bx-export"></i> Export
                    </a>
                </div>

                {{-- Tombol Tambah Manual (Opsional, jika ada route create) --}}
                <a href="" class="shadow-sm btn btn-primary">
                    <i class="bx bx-plus"></i> Tambah Guru
                </a>
            </div>

            {{-- @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif --}}

            <div class="border-0 shadow card">
                <div class="card-body">

                    <!-- Form Pencarian -->
                    <form action="{{ route('teachers.index') }}" method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari Nama, NIP, atau Email..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Cari</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="example" class="table align-middle table-striped table-bordered">
                            <thead class="text-center table-dark">
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <th>NIP</th>
                                    <th>L/P</th>
                                    <th>Email (Login)</th>
                                    <th>No. HP</th>
                                    <th>Keterangan</th> <!-- Kolom Baru -->
                                    <th class="text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teachers as $teacher)
                                    <tr>
                                        <td class="fw-bold">{{ $teacher->user->name ?? '-' }}</td>
                                        <td class="text-center">{{ $teacher->nip ?? '-' }}</td>
                                        <td class="text-center">{{ $teacher->gender ?? '-' }}</td>
                                        <td>{{ $teacher->user->email ?? '-' }}</td>
                                        <td class="text-center">{{ $teacher->phone ?? '-' }}</td>

                                        {{-- LOGIKA KETERANGAN GURU --}}
                                        <td class="text-center">
                                            @if($teacher->major)
                                                <span class="badge bg-info text-dark">Guru Jurusan {{ $teacher->major->code ?? '' }}</span>
                                            @else
                                                <span class="badge bg-secondary">Guru Umum</span>
                                            @endif

                                            @if($teacher->role_type == 'piket')
                                                <div class="mt-1">
                                                    <span class="badge bg-warning text-dark">Petugas Piket</span>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('teachers.show', $teacher->id) }}" class="text-white btn btn-sm btn-success" title="Detail">
                                                    <i class="bx bx-info-circle"></i>
                                                </a>

                                                <!-- TOMBOL MODAL EDIT -->
                                                <button type="button" class="text-white btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editTeacherModal{{ $teacher->id }}" title="Edit">
                                                    <i class="bx bx-message-square-edit"></i>
                                                </button>

                                                <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus guru ini? Akun login juga akan terhapus.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="bx bx-message-square-x"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- MODAL EDIT GURU -->
                                            <div class="modal fade" id="editTeacherModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-warning text-dark">
                                                            <h5 class="modal-title fw-bold">
                                                                <i class="bx bx-edit me-2"></i> Edit Data Guru
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body text-start">

                                                                <div class="row">
                                                                    <!-- Data Akun (User) -->
                                                                    <div class="col-md-6">
                                                                        <h6 class="mb-3 fw-bold text-primary">Informasi Akun</h6>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Nama Lengkap</label>
                                                                            <input type="text" name="name" class="form-control" value="{{ $teacher->user->name ?? '' }}" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Email (Login)</label>
                                                                            <input type="email" name="email" class="form-control" value="{{ $teacher->user->email ?? '' }}" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ubah)</small></label>
                                                                            <input type="password" name="password" class="form-control" placeholder="******">
                                                                        </div>
                                                                    </div>

                                                                    <!-- Data Profil (Teacher) -->
                                                                    <div class="col-md-6">
                                                                        <h6 class="mb-3 fw-bold text-primary">Data Profil</h6>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">NIP</label>
                                                                            <input type="text" name="nip" class="form-control" value="{{ $teacher->nip }}">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Jenis Kelamin</label>
                                                                            <select name="gender" class="form-select">
                                                                                <option value="L" {{ $teacher->gender == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                                                                <option value="P" {{ $teacher->gender == 'P' ? 'selected' : '' }}>Perempuan</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">No. HP</label>
                                                                            <input type="text" name="phone" class="form-control" value="{{ $teacher->phone }}">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Pangkat</label>
                                                                            <input type="text" name="pangkat" class="form-control" value="{{ $teacher->pangkat }}">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Golongan</label>
                                                                            <input type="text" name="golongan" class="form-control" value="{{ $teacher->golongan }}">
                                                                        </div>
                                                                         <div class="mb-3">
                                                                            <label class="form-label">Tugas Tamabahan</label>
                                                                            <input type="text" name="tugas_tambahan" class="form-control" value="{{ $teacher->tugas_tambahan }}">
                                                                        </div>

                                                                        <!-- Jika ada kolom jurusan (major_id) di table teachers -->
                                                                        @if(\Schema::hasColumn('teachers', 'major_id'))
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Jurusan (Opsional)</label>
                                                                            <select name="major_id" class="form-select">
                                                                                <option value="">-- Umum / Tidak Ada --</option>
                                                                                @foreach(\App\Models\Major::all() as $major)
                                                                                    <option value="{{ $major->id }}" {{ $teacher->major_id == $major->id ? 'selected' : '' }}>{{ $major->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            <small class="text-muted">Pilih jurusan jika Guru Produktif.</small>
                                                                        </div>
                                                                        @endif

                                                                        <!-- Role Type (Guru / Piket) -->
                                                                        @if(\Schema::hasColumn('teachers', 'role_type'))
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Tugas Tambahan</label>
                                                                            <select name="role_type" class="form-select">
                                                                                <option value="guru" {{ $teacher->role_type == 'guru' ? 'selected' : '' }}>Hanya Guru Mapel</option>
                                                                                <option value="piket" {{ $teacher->role_type == 'piket' ? 'selected' : '' }}>Guru Piket</option>
                                                                            </select>
                                                                        </div>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- END MODAL -->

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-4 text-center text-muted">
                                            Data guru belum tersedia. Silakan Import Data terlebih dahulu.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $teachers->withQueryString()->links() }}
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
