@section('title', 'Edit Jadwal Mengajar')

<x-app-layout>
    <div class="page-content">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="shadow card">
                        <div class="text-white card-header bg-warning">
                            <h5 class="mb-0 text-dark"><i class="fas fa-edit me-2"></i> Edit Jadwal Pelajaran</h5>
                        </div>
                        <div class="card-body">

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('schedule.update', $schedule->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- 1. Pilih Kelas -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Kelas</label>
                                    <select name="classroom_id" id="classroom_select" class="form-select" required>
                                        <option value="" disabled>-- Pilih Kelas --</option>
                                        @foreach($classrooms as $room)
                                            <option value="{{ $room->id }}" {{ $schedule->classroom_id == $room->id ? 'selected' : '' }}>
                                                {{ $room->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 2. Pilih Mata Pelajaran -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mata Pelajaran</label>
                                    <select name="subject_id" id="subject_select" class="form-select" required>
                                        <!-- Opsi awal diisi sesuai data yang tersimpan -->
                                        @if($schedule->subject)
                                            <option value="{{ $schedule->subject_id }}" selected>
                                                {{ $schedule->subject->name }}
                                                {{ $schedule->subject->code ? '('.$schedule->subject->code.')' : '' }}
                                            </option>
                                        @else
                                            <option value="" disabled selected>-- Pilih Mapel --</option>
                                        @endif
                                    </select>
                                    <small class="text-muted" id="mapel-info">
                                        *Hanya mapel yang Anda ajar di kelas terpilih yang muncul.
                                    </small>
                                </div>

                                <!-- 3. Pilih Ruangan / Bengkel (FITUR BARU) -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ruang / Labor / Bengkel</label>
                                    <select name="room_id" class="form-select">
                                        <option value="" selected>-- Tidak Ada Ruangan Khusus --</option>
                                        @foreach($rooms as $r)
                                            <option value="{{ $r->id }}" {{ $schedule->room_id == $r->id ? 'selected' : '' }}>
                                                {{ $r->name }} ({{ $r->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">*Opsional: Pilih jika pelajaran dilakukan di laboratorium atau bengkel.</small>
                                </div>

                                <!-- 4. Pilih Hari -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Hari</label>
                                    <select name="day" class="form-select" required>
                                        @php
                                            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                        @endphp
                                        @foreach($days as $day)
                                            <option value="{{ $day }}" {{ $day == $schedule->day ? 'selected' : '' }}>{{ $day }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 5. Jam -->
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-bold">Jam Mulai</label>
                                        <input type="time" name="start_time" class="form-control"
                                               value="{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}" required>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-bold">Jam Selesai</label>
                                        <input type="time" name="end_time" class="form-control"
                                               value="{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}" required>
                                    </div>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('schedule.index') }}" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-warning fw-bold">
                                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT FILTER MAPEL -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const assignments = @json($assignments);
            const classroomSelect = document.getElementById('classroom_select');
            const subjectSelect = document.getElementById('subject_select');

            // Simpan Subject ID awal agar bisa di-select kembali saat init
            const initialSubjectId = "{{ $schedule->subject_id }}";

            // Fungsi render dropdown subject
            function populateSubjects(classId, selectedSubId = null) {
                // Reset subject select, TAPI simpan opsi yang sedang terpilih jika masih valid
                // Cara paling aman: kosongkan dulu
                subjectSelect.innerHTML = '<option value="" disabled selected>-- Pilih Mapel --</option>';
                subjectSelect.disabled = true;

                if (classId) {
                    const filteredAssignments = assignments.filter(a => a.classroom_id == classId);

                    if (filteredAssignments.length > 0) {
                        subjectSelect.disabled = false;
                        filteredAssignments.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.subject.id;
                            option.textContent = item.subject.name + (item.subject.code ? ` (${item.subject.code})` : '');

                            // Auto select jika ID cocok
                            if (selectedSubId && item.subject.id == selectedSubId) {
                                option.selected = true;
                            }

                            subjectSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.textContent = "Tidak ada mapel terdaftar di kelas ini";
                        subjectSelect.appendChild(option);
                    }
                }
            }

            // 1. Jalankan saat halaman dimuat (agar mapel terpilih muncul)
            populateSubjects(classroomSelect.value, initialSubjectId);

            // 2. Jalankan saat user ganti kelas
            classroomSelect.addEventListener('change', function() {
                populateSubjects(this.value);
            });
        });
    </script>
</x-app-layout>
