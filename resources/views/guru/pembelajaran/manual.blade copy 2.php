@section('title')
    Absensi Manual - {{ $schedule->classroom->name }}
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">

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
                    <div>
                        <!-- TOMBOL ISI JURNAL -->
                        <button type="button" class="btn btn-warning me-2 fw-bold text-dark" onclick="openJournalModal()">
                            <i class="fas fa-book-reader me-1"></i> Isi Jurnal
                        </button>

                        <!-- TOMBOL CETAK DAFTAR HADIR -->
                        <a href="{{ route('report.attendance_list', $schedule->id) }}" target="_blank" class="text-white btn btn-info me-2 fw-bold">
                            <i class="fas fa-print me-1"></i> Cetak Presensi
                        </a>

                        <a href="{{ route('schedule.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
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

                            <div class="mb-3 row">
                                <!-- PENCARIAN SISWA -->
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                        <input type="text" id="searchStudent" class="form-control" placeholder="Cari Nama Siswa atau NIS...">
                                    </div>
                                </div>

                                <!-- TOMBOL QUICK CHECK -->
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
                                <table class="table align-middle table-hover table-bordered">
                                    <thead class="text-center table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="10%">NIS</th>
                                            <th width="25%" class="text-start">Nama Siswa</th>
                                            <!-- KOLOM BARU: HISTORY -->
                                            <th width="25%">History (5 Pertemuan Terakhir)</th>
                                            <th>Status Kehadiran Mapel</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $index => $student)
                                            @php
                                                // 1. DATA ABSENSI HARI INI
                                                $attendanceData = $existingAttendances[$student->id] ?? null;

                                                // Handling format data (Array vs String)
                                                if (is_array($attendanceData)) {
                                                    $dbStatus = $attendanceData['status'] ?? null;
                                                    $recordedBy = $attendanceData['recorded_by'] ?? null;
                                                } else {
                                                    $dbStatus = $attendanceData; // Fallback
                                                    $recordedBy = null;
                                                }

                                                $checked = '';
                                                if ($dbStatus == 'present' || $dbStatus == 'hadir') $checked = 'hadir';
                                                elseif ($dbStatus == 'late' || $dbStatus == 'terlambat') $checked = 'terlambat';
                                                elseif ($dbStatus == 'permission' || $dbStatus == 'izin') $checked = 'izin';
                                                elseif ($dbStatus == 'sick' || $dbStatus == 'sakit') $checked = 'sakit';
                                                elseif ($dbStatus == 'alpha' || $dbStatus == 'alpa') $checked = 'alpa';

                                                $isMissingGate = isset($studentsMissingGate) ? $studentsMissingGate->contains('id', $student->id) : false;

                                                // --- FITUR LOCK JIKA AUTO SCAN ---
                                                $isAutoScan = ($recordedBy == 'face_scan' || $recordedBy == 'barcode_scan');
                                                $isDisabled = $isMissingGate || $isAutoScan;

                                                // --- 2. DATA HISTORY MINGGU LALU (FIX ERROR UNDEFINED KEY) ---
                                                // Gunakan method ->get() pada collection untuk akses aman, jangan pakai [$key]
                                                $histories = isset($allHistories) ? $allHistories->get($student->id, collect()) : collect();

                                                // Ambil 5 data terakhir
                                                $histories = $histories->take(5);
                                            @endphp
                                            <tr class="student-row {{ $isMissingGate ? 'table-danger' : ($isAutoScan ? 'table-success' : '') }}">
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td class="text-center font-monospace student-nis">{{ $student->nis }}</td>
                                                <td class="fw-bold student-name">
                                                    {{ $student->name }}

                                                    @if($isMissingGate)
                                                        <span class="badge bg-danger ms-2" title="Belum Scan Gerbang"><i class="fas fa-times-circle"></i> Belum Masuk</span>
                                                    @endif

                                                    @if($isAutoScan)
                                                        @if($recordedBy == 'face_scan')
                                                            <span class="badge bg-success ms-2"><i class="fas fa-smile"></i> Auto (Wajah)</span>
                                                        @else
                                                            <span class="badge bg-primary ms-2"><i class="fas fa-qrcode"></i> Auto (Barcode)</span>
                                                        @endif
                                                    @endif
                                                </td>

                                                <!-- TAMPILAN KOLOM HISTORY -->
                                                <td class="text-center bg-light">
                                                    @if($histories->count() > 0)
                                                        <div class="flex-wrap gap-1 d-flex justify-content-center">
                                                            @foreach($histories as $hist)
                                                                @php
                                                                    $badgeClass = match($hist->status) {
                                                                        'hadir', 'present' => 'bg-success',
                                                                        'terlambat', 'late' => 'bg-warning text-dark',
                                                                        'sakit', 'sick' => 'bg-info',
                                                                        'izin', 'permission' => 'bg-primary',
                                                                        'alpa', 'alpha' => 'bg-danger',
                                                                        default => 'bg-secondary'
                                                                    };
                                                                    $statusLabel = ucfirst($hist->status);
                                                                    // Singkatan 1 Huruf (H, T, S, I, A)
                                                                    $statusShort = strtoupper(substr($statusLabel, 0, 1));
                                                                    $dateLabel = \Carbon\Carbon::parse($hist->date)->format('d/m');
                                                                @endphp
                                                                <div class="text-center d-inline-block" style="line-height: 1;">
                                                                    <span class="badge {{ $badgeClass }} rounded-1"
                                                                          style="font-size: 0.65rem; min-width: 20px; padding: 2px;"
                                                                          title="{{ $dateLabel }}: {{ $statusLabel }}">
                                                                        {{ $statusShort }}
                                                                    </span>
                                                                    <div style="font-size: 0.55rem; margin-top: 2px; color: #666;">
                                                                        {{ $dateLabel }}
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    @if($isMissingGate)
                                                        <div class="text-danger small fw-bold">
                                                            <i class="fas fa-lock"></i> Terkunci (Belum Absen Gerbang)
                                                        </div>
                                                    @else
                                                        <div class="btn-group w-100" role="group">

                                                            <!-- Jika Locked (Auto Scan), tambahkan hidden input agar value tetap terkirim -->
                                                            @if($isAutoScan)
                                                                <input type="hidden" name="attendances[{{ $student->id }}]" value="{{ $checked }}">
                                                            @endif

                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="h_{{ $student->id }}" value="hadir" {{ $checked == 'hadir' ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                                                            <label class="btn btn-outline-success btn-sm" for="h_{{ $student->id }}">Hadir</label>

                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="t_{{ $student->id }}" value="terlambat" {{ $checked == 'terlambat' ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                                                            <label class="btn btn-outline-warning btn-sm text-dark" for="t_{{ $student->id }}">Telat</label>

                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="s_{{ $student->id }}" value="sakit" {{ $checked == 'sakit' ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                                                            <label class="btn btn-outline-info btn-sm" for="s_{{ $student->id }}">Sakit</label>

                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="i_{{ $student->id }}" value="izin" {{ $checked == 'izin' ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                                                            <label class="btn btn-outline-primary btn-sm" for="i_{{ $student->id }}">Izin</label>

                                                            <input type="radio" class="btn-check" name="attendances[{{ $student->id }}]" id="a_{{ $student->id }}" value="alpa" {{ $checked == 'alpa' ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                                                            <label class="btn btn-outline-danger btn-sm" for="a_{{ $student->id }}">Alpa</label>
                                                        </div>
                                                        @if($isAutoScan)
                                                            <div class="mt-1 text-muted" style="font-size: 0.75rem;">
                                                                <i class="fas fa-lock"></i> Terkunci oleh sistem
                                                            </div>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">Belum ada siswa.</td></tr>
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

    <!-- 3. MODAL BANTUAN ABSENSI GERBANG -->
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
                            <p class="mb-0 small">Fitur ini digunakan jika terjadi kendala pada scanner gerbang (misal mati listrik/rusak). Guru dapat mengabsenkan massal sebagai <strong>"Hadir"</strong> di gerbang.</p>
                        </div>

                        <p class="mb-2 fw-bold">Daftar Siswa Belum Scan Gerbang ({{ $studentsMissingGate->count() }}):</p>

                        <div class="border rounded table-responsive" style="max-height: 300px; overflow-y:auto;">
                            <table class="table mb-0 table-sm table-striped">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40" class="text-center"><input type="checkbox" id="checkAll" checked></th>
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

                        <div class="p-3 mt-3 border rounded bg-light">
                            <label class="mb-2 fw-bold small d-block">Set Status Gerbang Sebagai:</label>
                            <div class="gap-3 d-flex align-items-center">
                                <select name="status" class="w-auto form-select form-select-sm">
                                    <option value="hadir" selected>Hadir (Tepat Waktu)</option>
                                    <option value="terlambat">Terlambat</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="izin">Izin</option>
                                    <option value="alpa">Alpa</option>
                                </select>
                                <span class="text-muted small fst-italic">*Pilih "Sakit/Izin" jika siswa memang tidak hadir.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="px-4 btn btn-danger btn-sm fw-bold">Simpan & Buka Akses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('checkAll')?.addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.student-check');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
    @endif

    <!-- 4. MODAL JURNAL PEMBELAJARAN -->
    <div class="modal fade" id="journalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-book-reader me-2"></i> Jurnal Pembelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="journalForm">
                        @csrf
                        <!-- ID Jadwal (Hidden) -->
                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

                        <div class="mb-3">
                            <label for="j_topic" class="form-label fw-bold">Materi / Topik Pembelajaran</label>
                            <input type="text" class="form-control" id="j_topic" name="topic" placeholder="Contoh: Instalasi Listrik Dasar" required>
                        </div>

                        <div class="mb-3">
                            <label for="j_activity" class="form-label fw-bold">Aktivitas Pembelajaran</label>
                            <textarea class="form-control" id="j_activity" name="activity" rows="3" placeholder="Contoh: Praktikum, Diskusi Kelompok..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="j_notes" class="form-label fw-bold">Catatan Tambahan (Opsional)</label>
                            <textarea class="form-control" id="j_notes" name="notes" rows="2" placeholder="Catatan khusus tentang siswa atau kendala..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="saveJournal()">Simpan Jurnal</button>
                </div>
            </div>
        </div>
    </div>

     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

     <script>
        // SCRIPT QUICK CHECK STATUS
        function setAllStatus(value) {
            let radios = document.querySelectorAll(`input[type="radio"][value="${value}"]`);
            radios.forEach(radio => {
                // Hanya check jika radio tidak disabled dan barisnya terlihat
                if(!radio.disabled && radio.closest('tr').style.display !== 'none') {
                    radio.checked = true;
                }
            });
        }

        // SCRIPT PENCARIAN SISWA
        document.getElementById('searchStudent').addEventListener('keyup', function() {
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

        // --- SCRIPT JURNAL PEMBELAJARAN ---
        function openJournalModal() {
            // Reset form sebelum dibuka
            $('#journalForm')[0].reset();

            // Load data jurnal yang sudah ada via AJAX
            $.ajax({
                url: "{{ route('journal.show', $schedule->id) }}",
                type: "GET",
                success: function(res) {
                    if (res.status === 'success' && res.data) {
                        $('#j_topic').val(res.data.topic);
                        $('#j_activity').val(res.data.activity);
                        $('#j_notes').val(res.data.notes);
                    }
                    // Tampilkan Modal
                    var myModal = new bootstrap.Modal(document.getElementById('journalModal'));
                    myModal.show();
                },
                error: function() {
                    // Jika error (misal belum ada data), tetap buka modal kosong
                    var myModal = new bootstrap.Modal(document.getElementById('journalModal'));
                    myModal.show();
                }
            });
        }

        function saveJournal() {
            let formData = new FormData(document.getElementById('journalForm'));

            $.ajax({
                url: "{{ route('journal.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        // Tutup modal
                        var modalEl = document.getElementById('journalModal');
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    // Coba ambil pesan error dari response JSON jika ada
                    let msg = 'Terjadi kesalahan saat menyimpan jurnal.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        }

        // Alert Session Success
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        // Alert Validation Error
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Mohon periksa kembali inputan Anda.',
            });
        @endif
    </script>

</x-app-layout>
