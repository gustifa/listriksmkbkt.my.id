@section('title', 'Penilaian PKL Siswa')

<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-chalkboard-teacher me-2"></i>Siswa Bimbingan PKL</h4>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="border-0 shadow-sm card">
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Siswa</th>
                                <th>Tempat PKL</th>
                                <th>Periode</th>
                                <th class="text-center">Nilai Akhir</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($internships as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $item->student->name }}</div>
                                    <small class="text-muted">{{ $item->student->nis }} • {{ $item->student->classroom->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $item->industry->name }}</div>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> {{ \Illuminate\Support\Str::limit($item->industry->address, 30) }}</small>
                                </td>
                                <td>
                                    <small>
                                        {{ $item->start_date->format('d/m/y') }} s.d {{ $item->end_date->format('d/m/y') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if($item->grade)
                                        <span class="badge bg-success fs-6">{{ $item->grade->final_score }}</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('teacher.internships.assess', $item->id) }}" class="btn btn-sm btn-{{ $item->grade ? 'warning' : 'primary' }} fw-bold">
                                        <i class="fas fa-edit me-1"></i> {{ $item->grade ? 'Edit Nilai' : 'Input Nilai' }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-muted">Anda belum memiliki siswa bimbingan PKL.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white card-footer">
                {{ $internships->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
