@section('title')
    Absensi Manual - {{ $schedule->classroom->name }}
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold text-primary">Absensi Manual</h4>
                        <small class="text-muted fw-bold">
                            <i class="fas fa-book-open me-1"></i> {{ $schedule->subject->name }} 
                            <span class="mx-2">|</span> 
                            <i class="fas fa-door-open me-1"></i> {{ $schedule->classroom->name }}
                        </small>
                    </div>
                    <a href="{{ route('schedule.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="shadow card border-top-primary">
                    <div class="card-body">
                        
                        <div class="alert alert-info small mb-4">
                            <i class="fas fa-info-circle me-1"></i> 
                            <strong>Catatan:</strong> Siswa hanya bisa diabsen jika sudah melakukan <b>Scan Masuk</b> di gerbang sekolah.
                        </div>

                        <form action="{{ route('attendance.storeManual', $schedule->id) }}" method="POST">
                            @csrf

                            <div class="table-responsive">
                                <table class="table align-middle table-hover table-bordered">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">NIS</th>
                                            <th width="30%" class="text-start">Nama Siswa</th>
                                            <th>Status Kehadiran</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $index => $student)
                                            @php
                                                // Mapping status DB (English) ke View (Indo) untuk 'checked'
                                                // DB: present, late, permission, sick, alpha
                                                $dbStatus = $existingAttendances[$student->id] ?? null;
                                                
                                                // Cari value mana yang harus dicentang
                                                $checked = '';
                                                if ($dbStatus == 'hadir') $checked = 'hadir';
                                                elseif ($dbStatus == 'terlambat') $checked = 'terlambat';
                                                elseif ($dbStatus == 'izin') $checked = 'izin';
                                                elseif ($dbStatus == 'sakit') $checked = 'sakit';
                                                elseif ($dbStatus == 'alpa') $checked = 'alpa';
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td class="text-center font-monospace">{{ $student->nis }}</td>
                                                <td class="fw-bold">{{ $student->name }}</td>
                                                <td class="text-center">
                                                    <div class="btn-group w-100" role="group">

                                                        <!-- HADIR -->
                                                        <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="h_{{ $student->id }}" value="hadir" {{ $checked == 'hadir' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-success btn-sm" for="h_{{ $student->id }}">Hadir</label>

                                                        <!-- TERLAMBAT (BARU) -->
                                                        <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="t_{{ $student->id }}" value="terlambat" {{ $checked == 'terlambat' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-warning btn-sm text-dark" for="t_{{ $student->id }}">Telat</label>

                                                        <!-- SAKIT -->
                                                        <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="s_{{ $student->id }}" value="sakit" {{ $checked == 'sakit' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-info btn-sm" for="s_{{ $student->id }}">Sakit</label>

                                                        <!-- IZIN -->
                                                        <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="i_{{ $student->id }}" value="izin" {{ $checked == 'izin' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-primary btn-sm" for="i_{{ $student->id }}">Izin</label>

                                                        <!-- ALPA -->
                                                        <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="a_{{ $student->id }}" value="alpa" {{ $checked == 'alpa' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-danger btn-sm" for="a_{{ $student->id }}">Alpa</label>

                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Belum ada siswa di kelas ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                    <i class="fas fa-save me-2"></i> Simpan Data Absensi
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>