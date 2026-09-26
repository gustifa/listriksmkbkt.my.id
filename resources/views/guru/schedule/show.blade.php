<x-app-layout>
    <div class="page-content">
        <div class="col-md-12">

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Daftar Hadir Siswa</h4>
                    <small class="text-muted">
                        {{ $schedule->subject_name }} - {{ $schedule->classroom->name }}
                        ({{ \Carbon\Carbon::now()->translatedFormat('d F Y') }})
                    </small>
                </div>
                <a href="{{ route('schedule.index') }}" class="btn btn-secondary">Kembali</a>
            </div>

            <div class="row">
                <div class="mb-3 col-md-2">
                    <div class="text-white card bg-success">
                        <div class="text-center card-body">
                            <h3>{{ $attendances->where('status', 'hadir')->count() }}</h3>
                            <small>Hadir Tepat Waktu</small>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-2">
                    <div class="card bg-warning text-dark">
                        <div class="text-center card-body">
                            <h3>{{ $attendances->where('status', 'terlambat')->count() }}</h3>
                            <small>Terlambat</small>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-2">
                    <div class="text-white card bg-success">
                        <div class="text-center card-body">
                            <h3>{{ $attendances->where('status', 'sakit')->count() }}</h3>
                            <small>Sakit</small>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-2">
                    <div class="card bg-warning text-dark">
                        <div class="text-center card-body">
                            <h3>{{ $attendances->where('status', 'izin')->count() }}</h3>
                            <small>Izin</small>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-2">
                    <div class="card bg-warning text-dark">
                        <div class="text-center card-body">
                            <h3>{{ $attendances->where('status', 'alpa')->count() }}</h3>
                            <small>Alfa</small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 col-md-2">
                    <div class="text-white card bg-secondary">
                        <div class="text-center card-body">
                            <h3>{{ $attendances->whereIn('status', ['hadir', 'terlambat'])->count() }}</h3>
                            <small>Total Masuk</small>
                        </div>
                    </div>
                </div>
                </div>

            <div class="shadow card">
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th>Jam Masuk</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $key => $row)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $row->check_in_time }}</td>
                                    <td>{{ $row->student->nis }}</td>
                                    <td class="fw-bold">{{ $row->student->name }}</td>
                                    <td>
                                        @if($row->status == 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif ($row->status == 'sakit')
                                            <span class="badge bg-warning text-dark">Sakit</span>
                                        @elseif ($row->status == 'izin')
                                            <span class="badge bg-warning text-dark">Izin</span>
                                        @elseif ($row->status == 'terlambat')
                                            <span class="badge bg-warning text-dark">Terlambat</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Alpa</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-muted">
                                        Belum ada siswa yang melakukan scan hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>