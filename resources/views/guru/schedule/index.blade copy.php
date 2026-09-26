@section('title')
   Jadwal Pembelejaran
@endsection

<x-app-layout>
    <div class="page-content">

        <div class="col-md-12">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            <div class="mb-3 d-flex justify-content-between align-items-center">
                <h3>Jadwal Mengajar Saya</h3>
                <div>
                    <a href="{{ route('schedule.create') }}" class="btn btn-success">
                        + Buat Jadwal Baru
                    </a>
                </div>
            </div>


            <div class="shadow card">
                <div class="card-body">
                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th class="text-center" width="25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $sched)
                                @php
                                    $dayMap = [
                                        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                                        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
                                    ];
                                    $todayIs = $dayMap[date('l')];
                                    $now = date('H:i:s');

                                    $isActive = false;
                                    if ($sched->day == $todayIs && $now >= $sched->start_time && $now <= $sched->end_time) {
                                        $isActive = true;
                                    }
                                @endphp


                                <tr class="{{ $isActive ? 'table-success fw-bold' : '' }}">
                                    <td>{{ $sched->day }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sched->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            {{ $sched->classroom->name ?? 'Tanpa Kelas' }}
                                        </span>
                                    </td>
                                    <td>{{ $sched->subject->name ?? 'Kosong' }}</td>
                                    <td class="text-center">
                                        <!-- TOMBOL 1: LIHAT DATA -->
                                        <a href="{{ route('schedule.show', $sched->id) }}" class="text-white btn btn-sm btn-info me-1" title="Lihat Detail">
                                             <i class="bx bx-show-alt"></i>
                                        </a>


                                        <!-- TOMBOL 2: CETAK PDF (LINK KE ROUTE BARU) -->
                                        <a href="{{ route('report.schedule', $sched->id) }}" class="btn btn-sm btn-danger me-1" target="_blank" title="Cetak Laporan Mapel">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>


                                        <!-- TOMBOL 3: SCAN / HAPUS -->
                                        @if($isActive)
                                            <a href="{{ url('/schedule/manual', ['schedule_id' => $sched->id]) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-message-square-add"></i>
                                            </a>
                                            <a href="{{ route('scan.index', ['schedule_id' => $sched->id]) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-scan"></i>
                                            </a>
                                            <a href="{{ route('scan.face', ['schedule_id' => $sched->id]) }}" class="btn btn-sm btn-warning" title="Mode Scan Wajah">
                                                <i class="bx bx-camera"></i>
                                            </a>

                                        @else
                                            <form id="delete-form-{{ $sched->id }}" action="{{ route('schedule.destroy', $sched->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ $sched->id }}', '{{ $sched->subject_name }}')">
                                                    Hapus
                                                </button>
                                            </form>

                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-muted">
                                        Belum ada jadwal. Silakan klik tombol "Buat Jadwal Baru".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>



        <script>
            function confirmDelete(id, mapel) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Jadwal " + mapel + " akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                })
            }
        </script>

</x-app-layout>
