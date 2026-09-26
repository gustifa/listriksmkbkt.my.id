@section('title')
    Absensi Manual -
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold text-primary">Absensi Manual</h4>
                        <small class="text-muted fw-bold">
                            <i class="fas fa-book-open me-1"></i>
                            <span class="mx-2">|</span>
                            <i class="fas fa-door-open me-1"></i>
                        </small>
                    </div>
                </div>

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

                <div class="shadow card border-top-primary">
                    <div class="card-body">

                        <form action="{{route('daily.storeManual')}}" method="POST">
                            @csrf

                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                        <input type="text" id="searchStudent" class="form-control" placeholder="Cari Nama Siswa atau NIS...">
                                    </div>
                                </div>

                                <div class="mt-2 col-md-6 text-md-end mt-md-0">
                                    <span class="me-2 fw-bold small text-muted">Set Semua Ke:</span>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="setAllStatus('hadir')">Hadir</button>
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark" onclick="setAllStatus('terlambat')">Telat</button>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="setAllStatus('sakit')">Sakit</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setAllStatus('izin')">Izin</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAllStatus('alpa')">Alpa</button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle table-hover table-bordered" id="attendanceTable">
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
                                                // Mapping status
                                                if ($dbStatus == 'present' || $dbStatus == 'hadir') $checked = 'hadir';
                                                elseif ($dbStatus == 'late' || $dbStatus == 'terlambat') $checked = 'terlambat';
                                                elseif ($dbStatus == 'permission' || $dbStatus == 'izin') $checked = 'izin';
                                                elseif ($dbStatus == 'sick' || $dbStatus == 'sakit') $checked = 'sakit';
                                                elseif ($dbStatus == 'alpha' || $dbStatus == 'alpa') $checked = 'alpa';

                                                $isMissingGate = isset($studentsMissingGate) ? $studentsMissingGate->contains('id', $student->id) : false;
                                            @endphp
                                            <tr class="student-row {{ $isMissingGate ? 'table-danger' : '' }}">
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td class="text-center font-monospace student-nis">{{ $student->nis }}</td>
                                                <td class="fw-bold student-name">
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
                                                            <input type="radio" class="btn-check status-radio" name="attendances[{{ $student->id }}]" id="h_{{ $student->id }}" value="hadir" {{ $checked == 'hadir' ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-success btn-sm" for="h_{{ $student->id }}">Hadir</label>

                                                            <input type="radio" class="btn-check status-radio" name="attendances[{{ $student->id }}]" id="t_{{ $student->id }}" value="terlambat" {{ $checked == 'terlambat' ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-warning btn-sm text-dark" for="t_{{ $student->id }}">Telat</label>

                                                            <input type="radio" class="btn-check status-radio" name="attendances[{{ $student->id }}]" id="s_{{ $student->id }}" value="sakit" {{ $checked == 'sakit' ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-info btn-sm" for="s_{{ $student->id }}">Sakit</label>

                                                            <input type="radio" class="btn-check status-radio" name="attendances[{{ $student->id }}]" id="i_{{ $student->id }}" value="izin" {{ $checked == 'izin' ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary btn-sm" for="i_{{ $student->id }}">Izin</label>

                                                            <input type="radio" class="btn-check status-radio" name="attendances[{{ $student->id }}]" id="a_{{ $student->id }}" value="alpa" {{ $checked == 'alpa' ? 'checked' : '' }}>
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

                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <p class="mb-0 fw-bold">Daftar Siswa Belum Absen Gerbang ({{ $studentsMissingGate->count() }}):</p>

                            <div class="gap-2 d-flex">
                                @php
                                    // Mengambil daftar nama kelas unik dari data siswa yang belum absen gerbang
                                    // Asumsi relasi nama kelas ada di $mStudent->classroom->name
                                    // Jika struktur DB Anda berbeda, sesuaikan 'classroom.name' di bawah ini
                                    $uniqueClasses = $studentsMissingGate->pluck('classroom.name')->filter()->unique();
                                @endphp
                                <select id="filterModalClass" class="form-select form-select-sm" style="min-width: 120px;">
                                    <option value="">Semua Kelas</option>
                                    @foreach($uniqueClasses as $cls)
                                        <option value="{{ strtolower($cls) }}">{{ $cls }}</option>
                                    @endforeach
                                </select>

                                <div class="input-group input-group-sm" style="min-width: 200px;">
                                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                    <input type="text" id="searchModalStudent" class="form-control" placeholder="Cari Siswa...">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 300px; overflow-y:auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40" class="text-center"><input type="checkbox" id="checkAll"></th>
                                        <th>Nama Siswa</th>
                                        <th>NIS</th>
                                        <th>Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($studentsMissingGate as $mStudent)
                                        <tr class="modal-student-row">
                                            <td class="text-center">
                                                <input type="checkbox" name="student_ids[]" value="{{ $mStudent->id }}" class="student-check" checked>
                                            </td>
                                            <td class="modal-student-name">{{ $mStudent->name }}</td>
                                            <td class="modal-student-nis">{{ $mStudent->nis }}</td>
                                            <td class="modal-student-class">{{ $mStudent->classroom->name ?? $mStudent->kelas ?? '-' }}</td>
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
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Script Check All di Modal
            const checkAllBtn = document.getElementById('checkAll');
            if(checkAllBtn) {
                checkAllBtn.addEventListener('change', function() {
                    let checkboxes = document.querySelectorAll('.student-check');
                    checkboxes.forEach(cb => {
                        // Hanya check/uncheck yang barisnya visible (tidak tersaring fitur cari/filter)
                        if(cb.closest('tr').style.display !== 'none') {
                            cb.checked = this.checked;
                        }
                    });
                });
            }

            // SCRIPT FILTER KELAS & PENCARIAN DI MODAL (CLIENT SIDE)
            const searchModal = document.getElementById('searchModalStudent');
            const filterClassModal = document.getElementById('filterModalClass');

            function applyModalFilters() {
                let textFilter = searchModal ? searchModal.value.toLowerCase() : '';
                let classFilter = filterClassModal ? filterClassModal.value.toLowerCase() : '';
                let rows = document.querySelectorAll('.modal-student-row');

                rows.forEach(row => {
                    let name = row.querySelector('.modal-student-name').textContent.toLowerCase();
                    let nis = row.querySelector('.modal-student-nis').textContent.toLowerCase();
                    let kelas = row.querySelector('.modal-student-class').textContent.toLowerCase();

                    // Kondisi 1: Apakah nama atau NIS mengandung teks pencarian?
                    let matchText = name.includes(textFilter) || nis.includes(textFilter);

                    // Kondisi 2: Apakah kelas cocok dengan filter dropdown? (Jika kosong berarti tampilkan semua)
                    let matchClass = (classFilter === '') || (kelas === classFilter);

                    // Tampilkan hanya jika cocok keduanya
                    if (matchText && matchClass) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Pasang event listener ke input teks dan dropdown select
            if(searchModal) searchModal.addEventListener('keyup', applyModalFilters);
            if(filterClassModal) filterClassModal.addEventListener('change', applyModalFilters);

            // SCRIPT PENCARIAN SISWA TABEL UTAMA (CLIENT SIDE)
            const searchMain = document.getElementById('searchStudent');
            if(searchMain) {
                searchMain.addEventListener('keyup', function() {
                    let filter = this.value.toLowerCase();
                    let rows = document.querySelectorAll('.student-row');

                    rows.forEach(row => {
                        let name = row.querySelector('.student-name').textContent.toLowerCase();
                        let nis = row.querySelector('.student-nis').textContent.toLowerCase();

                        if (name.includes(filter) || nis.includes(filter)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        });

        // SCRIPT QUICK CHECK STATUS
        function setAllStatus(value) {
            let radios = document.querySelectorAll(`input[type="radio"][value="${value}"]`);
            radios.forEach(radio => {
                if(!radio.disabled && radio.closest('tr').style.display !== 'none') {
                    radio.checked = true;
                }
            });
        }
    </script>

    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Mohon periksa kembali inputan Anda.',
            });
        @endif
    </script>

</x-app-layout>
