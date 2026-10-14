@section('title', 'Jadwal Semua Guru')

<x-app-layout>
    <div class="page-content">
        <!--breadcrumb-->
        <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
            <div class="breadcrumb-title pe-3">Mapping</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="p-0 mb-0 breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}"><i class="fas fa-calendar-alt me-2"></i></a></li>
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
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error) 
                            <li>{{ $error }}</li> 
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                
                <form id="scheduleForm" action="{{ route('schedule.store_admin') }}" method="POST">
                    @csrf
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

                        <!-- TIME SLOT JAM MULAI & TIME SLOT JAM SELESAI -->
                        <div class="row">
                            <div class="mb-3 col-6">
                                <label class="form-label fw-bold">Jam Mulai</label>
                                <select name="start_time" id="modal_start_time" class="form-select" required onchange="handleStartSlotChange(this.value)">
                                    <option value="" disabled selected>-- Pilih Jam Mulai --</option>
                                    @if(isset($timeSlots))
                                        @foreach($timeSlots as $slot)
                                            @if($slot->type === 'lesson')
                                                <option value="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}" 
                                                        data-end="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}"
                                                        data-label="{{ $slot->label }}">
                                                    {{ $slot->label }} ({{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }})
                                                </option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="mb-3 col-6">
                                <label class="form-label fw-bold">Jam Selesai</label>
                                <select name="end_time" id="modal_end_time" class="form-select" required onchange="updateDurationLabel()">
                                    <option value="" disabled selected>-- Pilih Jam Selesai --</option>
                                    @if(isset($timeSlots))
                                        @foreach($timeSlots as $slot)
                                            <option value="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}"
                                                    data-label="{{ $slot->label }}">
                                                Selesai {{ $slot->label }} ({{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer d-flex justify-content-between">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Handler saat Jam Mulai dipilih -> otomatis pilihkan Jam Selesai yang sesuai
        function handleStartSlotChange(startTime) {
            const startSelect = document.getElementById('modal_start_time');
            const selectedStartOption = startSelect.options[startSelect.selectedIndex];

            if (selectedStartOption && selectedStartOption.dataset.end) {
                const targetEnd = selectedStartOption.dataset.end;
                const endSelect = document.getElementById('modal_end_time');

                // Pilih otomatis option Jam Selesai yang cocok
                for (let i = 0; i < endSelect.options.length; i++) {
                    if (endSelect.options[i].value === targetEnd) {
                        endSelect.selectedIndex = i;
                        break;
                    }
                }

                updateDurationLabel();
            }
        }

        // Hitung durasi dan tampilkan label info
        function updateDurationLabel() {
            const startVal = document.getElementById('modal_start_time').value;
            const endVal = document.getElementById('modal_end_time').value;
            const startSelect = document.getElementById('modal_start_time');
            const selectedOption = startSelect.options[startSelect.selectedIndex];
            const labelName = selectedOption ? selectedOption.dataset.label : 'Slot Waktu';

            if (startVal && endVal) {
                const [sH, sM] = startVal.split(':').map(Number);
                const [eH, eM] = endVal.split(':').map(Number);
                const duration = (eH * 60 + eM) - (sH * 60 + sM);

                if (duration > 0) {
                    document.getElementById('modal_slot_label').innerText = `${labelName} (${startVal} - ${endVal} | ${duration} Menit)`;
                } else {
                    document.getElementById('modal_slot_label').innerText = "Waktu selesai harus lebih besar dari waktu mulai!";
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {

            // --- DATA ---
            const existingSchedules = @json($schedules);
            const allAssignments = @json($allAssignments);
            const rawTimeSlots = @json($timeSlots ?? []);

            // --- MAP SLOT JAM DINAMIS ---
            const scheduleMap = {};
            rawTimeSlots.forEach(slot => {
                const startKey = slot.start_time.substring(0, 5);
                
                const startArr = slot.start_time.split(':');
                const endArr = slot.end_time.split(':');
                const startMin = parseInt(startArr[0]) * 60 + parseInt(startArr[1]);
                const endMin = parseInt(endArr[0]) * 60 + parseInt(endArr[1]);
                const durationMin = endMin - startMin;

                scheduleMap[startKey] = {
                    label: slot.label,
                    duration: durationMin,
                    type: slot.type,
                    end_time: slot.end_time.substring(0, 5)
                };
            });

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
                    if(item.classroom && !map.has(item.classroom_id)){
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
                        if(m.subject) {
                            const opt = document.createElement('option');
                            opt.value = m.subject_id;
                            opt.textContent = m.subject.name;
                            if(selectedSubjectId && m.subject_id == selectedSubjectId) opt.selected = true;
                            subjectSelect.appendChild(opt);
                        }
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
                slotMaxTime: '19:00:00',
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

                // 1. KLIK SLOT KOSONG (TAMBAH JADWAL ADMIN)
                dateClick: function(info) {
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const date = info.date;
                    let h = String(date.getHours()).padStart(2, '0');
                    let m = String(date.getMinutes()).padStart(2, '0');
                    let timeKey = `${h}:${m}`;

                    if (!scheduleMap[timeKey]) { alert('Klik tepat pada slot jam pelajaran.'); return; }
                    const slotInfo = scheduleMap[timeKey];
                    if (slotInfo.type === 'break') { alert('Tidak bisa memilih jam istirahat.'); return; }

                    form.reset();
                    form.action = "{{ route('schedule.store_admin') }}";
                    document.getElementById('method-spoofing').innerHTML = '';
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Tambah Jadwal (Admin)';
                    document.getElementById('btnDelete').classList.add('d-none');
                    document.getElementById('btnSave').innerText = 'Simpan';
                    document.getElementById('error-alert').classList.add('d-none');

                    classroomSelect.innerHTML = '<option value="">-- Pilih Guru Dulu --</option>';
                    classroomSelect.disabled = true;
                    subjectSelect.innerHTML = '<option value="">-- Pilih Kelas Dulu --</option>';
                    subjectSelect.disabled = true;

                    document.getElementById('modal_day').value = days[date.getDay()];

                    // Pilih Time Slot Jam Mulai otomatis dari slot kalender yang diklik
                    const startSelect = document.getElementById('modal_start_time');
                    for (let i = 0; i < startSelect.options.length; i++) {
                        if (startSelect.options[i].value === timeKey) {
                            startSelect.selectedIndex = i;
                            handleStartSlotChange(timeKey);
                            break;
                        }
                    }

                    myModal.show();
                },

                // 2. KLIK EVENT (EDIT JADWAL ADMIN)
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    const props = info.event.extendedProps;
                    const eventId = info.event.id;

                    let updateUrl = "{{ route('schedule.update_admin', ':id') }}";
                    form.action = updateUrl.replace(':id', eventId);

                    document.getElementById('method-spoofing').innerHTML = '<input type="hidden" name="_method" value="PUT">';
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Jadwal (Admin)';
                    document.getElementById('btnDelete').classList.remove('d-none');
                    document.getElementById('btnDelete').setAttribute('data-id', eventId);
                    document.getElementById('btnSave').innerText = 'Update';
                    document.getElementById('error-alert').classList.add('d-none');

                    document.getElementById('modal_day').value = props.day;
                    document.getElementById('modal_teacher_id').value = props.teacher_id;

                    updateClassrooms(props.teacher_id, props.classroom_id);
                    updateSubjects(props.teacher_id, props.classroom_id, props.subject_id);

                    if (props.room_id) {
                        document.getElementById('modal_room_id').value = props.room_id;
                    } else {
                        document.getElementById('modal_room_id').value = "";
                    }

                    const start = info.event.start;
                    const end = info.event.end;
                    const sH = String(start.getHours()).padStart(2, '0');
                    const sM = String(start.getMinutes()).padStart(2, '0');
                    const eH = String(end.getHours()).padStart(2, '0');
                    const eM = String(end.getMinutes()).padStart(2, '0');
                    const timeKey = `${sH}:${sM}`;
                    const endTimeKey = `${eH}:${eM}`;

                    // Set Jam Mulai
                    const startSelect = document.getElementById('modal_start_time');
                    for (let i = 0; i < startSelect.options.length; i++) {
                        if (startSelect.options[i].value === timeKey) {
                            startSelect.selectedIndex = i;
                            break;
                        }
                    }

                    // Set Jam Selesai
                    const endSelect = document.getElementById('modal_end_time');
                    for (let i = 0; i < endSelect.options.length; i++) {
                        if (endSelect.options[i].value === endTimeKey) {
                            endSelect.selectedIndex = i;
                            break;
                        }
                    }

                    updateDurationLabel();
                    myModal.show();
                }
            });
            calendar.render();

            // --- DELETE FUNCTION ADMIN ---
            window.deleteSchedule = function() {
                const id = document.getElementById('btnDelete').getAttribute('data-id');
                if (!id) return;

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
                        let deleteUrl = "{{ route('schedule.destroy_admin', ':id') }}";
                        deleteForm.action = deleteUrl.replace(':id', id);
                        deleteForm.submit();
                    }
                });
            };

            // Validasi Conflict Client-side
            form.addEventListener('submit', function(e) {
                const inputTeacher = document.getElementById('modal_teacher_id').value;
                const inputClass = document.getElementById('modal_classroom_id').value;
                const inputDay = document.getElementById('modal_day').value;
                const inputStart = document.getElementById('modal_start_time').value;

                const methodInput = document.querySelector('input[name="_method"]');
                const isPut = methodInput && methodInput.value === 'PUT';
                const currentId = isPut ? form.action.split('/').pop() : null;

                const classBusy = existingSchedules.find(s => {
                    if(isPut && s.id == currentId) return false;
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
                    errorMsg.innerHTML = `<strong>BENTROK KELAS:</strong> Kelas ini sedang diisi oleh <u>${teacherName}</u>.`;
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

            teacherSelect.addEventListener('change', () => document.getElementById('error-alert').classList.add('d-none'));
            classroomSelect.addEventListener('change', () => document.getElementById('error-alert').classList.add('d-none'));
        });
    </script>

    <style>
        .fc-event-title { font-size: 0.8em; white-space: pre-wrap; }
        .fc-timegrid-slot-label-cushion { white-space: normal !important; width: 60px; }
        .fc-timegrid-axis-cushion { max-width: 60px; }
    </style>

</x-app-layout>