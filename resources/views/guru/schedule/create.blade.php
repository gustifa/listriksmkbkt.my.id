@section('title', 'Buat Jadwal Baru')

<x-app-layout>
    <div class="page-content">
        <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
            <div class="breadcrumb-title pe-3">Jadwal Mengajar</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="p-0 mb-0 breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('schedule.index') }}"><i class="fas fa-calendar-alt me-2"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Buat Jadwal Baru</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="border-0 shadow card">
                        <div class="text-white card-header bg-primary fw-bold">
                            <i class="fas fa-plus-circle me-1"></i> Form Tambah Jadwal Mengajar
                        </div>
                        <div class="card-body">

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

                            <form action="{{ route('schedule.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Hari</label>
                                    <select name="day" id="day" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Hari --</option>
                                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $d)
                                            <option value="{{ $d }}" {{ old('day', request('day')) == $d ? 'selected' : '' }}>
                                                {{ $d }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Kelas</label>
                                    <select name="classroom_id" id="classroom_id" class="form-select" required onchange="filterSubjects(this.value)">
                                        <option value="" disabled selected>-- Pilih Kelas --</option>
                                        @foreach($classrooms as $c)
                                            <option value="{{ $c->id }}" {{ old('classroom_id') == $c->id ? 'selected' : '' }}>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mata Pelajaran</label>
                                    <select name="subject_id" id="subject_id" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Kelas Terlebih Dahulu --</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ruangan / Bengkel (Opsional)</label>
                                    <select name="room_id" id="room_id" class="form-select">
                                        <option value="" selected>-- Tidak Ada Ruangan Khusus --</option>
                                        @foreach($rooms as $r)
                                            <option value="{{ $r->id }}" {{ old('room_id') == $r->id ? 'selected' : '' }}>
                                                {{ $r->name }} ({{ $r->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- DROPDOWN TIME SLOT JAM MULAI & JAM SELESAI -->
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-bold">Jam Mulai</label>
                                        <select name="start_time" id="start_time" class="form-select" required onchange="handleStartSlotChange(this.value)">
                                            <option value="" disabled selected>-- Pilih Jam Mulai --</option>
                                            @if(isset($timeSlots))
                                                @foreach($timeSlots as $slot)
                                                    @if($slot->type === 'lesson')
                                                        @php $sTime = \Carbon\Carbon::parse($slot->start_time)->format('H:i'); @endphp
                                                        <option value="{{ $sTime }}" 
                                                                data-end="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}"
                                                                data-label="{{ $slot->label }}"
                                                                {{ old('start_time', request('start_time')) == $sTime ? 'selected' : '' }}>
                                                            {{ $slot->label }} ({{ $sTime }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label fw-bold">Jam Selesai</label>
                                        <select name="end_time" id="end_time" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih Jam Selesai --</option>
                                            @if(isset($timeSlots))
                                                @foreach($timeSlots as $slot)
                                                    @php $eTime = \Carbon\Carbon::parse($slot->end_time)->format('H:i'); @endphp
                                                    <option value="{{ $eTime }}"
                                                            data-label="{{ $slot->label }}"
                                                            {{ old('end_time') == $eTime ? 'selected' : '' }}>
                                                        Selesai {{ $slot->label }} ({{ $eTime }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="gap-2 d-flex justify-content-end">
                                    <a href="{{ route('schedule.index') }}" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const assignments = @json($assignments);

        function filterSubjects(classId, selectedSubjectId = null) {
            const subjectSelect = document.getElementById('subject_id');
            subjectSelect.innerHTML = '<option value="" disabled selected>-- Pilih Mata Pelajaran --</option>';

            const filteredMapels = assignments.filter(a => a.classroom_id == classId);
            filteredMapels.forEach(item => {
                if(item.subject) {
                    const opt = document.createElement('option');
                    opt.value = item.subject_id;
                    opt.textContent = item.subject.name;
                    if(selectedSubjectId && item.subject_id == selectedSubjectId) opt.selected = true;
                    subjectSelect.appendChild(opt);
                }
            });
        }

        function handleStartSlotChange(startTime) {
            const startSelect = document.getElementById('start_time');
            const selectedOption = startSelect.options[startSelect.selectedIndex];

            if (selectedOption && selectedOption.dataset.end) {
                const targetEnd = selectedOption.dataset.end;
                const endSelect = document.getElementById('end_time');

                for (let i = 0; i < endSelect.options.length; i++) {
                    if (endSelect.options[i].value === targetEnd) {
                        endSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const classSelect = document.getElementById('classroom_id');
            if(classSelect.value) {
                filterSubjects(classSelect.value, "{{ old('subject_id') }}");
            }

            const startSelect = document.getElementById('start_time');
            if(startSelect.value) {
                handleStartSlotChange(startSelect.value);
            }
        });
    </script>
</x-app-layout>