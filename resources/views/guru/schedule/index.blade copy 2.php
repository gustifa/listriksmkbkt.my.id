@section('title')
   Jadwal Pembelejaran
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-primary"><i class="fas fa-chalkboard-teacher me-2"></i> Jadwal Mengajar Saya</h3>
                <p class="mb-0 text-muted">Kelola jadwal, lakukan absensi, dan pantau kehadiran siswa.</p>
            </div>
            <div>
                <a href="{{ route('schedule.create') }}" class="shadow-sm btn btn-success">
                    <i class="bx bx-plus me-1"></i> Tambah Jadwal
                </a>
                <!-- Tombol Shortcut untuk Cetak Kartu -->
                {{-- <a href="{{ route('print.index') }}" class="shadow-sm btn btn-outline-dark ms-2">
                    <i class="fas fa-id-card me-1"></i> Cetak Kartu Siswa
                </a> --}}
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="border-0 shadow card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-bordered">
                        <thead class="text-center table-dark">
                            <tr>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Kehadiran Hari Ini</th>
                                <th width="25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $sched)
                                @php
                                    $dayMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
                                    $todayIs = $dayMap[date('l')];
                                    $now = date('H:i:s');
                                    // Jadwal aktif jika HARI SAMA dan JAM SEKARANG masuk dalam rentang waktu
                                    $isActive = ($sched->day == $todayIs && $now >= $sched->start_time && $now <= $sched->end_time);
                                @endphp

                                <tr class="{{ $isActive ? 'table-success border-success' : '' }}">
                                    <td class="text-center">{{ $sched->day }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($sched->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }}</td>
                                    <td class="text-center fw-bold">{{ $sched->classroom->name ?? '-' }}</td>

                                    <!-- Nama Mapel (Support Relasi & Legacy String) -->
                                    <td>{{ $sched->subject->name ?? $sched->subject_name ?? '-' }}</td>

                                    <!-- MENAMPILKAN JUMLAH SISWA YANG SUDAH ABSEN HARI INI -->
                                    <td class="text-center">
                                        @if($sched->attendances_count > 0)
                                            <span class="badge bg-info text-dark" style="font-size: 0.9rem;">
                                                <i class="fas fa-user-check me-1"></i> {{ $sched->attendances_count }} Tercatat
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Belum ada</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="btn-group">
                                            <!-- Lihat Detail -->
                                            <a href="{{ route('schedule.show', $sched->id) }}" class="text-white btn btn-sm btn-info" title="Lihat Data Absen">
                                                <i class="bx bxs-show"></i>
                                            </a>

                                            <!-- Cetak PDF Laporan -->
                                            <a href="{{ route('report.schedule', $sched->id) }}" class="btn btn-sm btn-danger" target="_blank" title="Cetak Laporan">
                                                <i class="bx bxs-file-pdf"></i>
                                            </a>

                                            <!-- Scan / Hapus -->
                                            @if($isActive)
                                                <!-- Tombol Scan Berdenyut jika Aktif -->
                                                <a href="{{ route('scan.index', ['schedule_id' => $sched->id]) }}" class="btn btn-sm btn-primary pulse-button" title="Mulai Scan">
                                                    <i class="bx bx-barcode"></i>
                                                </a>
                                                <a href="{{ url('/schedule/manual', ['schedule_id' => $sched->id]) }}" class="btn btn-sm btn-primary">
                                                    <i class="bx bx-message-square-add"></i>
                                                </a>
                                                <a href="{{ route('scan.face', ['schedule_id' => $sched->id]) }}" class="btn btn-sm btn-warning" title="Mode Scan Wajah">
                                                    <i class="bx bx-camera"></i>
                                                </a>
                                            @else
                                                <form action="{{ route('schedule.destroy', $sched->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jadwal ini? Data absensi lama mungkin ikut terhapus.');">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="Hapus Jadwal"><i class="bx bx-trash"></i></button>
                                                </form>
                                            @endif
                                        </div>

                                        <!-- Shortcut Cetak Kartu Kelas Ini -->
                                        <div class="mt-1">
                                            <a href="{{ route('print.siswa.select', $sched->classroom_id) }}" class="text-decoration-none small text-muted">
                                                <i class="fas fa-print"></i> Kartu Kelas
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-5 text-center text-muted">Belum ada jadwal. Silakan klik tombol "Tambah Jadwal".</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<style>
    /* Animasi tombol berdenyut untuk jadwal aktif */
    .pulse-button {
        animation: pulse 1.5s infinite;
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
    }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }

</style>

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
