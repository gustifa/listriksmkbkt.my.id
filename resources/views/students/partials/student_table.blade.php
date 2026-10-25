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
                        {{-- Tombol Detail --}}
                        @can('view', $student)
                            <a href="{{ route('students.show', $student->id) }}" class="btn btn-sm btn-info text-white me-1" title="Detail Siswa">
                                <i class="bx bx-show"></i> Detail
                            </a>
                        @endcan
                        <a href="{{ route('face.register', $student->id) }}" class="btn btn-sm btn-primary" title="Scan Biometrik Wajah">
                            <i class="fas fa-camera"></i>
                        </a>

                        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-warning text-white" title="Edit Data">
                            <i class="bx bx-edit"></i>
                        </a>

                        <a href="{{ route('students.print_id', $student->id) }}" class="btn btn-sm btn-info text-white" title="Cetak Kartu">
                            <i class="bx bx-id-card"></i>
                        </a>

                        <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline form-delete">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-sm btn-delete" title="Hapus Data">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="py-4 text-center text-muted">
                    <i class="bx bx-folder-open fs-4 mb-2 d-block"></i>
                    Data siswa tidak ditemukan.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Links Pagination -->
@if($students instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            Menampilkan {{ $students->firstItem() ?? 0 }} - {{ $students->lastItem() ?? 0 }} dari {{ $students->total() }} data
        </div>
        <div>
            {{ $students->links() }}
        </div>
    </div>
@endif