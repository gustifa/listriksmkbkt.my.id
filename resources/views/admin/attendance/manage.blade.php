@extends('layouts.app') {{-- Sesuaikan layout admin Anda --}}

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Header Page -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-danger">
                <i class="fas fa-user-slash me-2"></i>Pembatalan & Kelola Presensi Siswa
            </h4>
            <p class="text-muted small mb-0">Cari data presensi harian siswa untuk melakukan pembatalan atau penghapusan record.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.attendance.manage.index') }}" method="GET" class="row g-3">
                
                <!-- Filter Tanggal -->
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold"><i class="far fa-calendar-alt me-1 text-primary"></i> Tanggal</label>
                    <input type="date" name="date" class="form-control rounded-3" value="{{ $date }}" required>
                </div>

                <!-- Filter Kelas -->
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold"><i class="fas fa-school me-1 text-primary"></i> Kelas</label>
                    <select name="classroom_id" id="classroom_select" class="form-select rounded-3">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}" {{ $classroomId ==$c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Siswa -->
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold"><i class="fas fa-user-graduate me-1 text-primary"></i> Siswa (Opsional)</label>
                    <select name="student_id" id="student_select" class="form-select rounded-3">
                        <option value="">-- Semua Siswa di Kelas --</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ $studentId ==$s->id ? 'selected' : '' }}>
                                {{ $s->name }} (NIS: {{$s->nis ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 text-end mt-3">
                    <a href="{{ route('admin.attendance.manage.index') }}" class="btn btn-light rounded-pill px-3 me-2">Reset</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-search me-1"></i> Tampilkan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($classroomId || $studentId)
        <!-- TABEL HASIL PRESENSI -->
        <div class="row g-4">

            <!-- 1. PRESENSI GERBANG -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-door-open me-2"></i> Presensi Gerbang ({{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }})
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ $gateAttendances->count() }} Data</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Siswa</th>
                                        <th class="text-center">Jam Masuk</th>
                                        <th class="text-center">Jam Pulang</th>
                                        <th class="text-center">Status</th>
                                        <th>Pencatat / Metode</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($gateAttendances as $gate)
                                        <tr>
                                            <td class="fw-bold">{{ $gate->student->name ?? '-' }}</td>
                                            <td class="text-center">
                                                {{ $gate->arrival_time ? \Carbon\Carbon::parse($gate->arrival_time)->format('H:i') . ' WIB' : '-' }}
                                            </td>
                                            <td class="text-center">
                                                {{ $gate->departure_time ? \Carbon\Carbon::parse($gate->departure_time)->format('H:i') . ' WIB' : '-' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $gate->status == 'hadir' ? 'success' : ($gate->status == 'terlambat' ? 'warning text-dark' : 'danger') }} rounded-pill px-3 py-1">
                                                    {{ ucfirst($gate->status) }}
                                                </span>
                                            </td>
                                            <td><small class="text-muted">{{ $gate->recorded_by ?? '-' }}</small></td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-cancel-attendance"
                                                        data-url="{{ route('admin.attendance.gate.delete', $gate->id) }}"
                                                        data-type="Presensi Gerbang"
                                                        data-nama="{{ $gate->student->name ?? 'Siswa' }}">
                                                    <i class="fas fa-trash-alt me-1"></i> Batalkan
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data presensi gerbang pada tanggal ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. PRESENSI MATA PELAJARAN -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-purple">
                            <i class="fas fa-book me-2"></i> Presensi Pembelajaran (Mapel)
                        </h6>
                        <span class="badge bg-purple rounded-pill">{{ $subjectAttendances->count() }} Data</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Siswa</th>
                                        <th>Mata Pelajaran</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subjectAttendances as $subj)
                                        <tr>
                                            <td class="fw-bold">{{ $subj->student->name ?? '-' }}</td>
                                            <td>{{ $subj->subject->name ?? 'Mapel' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-success rounded-pill px-3 py-1">{{ ucfirst($subj->status) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-cancel-attendance"
                                                        data-url="{{ route('admin.attendance.subject.delete', $subj->id) }}"
                                                        data-type="Presensi Mapel {{ $subj->subject->name ?? '' }}"
                                                        data-nama="{{ $subj->student->name ?? 'Siswa' }}">
                                                    <i class="fas fa-trash-alt me-1"></i> Batalkan
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Tidak ada data presensi mapel pada tanggal ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. PRESENSI SHOLAT -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-info">
                            <i class="fas fa-mosque me-2"></i> Presensi Sholat
                        </h6>
                        <span class="badge bg-info text-dark rounded-pill">{{ $prayerAttendances->count() }} Data</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Siswa</th>
                                        <th>Sholat</th>
                                        <th class="text-center">Jam</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($prayerAttendances as $p)
                                        <tr>
                                            <td class="fw-bold">{{ $p->student->name ?? '-' }}</td>
                                            <td>Sholat {{ ucfirst($p->prayer_name) }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($p->check_in_time)->format('H:i') }} WIB</td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-cancel-attendance"
                                                        data-url="{{ route('admin.attendance.prayer.delete', $p->id) }}"
                                                        data-type="Presensi Sholat {{ ucfirst($p->prayer_name) }}"
                                                        data-nama="{{ $p->student->name ?? 'Siswa' }}">
                                                    <i class="fas fa-trash-alt me-1"></i> Batalkan
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Tidak ada data presensi sholat pada tanggal ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @else
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <i class="fas fa-filter fa-3x text-muted mb-3 opacity-50"></i>
            <h6 class="fw-bold text-dark mb-1">Silakan Pilih Filter Terlebih Dahulu</h6>
            <p class="text-muted small mb-0">Pilih Kelas dan Tanggal di atas untuk menampilkan data presensi yang ingin dikelola/dibatalkan.</p>
        </div>
    @endif

</div>

<!-- SCRIPT SWEETALERT2 PEMBATALAN ABSENSI -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = '{{ csrf_token() }}';

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-cancel-attendance');
            if (!btn) return;

            const deleteUrl = btn.dataset.url;
            const attendanceType = btn.dataset.type;
            const studentName = btn.dataset.nama || 'Siswa';

            Swal.fire({
                title: 'Konfirmasi Pembatalan',
                html: `Apakah Anda yakin ingin membatalkan <strong>${attendanceType}</strong> milik <strong>${studentName}</strong>?<br><small class="text-danger">*Data yang dihapus tidak dapat dikembalikan.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Batalkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-4 shadow-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang membatalkan presensi',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                    });
                }
            });
        });
    });
</script>
@endsection