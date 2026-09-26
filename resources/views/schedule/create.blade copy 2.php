<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-8"> <!-- Ubah col-md-12 jadi 8 agar lebih rapi di tengah -->
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

                            <!-- 1. Pilih Kelas Dulu -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kelas</label>
                                <select name="classroom_id" id="classroom_select" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Kelas --</option>
                                    @foreach($classrooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hanya kelas yang Anda ajar yang tampil disini.</small>
                            </div>

                            <!-- 2. Pilih Mata Pelajaran (Otomatis Filter by JS) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mata Pelajaran</label>
                                <select name="subject_id" id="subject_select" class="form-select" required disabled>
                                    <option value="" disabled selected>-- Pilih Kelas Terlebih Dahulu --</option>
                                    {{-- Opsi akan diisi oleh JavaScript --}}
                                </select>
                            </div>

                            <!-- 3. Pilih Hari -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hari</label>
                                <select name="day" class="form-select" required>
                                    @php
                                        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                        $today = \Carbon\Carbon::now()->translatedFormat('l'); // Menggunakan translatedFormat utk Bahasa Indonesia
                                    @endphp
                                    @foreach($days as $day)
                                        <option value="{{ $day }}" {{ $day == $today ? 'selected' : '' }}>{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 4. Jam -->
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Jam Mulai</label>
                                    <input type="time" name="start_time" class="form-control" value="{{ date('H:i') }}" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label fw-bold">Jam Selesai</label>
                                    <input type="time" name="end_time" class="form-control" value="{{ date('H:i', strtotime('+1 hour')) }}" required>
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
            // Data Mapping dari Controller (Teacher -> Class -> Subjects)
            const assignments = @json($assignments);
            
            const classroomSelect = document.getElementById('classroom_select');
            const subjectSelect = document.getElementById('subject_select');

            classroomSelect.addEventListener('change', function() {
                const selectedClassId = this.value;
                
                // Reset Subject Dropdown
                subjectSelect.innerHTML = '<option value="" disabled selected>-- Pilih Mapel --</option>';
                subjectSelect.disabled = true;

                if (selectedClassId) {
                    // Filter Assignment berdasarkan Kelas yang dipilih
                    const filteredAssignments = assignments.filter(a => a.classroom_id == selectedClassId);

                    if (filteredAssignments.length > 0) {
                        subjectSelect.disabled = false;
                        
                        // Loop dan masukkan ke option
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