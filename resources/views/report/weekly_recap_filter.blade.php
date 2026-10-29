@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4 py-3">
                    <h5 class="m-0 fw-bold">
                        <i class="fas fa-filter me-2"></i>Filter Cetak Rekap Presensi Mingguan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('attendances.weekly-recap') }}" method="GET" target="_blank">
                        
                        <!-- 1. PILIH KELAS -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Kelas <span class="text-danger">*</span></label>
                            <select name="classroom_id" id="classroom_id" class="form-select rounded-3" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classrooms as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. PILIH MINGGU / TANGGAL -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Tanggal (Pekan) <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Sistem akan otomatis merekap dari hari Senin s/d Jumat pada pekan tanggal ini.</small>
                        </div>

                        <!-- 3. PILIH SISWA (OPTIONAL) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Siswa Spesifik <span class="text-muted">(Opsional)</span></label>
                            <select name="student_id" id="student_id" class="form-select rounded-3" disabled>
                                <option value="">-- Semua Siswa di Kelas --</option>
                            </select>
                        </div>

                        <!-- TOMBOL SUBMIT -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold">
                                <i class="fas fa-file-pdf me-2"></i> Generate & Cetak Laporan PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
<script>
$(document).ready(function() {
    $('#classroom_id').change(function() {
        var classroomId = $(this).val();
        var studentSelect = $('#student_id');

        studentSelect.empty().append('<option value="">-- Semua Siswa di Kelas --</option>');

        if (classroomId) {
            studentSelect.prop('disabled', false);
            $.ajax({
                url: "{{ url('attendances/get-students') }}/" + classroomId,
                type: 'GET',
                success: function(data) {
                    $.each(data, function(key, student) {
                        studentSelect.append('<option value="' + student.id + '">' + student.name + ' (NIS: ' + (student.nis ?? '-') + ')</option>');
                    });
                }
            });
        } else {
            studentSelect.prop('disabled', true);
        }
    });
});
</script>
@endpush