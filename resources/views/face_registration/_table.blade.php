<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jumlah Sampel</th>
                <th>Status</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
            <tr>
                <td>{{ $student->nis }}</td>
                <td><strong>{{ $student->name }}</strong></td>
                <td>
                    <span class="badge bg-secondary">
                        {{ $student->classroom->name ?? '-' }}
                    </span>
                </td>
                <td><span class="badge bg-info text-dark fs-6">{{ $student->face_descriptors_count }} Sampel</span></td>
                <td>
                    @if($student->face_descriptors_count >= 3)
                        <span class="badge bg-success">Optimal (Akurat)</span>
                    @elseif($student->face_descriptors_count > 0)
                        <span class="badge bg-warning text-dark">Kurang (Min. 3)</span>
                    @else
                        <span class="badge bg-danger">Belum Ada Data</span>
                    @endif
                </td>
                <td class="text-center">
                    <a href="{{ route('face.create', $student->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah Sampel
                    </a>
                    <a href="{{ route('face.edit', $student->id) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Kelola
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">Data siswa tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- PAGINATION AJAX -->
<div class="d-flex justify-content-end mt-3">
    {{ $students->links('pagination::bootstrap-5') }}
</div>
