@section('title')
   Data Seluruh Murid
@endsection

<x-app-layout>
    <div class="page-content">

        <div class="container">
            <h4 class="mb-4 fw-bold text-primary"><i class="fas fa-book-reader me-2"></i> Riwayat Absensi Pelajaran</h4>

            <div class="border-0 shadow card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->check_in_time)->format('H:i') }}</td>
                                        <td class="fw-bold">
                                            {{ $row->schedule->subject->name ?? $row->schedule->subject_name ?? '-' }}
                                        </td>
                                        <td>{{ $row->schedule->teacher->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $bg = match($row->status) { 'hadir'=>'success', 'terlambat'=>'warning', 'sakit'=>'primary', 'izin'=>'info', default=>'danger' };
                                            @endphp
                                            <span class="badge bg-{{ $bg }} text-uppercase">{{ $row->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-muted">Belum ada riwayat absensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
