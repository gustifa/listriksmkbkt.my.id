<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary">
                        <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i> Buat Jadwal Pelajaran Baru</h5>
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

                        <form action="{{ route('schedule.store') }}" method="POST">
                            @csrf

                            <!-- 1. Pilih Kelas -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kelas</label>
                                <select name="classroom_id" id="classroom_select" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Kelas --</option>
                                    @foreach($classrooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Pilih Mata Pelajaran -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mata Pelajaran</label>
                                <select name="subject_id" id="subject_select" class="form-select" required disabled>
                                    <option value="" disabled selected>-- Pilih Kelas Terlebih Dahulu --</option>
                                </select>
                            </div>

                            <!-- 3. Pilih Hari (Otomatis Terisi dari Kalender) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hari</label>
                                <select name="day" class="form-select" required>
                                    @php
                                        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                        // Ambil 'day' dari URL (request), jika tidak ada gunakan Hari Ini
                                        $selectedDay = request('day', \Carbon\Carbon::now()->translatedFormat('l')); 
                                    @endphp
                                    @foreach($days as $day)
                                        <option value="{{ $day }}" {{ $day == $selectedDay ? 'selected' : '' }}>{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 4. Jam (Otomatis Terisi dari Kalender) -->
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Jam Mulai</label>
                                    <!-- Ambil 'start_time' dari URL -->
                                    <input type="time" name="start_time" class="form-control" 
                                           value="{{ request('start_time', date('H:i')) }}" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Jam Selesai</label>
                                    <!-- Jam Selesai otomatis +1 Jam dari Start Time -->
                                    <input type="time" name="end_time" class="form-control" 
                                           value="{{ request('start_time') ? date('H:i', strtotime(request('start_time') . ' +1 hour')) : date('H:i', strtotime('+1 hour')) }}" 
                                           required>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('schedule.index') }}" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan Jadwal
                                </button>
                            </div>

                        </form>
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

            classroomSelect.addEventListener('change', function() {
                const selectedClassId = this.value;
                subjectSelect.innerHTML = '<option value="" disabled selected>-- Pilih Mapel --</option>';
                subjectSelect.disabled = true;

                if (selectedClassId) {
                    const filteredAssignments = assignments.filter(a => a.classroom_id == selectedClassId);

                    if (filteredAssignments.length > 0) {
                        subjectSelect.disabled = false;
                        filteredAssignments.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.subject.id;
                            option.textContent = item.subject.name + (item.subject.code ? ` (${item.subject.code})` : '');
                            subjectSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.textContent = "Tidak ada mapel terdaftar di kelas ini";
                        subjectSelect.appendChild(option);
                    }
                }
            });
        });
    </script>
</x-app-layout>