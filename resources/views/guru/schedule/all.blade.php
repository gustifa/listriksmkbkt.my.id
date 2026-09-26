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
    <!--end breadcrumb-->
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

    <!-- MODAL TAMBAH / EDIT JADWAL -->
    <div class="modal fade" id="adminScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="text-white modal-header bg-primary">
                    <h5 class="modal-title" id="modalTitle"><i class="fas fa-plus-circle"></i> Tambah Jadwal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <!-- Form Action akan di-set lewat JS -->
                <form id="scheduleForm" action="{{ route('schedule.store_admin') }}" method="POST">
                    @csrf
                    <!-- Container untuk spoofing method PUT saat edit -->
                    <div id="method-spoofing"></div>

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

                        <!-- INPUT ROOM / BENGKEL -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ruangan / Bengkel</label>
                            <select name="room_id" id="modal_room_id" class="form-select">
                                <option value="" selected>-- Tidak Ada Ruangan Khusus --</option>
                                @if(isset($rooms))
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text text-muted small">Pilih ruangan jika pelajaran dilakukan di Lab/Bengkel.</div>
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
                    <div class="modal-footer d-flex justify-content-between">
                        <!-- Tombol Delete (Hanya muncul saat Edit) -->
                        <button type="button" id="btnDelete" class="btn btn-danger d-none" onclick="deleteSchedule()">
                            <i class="fas fa-trash me-1"></i> Hapus
                        </button>

                        <div class="gap-2 d-flex ms-auto">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="btnSave">Simpan</button>
                        </div>
                    </div>
                </form>

                <!-- Form Hapus Tersembunyi -->
                <form id="deleteForm" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
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

            $color = '#' . substr(md5($s->teacher_id), 0, 6);

            $events[] = [
                'id' => $s->id,
                'title' => ($s->teacher->name ?? 'Guru') . "\n" . ($s->subject->name ?? 'Mapel') . "\n(" . ($s->classroom->name ?? 'Kls') . ")",
                'startTime' => \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                'endTime' => \Carbon\Carbon::parse($s->end_time)->format('H:i'),
                'daysOfWeek' => [$dayMap[$dayKey]],
                'color' => $color,
                // Kita simpan data detail di extendedProps untuk diakses JS
                'extendedProps' => [
                    'teacher_id' => $s->teacher_id,
                    'classroom_id' => $s->classroom_id,
                    'subject_id' => $s->subject_id,
                    'room_id' => $s->room_id,
                    'day' => $s->day
                ]
            ];
        }
    @endphp

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- DATA ---
            const existingSchedules = @json($schedules);
            const allAssignments = @json($allAssignments);

            // --- DEFINISI SLOT ---
            const scheduleMap = {
                '07:00': { label: 'Jam 1', duration: 45, type: 'lesson' },
                '07:45': { label: 'Jam 2', duration: 45, type: 'lesson' },
                '08:30': { label: 'Jam 3', duration: 45, type: 'lesson' },
                '09:15': { label: 'Jam 4', duration: 45, type: 'lesson' },
                '10:00': { label: 'Istirahat I', duration: 15, type: 'break' },
                '10:15': { label: 'Jam 5', duration: 45, type: 'lesson' },
                '11:00': { label: 'Jam 6', duration: 45, type: 'lesson' },
                '11:45': { label: 'Jam 7', duration: 45, type: 'lesson' },
                '12:30': { label: 'Ishoma', duration: 45, type: 'break' },
                '13:15': { label: 'Jam 8', duration: 45, type: 'lesson' },
                '14:00': { label: 'Jam 9', duration: 45, type: 'lesson' },
                '14:45': { label: 'Jam 10', duration: 45, type: 'lesson' },
                '15:30': { label: 'Istirahat III', duration: 15, type: 'break' },
                '15:45': { label: 'Jam 11', duration: 45, type: 'lesson' },
                '16:30': { label: 'Jam 12', duration: 45, type: 'lesson' },
                '17:15': { label: 'Jam 13', duration: 45, type: 'lesson' }
            };

            // --- ELEMEN DOM ---
            const modalEl = document.getElementById('adminScheduleModal');
            const myModal = new bootstrap.Modal(modalEl);
            const form = document.getElementById('scheduleForm');
            const deleteForm = document.getElementById('deleteForm');

            // Dropdowns
            const teacherSelect = document.getElementById('modal_teacher_id');
            const classroomSelect = document.getElementById('modal_classroom_id');
            const subjectSelect = document.getElementById('modal_subject_id');

            // --- HELPER FUNCTIONS FOR DROPDOWNS ---
            function updateClassrooms(teacherId, selectedClassId = null) {
                classroomSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                subjectSelect.innerHTML = '<option value="">-- Pilih Mapel --</option>';
                classroomSelect.disabled = true;
                subjectSelect.disabled = true;

                if (!teacherId) return;

                const myAssignments = allAssignments.filter(a => a.teacher_id == teacherId);
                const uniqueClasses = [];
                const map = new Map();

                myAssignments.forEach(item => {
                    if(!map.has(item.classroom_id)){
                        map.set(item.classroom_id, true);
                        uniqueClasses.push({ id: item.classroom_id, name: item.classroom.name });
                    }
                });

                if (uniqueClasses.length > 0) {
                    classroomSelect.disabled = false;
                    uniqueClasses.forEach(cls => {
                        const opt = document.createElement('option');
                        opt.value = cls.id;
                        opt.textContent = cls.name;
                        if(selectedClassId && cls.id == selectedClassId) opt.selected = true;
                        classroomSelect.appendChild(opt);
                    });

                    // Jika ada selectedClassId, trigger update subject
                    if(selectedClassId) updateSubjects(teacherId, selectedClassId);
                } else {
                    classroomSelect.innerHTML = '<option>Guru ini belum di-mapping!</option>';
                }
            }

            function updateSubjects(teacherId, classroomId, selectedSubjectId = null) {
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
                        if(selectedSubjectId && m.subject_id == selectedSubjectId) opt.selected = true;
                        subjectSelect.appendChild(opt);
                    });
                }
            }

            // Event Listeners Dropdowns
            teacherSelect.addEventListener('change', function() {
                updateClassrooms(this.value);
            });

            classroomSelect.addEventListener('change', function() {
                updateSubjects(teacherSelect.value, this.value);
            });

            // --- FULLCALENDAR ---
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
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
                        return { html: `<div class="${colorClass}" style="font-size:10px; line-height:1.2;">${icon} ${info.label}</div><div class="small text-muted" style="font-size:9px;">${timeText}</div>` };
                    }
                    return { html: '' };
                },
                events: @json($events),

                // 1. KLIK SLOT KOSONG (TAMBAH DATA)
                dateClick: function(info) {
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const date = info.date;
                    let h = String(date.getHours()).padStart(2, '0');
                    let m = String(date.getMinutes()).padStart(2, '0');
                    let timeKey = `${h}:${m}`;

                    if (!scheduleMap[timeKey]) { alert('Klik tepat pada jam mulai.'); return; }
                    const slotInfo = scheduleMap[timeKey];
                    if (slotInfo.type === 'break') { alert('Tidak bisa input di jam istirahat.'); return; }

                    // RESET FORM UNTUK CREATE
                    form.reset();
                    form.action = "{{ route('schedule.store_admin') }}"; // Route Create
                    document.getElementById('method-spoofing').innerHTML = ''; // Hapus method PUT
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Tambah Jadwal';
                    document.getElementById('btnDelete').classList.add('d-none');
                    document.getElementById('btnSave').innerText = 'Simpan';
                    document.getElementById('error-alert').classList.add('d-none');

                    // Reset Dropdowns
                    classroomSelect.innerHTML = '<option value="">-- Pilih Guru Dulu --</option>';
                    classroomSelect.disabled = true;
                    subjectSelect.innerHTML = '<option value="">-- Pilih Kelas Dulu --</option>';
                    subjectSelect.disabled = true;

                    // Isi Data Waktu
                    document.getElementById('modal_day').value = days[date.getDay()];
                    document.getElementById('modal_start_time').value = timeKey;
                    document.getElementById('modal_slot_label').innerText = `${slotInfo.label} (${slotInfo.duration} Menit)`;

                    let endDate = new Date(date.getTime() + slotInfo.duration * 60000);
                    const endH = String(endDate.getHours()).padStart(2, '0');
                    const endM = String(endDate.getMinutes()).padStart(2, '0');
                    document.getElementById('modal_end_time').value = `${endH}:${endM}`;

                    myModal.show();
                },

                // 2. KLIK EVENT ADA (EDIT DATA)
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    const props = info.event.extendedProps;
                    const eventId = info.event.id;

                    // SETUP FORM UNTUK EDIT
                    // Ganti URL ke route update. Gunakan placeholder ID atau route helper jika tersedia
                    form.action = "{{ url('/schedule') }}/" + eventId;
                    document.getElementById('method-spoofing').innerHTML = '<input type="hidden" name="_method" value="PUT">';

                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Jadwal';
                    document.getElementById('btnDelete').classList.remove('d-none');
                    document.getElementById('btnDelete').setAttribute('data-id', eventId);
                    document.getElementById('btnSave').innerText = 'Update';
                    document.getElementById('error-alert').classList.add('d-none');

                    // Isi Data
                    document.getElementById('modal_day').value = props.day;
                    document.getElementById('modal_teacher_id').value = props.teacher_id;

                    // Trigger update options dan set value (Chained)
                    updateClassrooms(props.teacher_id, props.classroom_id);
                    // updateSubjects dipanggil otomatis di dalam updateClassrooms jika classId ada
                    // Tapi kita perlu memastikan subject terseleksi.
                    // Karena updateClassrooms async? Tidak, ini sync. Jadi aman.
                    // Namun kita harus panggil updateSubjects manual untuk set selectedSubjectId
                    updateSubjects(props.teacher_id, props.classroom_id, props.subject_id);

                    if (props.room_id) {
                        document.getElementById('modal_room_id').value = props.room_id;
                    } else {
                        document.getElementById('modal_room_id').value = "";
                    }

                    // Waktu
                    // Format dari FullCalendar ISO string ke H:i
                    const start = info.event.start;
                    const end = info.event.end;
                    const sH = String(start.getHours()).padStart(2, '0');
                    const sM = String(start.getMinutes()).padStart(2, '0');
                    const eH = String(end.getHours()).padStart(2, '0');
                    const eM = String(end.getMinutes()).padStart(2, '0');

                    document.getElementById('modal_start_time').value = `${sH}:${sM}`;
                    document.getElementById('modal_end_time').value = `${eH}:${eM}`;

                    // Cari label slot
                    const timeKey = `${sH}:${sM}`;
                    if(scheduleMap[timeKey]){
                         document.getElementById('modal_slot_label').innerText = `${scheduleMap[timeKey].label} (Edit Mode)`;
                    } else {
                         document.getElementById('modal_slot_label').innerText = "Edit Waktu";
                    }

                    myModal.show();
                }
            });
            calendar.render();

            // --- DELETE FUNCTION ---
            window.deleteSchedule = function() {
                const id = document.getElementById('btnDelete').getAttribute('data-id');
                if(!id) return;

                Swal.fire({
                    title: 'Hapus Jadwal?',
                    text: "Jadwal yang dihapus tidak bisa dikembalikan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteForm.action = "{{ url('/schedule') }}/" + id;
                        deleteForm.submit();
                    }
                })
            };

            // Validasi Submit (Conflict Check)
            form.addEventListener('submit', function(e) {
                // Logika validasi client-side bisa ditambahkan di sini jika perlu
                // Saat ini mengandalkan server-side validation & error return
                // Tapi kita bisa cek bentrok sederhana dari data existingSchedules di client side
                // untuk UX yang lebih cepat.

                const inputTeacher = document.getElementById('modal_teacher_id').value;
                const inputClass = document.getElementById('modal_classroom_id').value;
                const inputDay = document.getElementById('modal_day').value;
                const inputStart = document.getElementById('modal_start_time').value;

                // Ambil ID jika sedang edit (dari action form)
                const isEdit = form.action.includes('?'); // Kasar, tapi PUT spoofing ada di div
                const methodInput = document.querySelector('input[name="_method"]');
                const isPut = methodInput && methodInput.value === 'PUT';
                // Extract ID from action url if needed
                const currentId = isPut ? form.action.split('/').pop() : null;

                const classBusy = existingSchedules.find(s => {
                    if(isPut && s.id == currentId) return false; // Skip diri sendiri saat edit
                    const dbStart = s.start_time.substring(0, 5);
                    return s.day.toLowerCase() === inputDay.toLowerCase() &&
                           dbStart === inputStart &&
                           s.classroom_id == inputClass;
                });

                const teacherBusy = existingSchedules.find(s => {
                    if(isPut && s.id == currentId) return false;
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
                    errorMsg.innerHTML = `<strong>BENTROK KELAS:</strong> Kelas ini sudah diisi oleh <u>${teacherName}</u>.`;
                    errorBox.classList.remove('d-none');
                    return;
                }

                if (teacherBusy) {
                    e.preventDefault();
                    let className = teacherBusy.classroom ? teacherBusy.classroom.name : 'Kelas Lain';
                    errorMsg.innerHTML = `<strong>BENTROK GURU:</strong> Guru ini sedang mengajar di <u>${className}</u>.`;
                    errorBox.classList.remove('d-none');
                    return;
                }
            });

            // Hide error on change
            teacherSelect.addEventListener('change', () => document.getElementById('error-alert').classList.add('d-none'));
            classroomSelect.addEventListener('change', () => document.getElementById('error-alert').classList.add('d-none'));

            // Fix modal z-index for fullcalendar
            var calendarTab = document.getElementById('calendar-tab');
            if (calendarTab) {
                calendarTab.addEventListener('shown.bs.tab', function (e) { calendar.render(); });
            }
        });
    </script>

    <style>
        .fc-event-title { font-size: 0.8em; white-space: pre-wrap; }
        .fc-timegrid-slot-label-cushion { white-space: normal !important; width: 60px; }
        .fc-timegrid-axis-cushion { max-width: 60px; }
    </style>

</x-app-layout>
