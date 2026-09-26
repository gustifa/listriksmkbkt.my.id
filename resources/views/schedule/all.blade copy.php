@section('title', 'Jadwal Semua Guru')

<x-app-layout>
    <div class="page-content">
        <div class="container-fluid py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-calendar-alt me-2"></i> Jadwal Pelajaran (Master)</h4>
                    <p class="text-muted small mb-0">Pantau dan kelola jadwal seluruh guru di sini.</p>
                </div>
                
                <!-- Filter Guru -->
                <form method="GET" class="d-flex gap-2">
                    <select name="teacher_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Guru --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('teacher_id'))
                        <a href="{{ route('schedule.all') }}" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i></a>
                    @endif
                </form>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow border-0">
                <div class="card-body p-0">
                    <div id="calendar" class="p-3"></div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL TAMBAH JADWAL (ADMIN) -->
    <div class="modal fade" id="adminScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Jadwal (Admin Mode)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('schedule.store_admin') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        
                        <!-- Input Hidden Hari -->
                        <input type="hidden" name="day" id="modal_day">

                        <!-- 1. Pilih Guru -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Guru</label>
                            <select name="teacher_id" id="modal_teacher_id" class="form-select select2-modal" required>
                                <option value="" disabled selected>-- Pilih Guru --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Pilih Kelas (Filter by JS) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kelas</label>
                            <select name="classroom_id" id="modal_classroom_id" class="form-select" required disabled>
                                <option value="">-- Pilih Guru Dulu --</option>
                            </select>
                        </div>

                        <!-- 3. Pilih Mapel (Filter by JS) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mata Pelajaran</label>
                            <select name="subject_id" id="modal_subject_id" class="form-select" required disabled>
                                <option value="">-- Pilih Kelas Dulu --</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Jam Mulai</label>
                                <input type="time" name="start_time" id="modal_start_time" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold">Jam Selesai</label>
                                <input type="time" name="end_time" id="modal_end_time" class="form-control" required>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DATA & SCRIPTS -->
    @php
        $events = [];
        $dayMap = ['minggu'=>0, 'senin'=>1, 'selasa'=>2, 'rabu'=>3, 'kamis'=>4, 'jumat'=>5, 'sabtu'=>6];

        foreach($schedules as $s) {
            $dayKey = strtolower(trim($s->day));
            if (!isset($dayMap[$dayKey])) continue;

            // Warna beda per guru (opsional, hash dari ID)
            $color = '#' . substr(md5($s->teacher_id), 0, 6);

            $events[] = [
                'id' => $s->id,
                'title' => ($s->teacher->name ?? 'Guru') . "\n" . ($s->subject->name ?? 'Mapel') . "\n(" . ($s->classroom->name ?? 'Kls') . ")",
                'startTime' => \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                'endTime' => \Carbon\Carbon::parse($s->end_time)->format('H:i'),
                'daysOfWeek' => [$dayMap[$dayKey]],
                'color' => $color,
                'url' => route('schedule.show', $s->id), // Klik untuk hapus/lihat detail
            ];
        }
    @endphp

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. FULLCALENDAR SETUP ---
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'timeGridWeek,dayGridMonth' },
                locale: 'id',
                slotMinTime: '06:00:00',
                slotMaxTime: '23:00:00',
                allDaySlot: false,
                contentHeight: 'auto',
                events: @json($events),
                
                // Klik slot kosong -> Buka Modal
                dateClick: function(info) {
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const date = info.date;
                    
                    // Isi form otomatis
                    document.getElementById('modal_day').value = days[date.getDay()];
                    
                    const h = String(date.getHours()).padStart(2, '0');
                    const m = String(date.getMinutes()).padStart(2, '0');
                    document.getElementById('modal_start_time').value = `${h}:${m}`;
                    
                    // Perkiraan selesai +1 jam
                    let endH = date.getHours() + 1;
                    document.getElementById('modal_end_time').value = `${String(endH).padStart(2,'0')}:${m}`;

                    // Buka Modal
                    var myModal = new bootstrap.Modal(document.getElementById('adminScheduleModal'));
                    myModal.show();
                },

                eventClick: function(info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault();
                        if(confirm("Lihat detail jadwal ini?")) {
                            window.location.href = info.event.url;
                        }
                    }
                }
            });
            calendar.render();


            // --- 2. LOGIC FILTER DROPDOWN (ADMIN) ---
            const allAssignments = @json($allAssignments); // Data mapping lengkap

            const teacherSelect = document.getElementById('modal_teacher_id');
            const classroomSelect = document.getElementById('modal_classroom_id');
            const subjectSelect = document.getElementById('modal_subject_id');

            // Saat Admin memilih Guru
            teacherSelect.addEventListener('change', function() {
                const teacherId = this.value;
                
                // Reset Kelas & Mapel
                classroomSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                subjectSelect.innerHTML = '<option value="">-- Pilih Mapel --</option>';
                classroomSelect.disabled = true;
                subjectSelect.disabled = true;

                if (!teacherId) return;

                // Cari Mapping milik guru ini
                // Kita cari kelas-kelas yang unik dimana guru ini mengajar
                const myAssignments = allAssignments.filter(a => a.teacher_id == teacherId);
                
                // Ekstrak Kelas Unik (Manual karena object JS)
                const uniqueClasses = [];
                const map = new Map();
                for (const item of myAssignments) {
                    if(!map.has(item.classroom_id)){
                        map.set(item.classroom_id, true);    // set any value to Map
                        uniqueClasses.push({ id: item.classroom_id, name: item.classroom.name });
                    }
                }

                if (uniqueClasses.length > 0) {
                    classroomSelect.disabled = false;
                    uniqueClasses.forEach(cls => {
                        const opt = document.createElement('option');
                        opt.value = cls.id;
                        opt.textContent = cls.name;
                        classroomSelect.appendChild(opt);
                    });
                } else {
                    classroomSelect.innerHTML = '<option>Guru ini belum di-mapping!</option>';
                }
            });

            // Saat Admin memilih Kelas -> Tampilkan Mapel yg sesuai mapping
            classroomSelect.addEventListener('change', function() {
                const teacherId = teacherSelect.value;
                const classroomId = this.value;

                subjectSelect.innerHTML = '<option value="">-- Pilih Mapel --</option>';
                subjectSelect.disabled = true;

                if(!classroomId) return;

                const validMapels = allAssignments.filter(a => a.teacher_id == teacherId && a.classroom_id == classroomId);

                if (validMapels.length > 0) {
                    subjectSelect.disabled = false;
                    validMapels.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.subject_id;
                        opt.textContent = m.subject.name;
                        subjectSelect.appendChild(opt);
                    });
                }
            });

        });
    </script>
    
    <style>
        .fc-event-title { font-size: 0.8em; white-space: pre-wrap; }
        .fc-toolbar-title { font-size: 1.2rem !important; }
    </style>

</x-app-layout>