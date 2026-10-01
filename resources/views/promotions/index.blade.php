@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">Menu Kenaikan Kelas & Kelulusan</h3>
        <form action="{{ route('promotions.reset') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin me-reset status kenaikan kelas untuk seluruh siswa aktif?')">
            @csrf
            <button type="submit" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-arrow-counterclockwise"></i> Reset Status Tahun Ajaran Baru
            </button>
        </form>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Kelas --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('promotions.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="classroom_id" class="form-label fw-bold">Pilih Kelas yang Akan Diproses:</label>
                    <select name="classroom_id" id="classroom_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms as $room)
                            <option value="{{ $room->id }}" {{ $selectedClassroomId == $room->id ? 'selected' : '' }}>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($currentClassroom)
    <form action="{{ route('promotions.process') }}" method="POST">
        @csrf
        <input type="hidden" name="source_classroom_id" value="{{ $currentClassroom->id }}">

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Siswa Kelas: {{ $currentClassroom->name }}</h5>
                <span class="badge bg-light text-primary fs-6">Siswa Ditemukan: {{ $students->count() }}</span>
            </div>

            <div class="card-body">
                {{-- Panel Aksi Massal --}}
                <div class="p-3 mb-4 bg-light border rounded">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Aksi Serentak (Pilih Semua):</label>
                            <select id="bulk_action" class="form-select">
                                <option value="">-- Pilih Aksi Massal --</option>
                                <option value="promote">Naik Kelas (Ke Kelas Tujuan)</option>
                                <option value="graduate">Luluskan Semua</option>
                                <option value="stay">Tinggal Kelas Semua</option>
                            </select>
                        </div>

                        <div class="col-md-5 d-none" id="bulk_target_container">
                            <label class="form-label fw-bold">Kelas Tujuan Massal:</label>
                            <select id="bulk_target_classroom" class="form-select">
                                <option value="">-- Pilih Kelas Tujuan --</option>
                                @foreach($classrooms as $room)
                                    @if($room->id != $currentClassroom->id)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 align-self-end">
                            <button type="button" class="btn btn-secondary w-100" id="btn_apply_bulk">Terapkan Massal</button>
                        </div>
                    </div>
                </div>

                {{-- Field Tambahan saat ada Lulus --}}
                <div class="row g-3 mb-4 p-3 border rounded bg-warning bg-opacity-10 d-none" id="graduation_fields">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tahun Kelulusan</label>
                        <input type="text" name="graduation_year" class="form-control" placeholder="Contoh: 2025/2026" value="{{ date('Y').'/'.(date('Y')+1) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Kelulusan</label>
                        <input type="date" name="graduation_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                {{-- Tabel Siswa --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">NIS/NISN</th>
                                <th width="30%">Nama Siswa</th>
                                <th width="20%">Status Kenaikan</th>
                                <th width="30%">Kelas Tujuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->nisn ?? $student->nis ?? '-' }}</td>
                                <td><strong>{{ $student->name }}</strong></td>
                                <td>
                                    <select name="students[{{ $student->id }}][action]" class="form-select action-select" data-student-id="{{ $student->id }}">
                                        <option value="promote" selected>Naik Kelas</option>
                                        <option value="graduate">Lulus</option>
                                        <option value="stay">Tinggal Kelas</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="students[{{ $student->id }}][target_classroom_id]" class="form-select target-select" id="target_{{ $student->id }}">
                                        <option value="">-- Pilih Kelas Tujuan --</option>
                                        @foreach($classrooms as $room)
                                            @if($room->id != $currentClassroom->id)
                                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <em>Tidak ada siswa aktif yang siap diproses di kelas ini (Semua siswa sudah diproses atau kelas kosong).</em>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($students->isNotEmpty())
            <div class="card-footer text-end py-3">
                <button type="submit" class="btn btn-success btn-lg px-4" onclick="return confirm('Apakah Anda yakin data kenaikan kelas & kelulusan ini sudah benar?')">
                    <i class="bi bi-check-circle"></i> Simpan & Proses Kenaikan
                </button>
            </div>
            @endif
        </div>
    </form>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulkAction = document.getElementById('bulk_action');
    const bulkTargetContainer = document.getElementById('bulk_target_container');
    const bulkTargetClassroom = document.getElementById('bulk_target_classroom');
    const btnApplyBulk = document.getElementById('btn_apply_bulk');
    const graduationFields = document.getElementById('graduation_fields');

    if (bulkAction) {
        bulkAction.addEventListener('change', function () {
            if (this.value === 'promote') {
                bulkTargetContainer.classList.remove('d-none');
            } else {
                bulkTargetContainer.classList.add('d-none');
            }
        });
    }

    if (btnApplyBulk) {
        btnApplyBulk.addEventListener('click', function () {
            const actionVal = bulkAction.value;
            const targetRoomVal = bulkTargetClassroom.value;

            if (!actionVal) {
                alert('Silakan pilih aksi massal terlebih dahulu!');
                return;
            }

            if (actionVal === 'promote' && !targetRoomVal) {
                alert('Silakan pilih kelas tujuan massal!');
                return;
            }

            document.querySelectorAll('.action-select').forEach(select => {
                select.value = actionVal;
                select.dispatchEvent(new Event('change'));
            });

            if (actionVal === 'promote') {
                document.querySelectorAll('.target-select').forEach(select => {
                    select.value = targetRoomVal;
                });
            }
        });
    }

    document.querySelectorAll('.action-select').forEach(select => {
        select.addEventListener('change', function () {
            const studentId = this.getAttribute('data-student-id');
            const targetSelect = document.getElementById('target_' + studentId);

            if (this.value === 'graduate' || this.value === 'stay') {
                targetSelect.value = '';
                targetSelect.disabled = true;
            } else {
                targetSelect.disabled = false;
            }

            checkGraduationFieldsVisibility();
        });
    });

    function checkGraduationFieldsVisibility() {
        let hasGraduation = false;
        document.querySelectorAll('.action-select').forEach(select => {
            if (select.value === 'graduate') {
                hasGraduation = true;
            }
        });

        if (graduationFields) {
            if (hasGraduation) {
                graduationFields.classList.remove('d-none');
            } else {
                graduationFields.classList.add('d-none');
            }
        }
    }
});
</script>
@endsection