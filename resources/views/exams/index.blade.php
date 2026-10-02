@extends('layouts.app')

@section('title', 'Daftar Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">Daftar Kelola Ujian</h4>
    <a href="{{ route('exams.create') }}" class="btn btn-primary">
        + Buat Ujian Baru
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Nama Ujian</th>
                        <th>Mata Pelajaran</th>
                        @if(Auth::user()->hasRole('admin'))
                            <th>Guru Pengampu</th>
                        @endif
                        <th>Kelas</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $index => $exam)
                        <tr>
                            <td class="ps-3">{{ $exams->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $exam->title }}</strong><br>
                                <small class="text-muted">Tipe: {{ strtoupper(str_replace('_', ' ', $exam->type)) }}</small>
                            </td>
                            <td>{{ $exam->subject->name ?? '-' }}</td>
                            @if(Auth::user()->hasRole('admin'))
                                <td>{{ $exam->teacher->name ?? '-' }}</td>
                            @endif
                            <td>
                                @foreach($exam->classrooms as $cls)
                                    <span class="badge bg-secondary mb-1">{{ $cls->name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $exam->duration_minutes }} Menit</td>
                            <td>
                                @if($exam->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="" class="btn btn-sm btn-info text-white me-1" title="Detail / Kelola Soal">
                                    Soal ({{ $exam->questions_count ?? 0 }})
                                </a>
                                <a href="" class="btn btn-sm btn-success me-1" title="Import Excel">
                                    Import
                                </a>
                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ujian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->hasRole('admin') ? 8 : 7 }}" class="text-center py-4 text-muted">
                                Belum ada data ujian yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($exams->hasPages())
        <div class="card-footer bg-white">
            {{ $exams->links() }}
        </div>
    @endif
</div>
@endsection
