<x-app-layout>
    <div class="page-content">
        <div class="col-md-12">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-primary">Absensi Manual</h4>
                    <small class="text-muted">
                        {{ $schedule->subject->name ?? $schedule->subject_name }} - {{ $schedule->classroom->name }}
                    </small>
                </div>
                <a href="{{ route('schedule.index') }}" class="btn btn-secondary">Kembali</a>
            </div>

            <div class="shadow card">
                <div class="card-body">
                    <form action="{{ route('attendance.storeManual', $schedule->id) }}" method="POST">
                        @csrf

                        <div class="table-responsive">
                            <table class="table align-middle table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th class="text-center">Status Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $index => $student)
                                        @php
                                            // Cek status yang sudah tersimpan (jika ada)
                                            $status = $existingAttendances[$student->id] ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->nis }}</td>
                                            <td class="fw-bold">{{ $student->name }}</td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">

                                                    <!-- HADIR -->
                                                    <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="h_{{ $student->id }}" value="hadir" {{ $status == 'hadir' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-success" for="h_{{ $student->id }}">Hadir</label>

                                                    <!-- IZIN -->
                                                    <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="i_{{ $student->id }}" value="izin" {{ $status == 'izin' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="i_{{ $student->id }}">Izin</label>

                                                    <!-- SAKIT -->
                                                    <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="s_{{ $student->id }}" value="sakit" {{ $status == 'sakit' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-warning" for="s_{{ $student->id }}">Sakit</label>

                                                    <!-- ALPA -->
                                                    <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="a_{{ $student->id }}" value="alpa" {{ $status == 'alpa' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-danger" for="a_{{ $student->id }}">Alpa</label>





                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada data siswa di kelas ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Simpan Data Absensi
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    
</x-app-layout>
