@section('title')
    Absensi Manual - {{ $schedule->classroom->name }}
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-10">

                <!-- Header -->
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

                <!-- 1. ALERT & FITUR BANTUAN ABSEN GERBANG -->
                @if(isset($gatePercentage) && $gatePercentage < 50)
                    <div class="shadow-sm alert alert-danger border-left-danger d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="alert-heading fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> PERHATIAN!</h5>
                            <p class="mb-0 small">
                                Hanya <strong>{{ round($gatePercentage) }}%</strong> siswa di kelas ini yang sudah melakukan scan di gerbang.
                                <br>Siswa yang belum scan gerbang <strong>tidak dapat</strong> diabsen di mapel ini.
                            </p>
                        </div>
                        <button type="button" class="shadow-sm btn btn-light text-danger fw-bold" data-bs-toggle="modal" data-bs-target="#bulkGateModal">
                            <i class="fas fa-magic me-1"></i> Bantuan: Absen Gerbang Otomatis
                        </button>
                    </div>
                @elseif(isset($gatePercentage))
                    <div class="mb-4 alert alert-info small">
                        <i class="fas fa-check-circle me-1"></i>
                        <strong>Status Gerbang Aman:</strong> {{ round($gatePercentage) }}% siswa sudah scan masuk.
                    </div>
                @endif

                <!-- 2. FORM ABSENSI MAPEL -->
                <div class="shadow card border-top-primary">
                    <div class="card-body">
                        <form action="{{ route('attendance.storeManual', $schedule->id) }}" method="POST">
                            @csrf

                            <div class="table-responsive">
                                <table class="table align-middle table-hover table-bordered">
                                    <thead class="text-center table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">NIS</th>
                                            <th width="30%" class="text-start">Nama Siswa</th>
                                            <th>Status Kehadiran Mapel</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $index => $student)
                                            @php
                                                $dbStatus = $existingAttendances[$student->id] ?? null;

                                                $checked = '';
                                                // Mapping status dari database
                                                if ($dbStatus == 'present' || $dbStatus == 'hadir') $checked = 'hadir';
                                                elseif ($dbStatus == 'late' || $dbStatus == 'terlambat') $checked = 'terlambat';
                                                elseif ($dbStatus == 'permission' || $dbStatus == 'izin') $checked = 'izin';
                                                elseif ($dbStatus == 'sick' || $dbStatus == 'sakit') $checked = 'sakit';
                                                elseif ($dbStatus == 'alpha' || $dbStatus == 'alpa') $checked = 'alpa';

                                                // Cek apakah siswa ini ada di list Missing Gate?
                                                $isMissingGate = isset($studentsMissingGate) ? $studentsMissingGate->contains('id', $student->id) : false;
                                            @endphp
                                            <tr class="{{ $isMissingGate ? 'table-danger' : '' }}">
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td class="text-center font-monospace">{{ $student->nis }}</td>
                                                <td class="fw-bold">
                                                    {{ $student->name }}
                                                    @if($isMissingGate)
                                                        <span class="badge bg-danger ms-2" title="Belum Scan Gerbang">Belum Masuk</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isMissingGate)
                                                        <div class="text-danger small fw-bold">
                                                            <i class="fas fa-lock"></i> Terkunci (Belum Absen Gerbang)
                                                        </div>
                                                    @else
                                                        <div class="btn-group w-100" role="group">
                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="h_{{ $student->id }}" value="hadir" {{ $checked == 'hadir' ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-success btn-sm" for="h_{{ $student->id }}">Hadir</label>

                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="t_{{ $student->id }}" value="terlambat" {{ $checked == 'terlambat' ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-warning btn-sm text-dark" for="t_{{ $student->id }}">Telat</label>

                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="s_{{ $student->id }}" value="sakit" {{ $checked == 'sakit' ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-info btn-sm" for="s_{{ $student->id }}">Sakit</label>

                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="i_{{ $student->id }}" value="izin" {{ $checked == 'izin' ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary btn-sm" for="i_{{ $student->id }}">Izin</label>

                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="a_{{ $student->id }}" value="alpa" {{ $checked == 'alpa' ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-danger btn-sm" for="a_{{ $student->id }}">Alpa</label>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">Belum ada siswa.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="gap-2 mt-4 d-grid">
                                <button type="submit" class="shadow-sm btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Simpan Data Absensi Mapel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 3. MODAL BANTUAN ABSENSI GERBANG (POPUP) -->
    @if(isset($studentsMissingGate) && $studentsMissingGate->count() > 0)
    <div class="modal fade" id="bulkGateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="text-white modal-header bg-danger">
                    <h5 class="modal-title fw-bold"><i class="fas fa-magic me-2"></i> Bantuan Absensi Gerbang</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('daily.store_bulk') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="border alert alert-light">
                            <p class="mb-0 small">Fitur ini digunakan jika terjadi kendala pada scanner gerbang atau siswa lupa scan. Guru/Piket dapat mengabsenkan mereka secara massal di gerbang agar status mapel terbuka.</p>
                        </div>

                        <p class="mb-2 fw-bold">Daftar Siswa Belum Absen Gerbang ({{ $studentsMissingGate->count() }}):</p>

                        <div class="table-responsive" style="max-height: 300px; overflow-y:auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40" class="text-center"><input type="checkbox" id="checkAll"></th>
                                        <th>Nama Siswa</th>
                                        <th>NIS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($studentsMissingGate as $mStudent)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="student_ids[]" value="{{ $mStudent->id }}" class="student-check" checked>
                                            </td>
                                            <td>{{ $mStudent->name }}</td>
                                            <td>{{ $mStudent->nis }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <label class="fw-bold small">Set Status Gerbang Sebagai:</label>
                            <select name="status" class="w-auto form-select d-inline-block ms-2">
                                <option value="hadir" selected>Hadir (Tepat Waktu)</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="sakit">Sakit</option>
                                <option value="izin">Izin</option>
                                <option value="alpa">Alpa</option>
                            </select>
                            <div class="mt-1 form-text text-muted small">
                                *Pilih "Sakit/Izin" untuk siswa yang tidak hadir, agar data harian lengkap.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Simpan Absensi Gerbang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Script Check All di Modal
        document.getElementById('checkAll')?.addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.student-check');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
    @endif

</x-app-layout>
