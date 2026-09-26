@section('title', 'Jadwal Semua Guru')

<x-app-layout>
    <div class="page-content">
        <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Maping</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="fas fa-calendar-alt me-2"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Kelola jadwal pelajaran dan pantau absensi harian.</li>
                </ol>
            </nav>
        </div>

    </div>
        <div class="py-4 container-fluid">

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-calendar-alt me-2"></i> Jadwal Pelajaran (Master)</h4>
                    <p class="mb-0 text-muted small">Pantau dan kelola jadwal seluruh guru di sini.</p>
                </div>

                <form method="GET" class="gap-2 d-flex">
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

            <div class="border-0 shadow card">
                <div class="p-0 card-body">
                    <div id="calendar" class="p-3"></div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="adminScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="text-white modal-header bg-primary">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Jadwal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm" action="{{ route('schedule.store_admin') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <input type="hidden" name="day" id="modal_day">

                    <div id="error-alert" class="mb-3 alert alert-danger d-none align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="error-message"></span>
                    </div>

                    <div class="mb-3 alert alert-info d-flex align-items-center">
                        <i class="fas fa-clock fa-2x me-3"></i>
                        <div>
                            <div class="small text-uppercase fw-bold text-muted">Slot Waktu</div>
                            <strong id="modal_slot_label" class="fs-5">Loading...</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Guru</label>
                        <select name="teacher_id" id="modal_teacher_id" class="form-select select2-modal" required>
                            <option value="" disabled selected>-- Pilih Guru --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kelas</label>
                        <select name="classroom_id" id="modal_classroom_id" class="form-select" required disabled>
                            <option value="">-- Pilih Guru Dulu --</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mata Pelajaran</label>
                        <select name="subject_id" id="modal_subject_id" class="form-select" required disabled>
                            <option value="">-- Pilih Kelas Dulu --</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Jam Mulai</label>
                            <input type="time" name="start_time" id="modal_start_time" class="form-control bg-light" readonly required>
                        </div>
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Jam Selesai</label>
                            <input type="time" name="end_time" id="modal_end_time" class="form-control" required>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>



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
                'url' => route('schedule.show', $s->id),
            ];
        }
    @endphp

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. FULLCALENDAR SETUP ---
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'timeGridWeek,dayGridMonth' },
                locale: 'id',

                // --- KONFIGURASI JAM PELAJARAN ---
                slotMinTime: '07:00:00', // Mulai Jam 1
                slotMaxTime: '18:00:00', // Selesai Jam 12 (15:15 + 45 menit = 16:00)
                slotDuration: '00:45:00', // Durasi per slot 45 menit
                slotLabelInterval: '00:45:00', // Label muncul tiap 45 menit
                expandRows: true, // Agar baris meregang memenuhi tinggi
                allDaySlot: false,

                // Custom Label (Jam 1, Jam 2, dst)
                slotLabelContent: function(arg) {
                    // Hitung Jam Ke-berapa berdasarkan waktu slot
                    let date = arg.date;
                    let totalMinutes = (date.getHours() * 60) + date.getMinutes();
                    let startMinutes = (7 * 60); // Jam 07:00 dalam menit

                    // Rumus: (Waktu Slot - Waktu Mulai) / 45 menit + 1
                    let jamKe = Math.round((totalMinutes - startMinutes) / 45) + 1;

                    // Format tampilan waktu (misal 07:00)
                    let timeText = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                    // Return HTML untuk label
                    return { html: `<div class="text-center fw-bold">Jam ${jamKe}</div><div class="small text-muted">${timeText}</div>` };
                },

                events: @json($events),

                // Klik slot kosong -> Buka Modal
                dateClick: function(info) {
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const date = info.date;

                    // Isi form hari
                    document.getElementById('modal_day').value = days[date.getDay()];

                    // Isi form jam mulai (sesuai slot yang diklik)
                    const h = String(date.getHours()).padStart(2, '0');
                    const m = String(date.getMinutes()).padStart(2, '0');
                    document.getElementById('modal_start_time').value = `${h}:${m}`;

                    // Isi form jam selesai (Otomatis +45 menit)
                    let endDate = new Date(date.getTime() + 45 * 60000); // tambah 45 menit
                    const endH = String(endDate.getHours()).padStart(2, '0');
                    const endM = String(endDate.getMinutes()).padStart(2, '0');
                    document.getElementById('modal_end_time').value = `${endH}:${endM}`;

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


            // --- 2. LOGIC FILTER DROPDOWN (ADMIN) - TETAP SAMA ---
            const allAssignments = @json($allAssignments);

            const teacherSelect = document.getElementById('modal_teacher_id');
            const classroomSelect = document.getElementById('modal_classroom_id');
            const subjectSelect = document.getElementById('modal_subject_id');

            teacherSelect.addEventListener('change', function() {
                const teacherId = this.value;
                classroomSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                subjectSelect.innerHTML = '<option value="">-- Pilih Mapel --</option>';
                classroomSelect.disabled = true;
                subjectSelect.disabled = true;

                if (!teacherId) return;

                const myAssignments = allAssignments.filter(a => a.teacher_id == teacherId);
                const uniqueClasses = [];
                const map = new Map();
                for (const item of myAssignments) {
                    if(!map.has(item.classroom_id)){
                        map.set(item.classroom_id, true);
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
    </script> -->

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- 1. DATA DARI DATABASE ---
        const existingSchedules = @json($schedules);
        const allAssignments = @json($allAssignments);

        // --- 2. DEFINISI JADWAL SEKOLAH ---
        const scheduleMap = {
            '07:00': { label: 'Jam 1',           duration: 45, type: 'lesson' },
            '07:45': { label: 'Jam 2',           duration: 45, type: 'lesson' },
            '08:30': { label: 'Jam 3',           duration: 45, type: 'lesson' },
            '09:15': { label: 'Jam 4',           duration: 45, type: 'lesson' },
            '10:00': { label: 'Istirahat I',     duration: 15, type: 'break' },  // <--- Tipe Break
            '10:15': { label: 'Jam 5',           duration: 45, type: 'lesson' },
            '11:00': { label: 'Jam 6',           duration: 45, type: 'lesson' },
            '11:45': { label: 'Jam 7',           duration: 45, type: 'lesson' },
            '12:30': { label: 'Ishoma',          duration: 45, type: 'break' },  // <--- Tipe Break
            '13:15': { label: 'Jam 8',           duration: 45, type: 'lesson' },
            '14:00': { label: 'Jam 9',           duration: 45, type: 'lesson' },
            '14:45': { label: 'Jam 10',          duration: 45, type: 'lesson' },
            '15:30': { label: 'Istirahat III',   duration: 15, type: 'break' },  // <--- Tipe Break
            '15:45': { label: 'Jam 11',          duration: 45, type: 'lesson' },
            '16:30': { label: 'Jam 12',          duration: 45, type: 'lesson' },
            '17:15': { label: 'Jam 13',          duration: 45, type: 'lesson' }
        };

        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'timeGridWeek,dayGridMonth' },
            locale: 'id',
            slotMinTime: '07:00:00',
            slotMaxTime: '18:15:00',
            slotDuration: '00:15:00',
            slotLabelInterval: '00:15:00',
            expandRows: true,
            allDaySlot: false,

            slotLabelContent: function(arg) {
                let timeText = arg.date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
                if (scheduleMap[timeText]) {
                    let info = scheduleMap[timeText];
                    let colorClass = info.type === 'break' ? 'text-danger fw-bold' : 'text-primary fw-bold';
                    let icon = info.type === 'break' ? '<i class="fas fa-coffee"></i>' : '';
                    return {
                        html: `<div class="${colorClass}" style="font-size:10px; line-height:1.2;">${icon} ${info.label}</div>
                               <div class="small text-muted" style="font-size:9px;">${timeText}</div>`
                    };
                }
                return { html: '' };
            },

            events: @json($events),

            // --- VALIDASI SAAT KLIK SLOT (DATE CLICK) ---
            dateClick: function(info) {
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const date = info.date;
                let h = String(date.getHours()).padStart(2, '0');
                let m = String(date.getMinutes()).padStart(2, '0');
                let timeKey = `${h}:${m}`;

                // 1. Cek apakah slot terdaftar di map
                if (!scheduleMap[timeKey]) {
                    alert('Mohon klik tepat pada jam mulai pelajaran (sesuai label di kiri).');
                    return;
                }

                const slotInfo = scheduleMap[timeKey];

                // 2. VALIDASI JAM ISTIRAHAT (BARU)
                // Jika tipe slot adalah 'break', tampilkan alert dan jangan buka modal
                if (slotInfo.type === 'break') {
                    alert('Maaf, Anda tidak dapat menginput jadwal pada jam ' + slotInfo.label + '.');
                    return; // Stop proses di sini
                }

                // Jika lolos validasi, Lanjutkan buka modal...

                // RESET FORM & ERROR
                document.getElementById('scheduleForm').reset();
                document.getElementById('error-alert').classList.add('d-none');
                document.getElementById('modal_classroom_id').disabled = true;
                document.getElementById('modal_subject_id').disabled = true;

                document.getElementById('modal_day').value = days[date.getDay()];
                document.getElementById('modal_start_time').value = timeKey;
                document.getElementById('modal_slot_label').innerText = `${slotInfo.label} (${slotInfo.duration} Menit)`;

                let endDate = new Date(date.getTime() + slotInfo.duration * 60000);
                const endH = String(endDate.getHours()).padStart(2, '0');
                const endM = String(endDate.getMinutes()).padStart(2, '0');
                document.getElementById('modal_end_time').value = `${endH}:${endM}`;

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


        // --- 3. VALIDASI SAAT SUBMIT FORM (BENTROK GURU/KELAS) ---
        const form = document.getElementById('scheduleForm');
        form.addEventListener('submit', function(e) {

            const inputTeacher = document.getElementById('modal_teacher_id').value;
            const inputClass = document.getElementById('modal_classroom_id').value;
            const inputDay = document.getElementById('modal_day').value;
            const inputStart = document.getElementById('modal_start_time').value;

            if(!inputTeacher || !inputClass) return;

            // Cek Bentrok Kelas
            const classBusy = existingSchedules.find(s => {
                const dbStart = s.start_time.substring(0, 5);
                return s.day.toLowerCase() === inputDay.toLowerCase() &&
                       dbStart === inputStart &&
                       s.classroom_id == inputClass;
            });

            // Cek Bentrok Guru
            const teacherBusy = existingSchedules.find(s => {
                const dbStart = s.start_time.substring(0, 5);
                return s.day.toLowerCase() === inputDay.toLowerCase() &&
                       dbStart === inputStart &&
                       s.teacher_id == inputTeacher;
            });

            const errorBox = document.getElementById('error-alert');
            const errorMsg = document.getElementById('error-message');

            if (classBusy) {
                e.preventDefault();
                let teacherName = classBusy.teacher ? classBusy.teacher.name : 'Guru Lain';
                errorMsg.innerHTML = `<strong>BENTROK KELAS:</strong> Kelas ini sudah diisi oleh <u>${teacherName}</u> pada jam tersebut.`;
                errorBox.classList.remove('d-none');
                return;
            }

            if (teacherBusy) {
                e.preventDefault();
                let className = teacherBusy.classroom ? teacherBusy.classroom.name : 'Kelas Lain';
                errorMsg.innerHTML = `<strong>BENTROK GURU:</strong> Guru ini sedang mengajar di <u>${className}</u> pada jam tersebut.`;
                errorBox.classList.remove('d-none');
                return;
            }
        });

        // Hide error on change
        document.getElementById('modal_teacher_id').addEventListener('change', () => {
            document.getElementById('error-alert').classList.add('d-none');
        });
        document.getElementById('modal_classroom_id').addEventListener('change', () => {
            document.getElementById('error-alert').classList.add('d-none');
        });


        // --- 4. LOGIC DROPDOWN ---
        const teacherSelect = document.getElementById('modal_teacher_id');
        const classroomSelect = document.getElementById('modal_classroom_id');
        const subjectSelect = document.getElementById('modal_subject_id');

        teacherSelect.addEventListener('change', function() {
            const teacherId = this.value;
            classroomSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
            subjectSelect.innerHTML = '<option value="">-- Pilih Mapel --</option>';
            classroomSelect.disabled = true;
            subjectSelect.disabled = true;

            if (!teacherId) return;

            const myAssignments = allAssignments.filter(a => a.teacher_id == teacherId);
            const uniqueClasses = [];
            const map = new Map();
            for (const item of myAssignments) {
                if(!map.has(item.classroom_id)){
                    map.set(item.classroom_id, true);
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
        /* Membesarkan sedikit area header jam agar muat tulisan "Jam 1" */
        .fc-timegrid-slot-label-cushion { white-space: normal !important; width: 60px; }
        .fc-timegrid-axis-cushion { max-width: 60px; }
    </style>

</x-app-layout>
