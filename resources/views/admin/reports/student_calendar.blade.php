@extends('layouts.app')
<!-- Select2 CSS & Theme Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />


@section('content')
<div class="container-fluid py-4">
    <!-- Title Page -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calendar3-range-fill text-primary me-2"></i>Kalender Presensi Siswa</h4>
            <p class="text-muted small mb-0">Lihat rekapitulasi kehadiran harian (Gerbang, Mapel, dan Sholat) per siswa.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-secondary small">1. Pilih Kelas</label>
                    <select id="classroomFilter" class="form-select rounded-3 select2">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-secondary small">2. Pilih Siswa</label>
                    <select id="studentFilter" class="form-select rounded-3 select2" disabled>
                        <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="btnReset" class="btn btn-light border rounded-3 w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3 p-md-4">
            <div id="adminAttendanceCalendar"></div>
        </div>
    </div>
</div>

<!-- Modal Detail Absensi -->
<!-- Modal Detail Absensi -->
<!-- Modal Detail Absensi -->
<div class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            
            <!-- Modal Header dengan Tombol WA & Gambar -->
            <div class="modal-header bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold" id="detailModalTitle">
                    <i class="bi bi-info-circle me-2"></i>Detail Presensi
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <!-- Tombol Kirim WA -->
                    <button type="button" class="btn btn-sm btn-success fw-bold rounded-3" id="btnSendWa">
                        <i class="bi bi-whatsapp me-1"></i> Kirim WA
                    </button>
                    <!-- Tombol Simpan Gambar -->
                    <button type="button" class="btn btn-sm btn-light text-primary fw-bold rounded-3" id="btnDownloadModal">
                        <i class="bi bi-download me-1"></i> Gambar
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body p-4" id="detailModalBody">
                <!-- Konten dinamis JS -->
            </div>
        </div>
    </div>
</div>
<!-- jQuery, Select2 JS, FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<!-- FullCalendar Script & Init -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('adminAttendanceCalendar');
        var classroomFilter = document.getElementById('classroomFilter');
        var studentFilter = document.getElementById('studentFilter');
        var btnReset = document.getElementById('btnReset');
        var selectedStudentId = '';

        // Inisialisasi FullCalendar
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                list: 'Daftar'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                var baseUrl = "{{ route('admin.reports.student-calendar.events') }}";
                var classroomId = $('#classroomFilter').val(); // Ambil ID Kelas
                var params = new URLSearchParams({
                    start: fetchInfo.startStr.split('T')[0],
                    end: fetchInfo.endStr.split('T')[0]
                });

                if (classroomId) {
                    params.append('classroom_id', classroomId);
                }
    

                if (selectedStudentId) {
                    params.append('student_id', selectedStudentId);
                }

                fetch(`${baseUrl}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => successCallback(data))
                    .catch(error => failureCallback(error));
            },
            eventClick: function(info) {
                var props = info.event.extendedProps;
                var modalBody = document.getElementById('detailModalBody');
                var modalTitle = document.getElementById('detailModalTitle');
                
                var html = `<div class="mb-3 text-muted small"><strong>Tanggal:</strong> ${info.event.startStr}</div>`;
                
                if (props.category === 'holiday') {
                    modalTitle.innerHTML = '<i class="bi bi-calendar-x text-danger me-2"></i>Hari Libur Sekolah';
                    html += `
                        <div class="alert alert-danger mb-0 rounded-3">
                            <h6 class="fw-bold mb-1">${props.title}</h6>
                            <p class="small mb-0">${props.description}</p>
                        </div>`;
                } else if (props.category === 'gate') {
                    modalTitle.innerHTML = '<i class="bi bi-door-open text-primary me-2"></i>Presensi Gerbang';
                    html += `
                        <ul class="list-group list-group-flush rounded-3 border">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Status <span class="badge bg-primary fs-6">${props.status}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Jam Masuk <span class="fw-bold">${props.arrival_time}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Jam Pulang <span class="fw-bold">${props.departure_time}</span>
                            </li>
                            <li class="list-group-item">
                                Catatan: <br><small class="text-muted">${props.notes}</small>
                            </li>
                        </ul>`;
                } else if (props.category === 'subject') {
                    modalTitle.innerHTML = '<i class="bi bi-book text-purple me-2"></i>Presensi Mata Pelajaran';
                    html += `<div class="list-group rounded-3">`;
                    props.details.forEach(function(s) {
                        // Tampilkan nama siswa jika ada (saat filter rekap kelas)
                        var studentText = s.student_name ? `<small class="text-primary fw-semibold d-block mb-1">${s.student_name}</small>` : '';

                        html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    ${studentText}
                                    <strong class="d-block">${s.subject_name}</strong>
                                    <small class="text-muted"><i class="bi bi-clock me-1"></i>${s.check_in_time}</small>
                                </div>
                                <span class="badge bg-success">${s.status}</span>
                            </div>`;
                    });
                    html += `</div>`;
                } else if (props.category === 'prayer') {
                    modalTitle.innerHTML = '<i class="bi bi-moon-stars text-info me-2"></i>Presensi Sholat';
                    html += `<div class="list-group rounded-3">`;
                    props.details.forEach(function(p) {
                        // Tampilkan nama siswa jika ada (saat filter rekap kelas)
                        var studentText = p.student_name ? `<small class="text-primary fw-semibold d-block mb-1">${p.student_name}</small>` : '';

                        html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    ${studentText}
                                    <strong class="d-block text-capitalize">${p.prayer_name}</strong>
                                    <small class="text-muted"><i class="bi bi-clock me-1"></i>${p.check_in_time}</small>
                                </div>
                                <span class="badge bg-info text-dark">${p.status}</span>
                            </div>`;
                    });
                    html += `</div>`;
                } else if (props.category === 'class_summary') {
                    modalTitle.innerHTML = '<i class="bi bi-people text-primary me-2"></i>Rekap Presensi Kelas';
                    html += `<div class="list-group rounded-3">`;
                    props.details.forEach(function(d) {
                        html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="d-block">${d.student_name}</strong>
                                    <small class="text-muted"><i class="bi bi-clock me-1"></i>${d.time}</small>
                                </div>
                                <span class="badge bg-primary">${d.status}</span>
                            </div>`;
                    });
                    html += `</div>`;
                } else if (props.category === 'class_summary') {
    modalTitle.innerHTML = '<i class="bi bi-people text-primary me-2"></i>Rekap Presensi Kelas';
    html += `<div class="list-group rounded-3">`;
    props.details.forEach(function(d) {
        var badgeClass = 'bg-success';
        
        if (d.status === 'TERLAMBAT') {
            badgeClass = 'bg-warning text-dark';
        } else if (d.status === 'IZIN' || d.status === 'SAKIT') {
            badgeClass = 'bg-info text-dark';
        } else if (d.status === 'TANPA KETERANGAN' || d.status === 'ALFA') {
            badgeClass = 'bg-danger';
        }

        html += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong class="d-block">${d.student_name}</strong>
                    <small class="text-muted"><i class="bi bi-clock me-1"></i>${d.time}</small>
                </div>
                <span class="badge ${badgeClass}">${d.status}</span>
            </div>`;
    });
    html += `</div>`;
}


                modalBody.innerHTML = html;
                var modal = new bootstrap.Modal(document.getElementById('attendanceDetailModal'));
                modal.show();
            },
            windowResize: function() {
                if (window.innerWidth < 768) {
                    calendar.changeView('listMonth');
                } else {
                    calendar.changeView('dayGridMonth');
                }
            }
        });

        calendar.render();

        // Gunakan Event Delegation agar tombol tetap merespon meskipun modal dibuka-tutup berkali-kali
$(document).on('click', '#btnSendWa', function() {
    if (!currentEventData) {
        alert('Data presensi tidak ditemukan.');
        return;
    }

    var text = "*REKAP PRESENSI SISWA*\n";
    text += `Tanggal: ${currentEventData.date}\n`;
    text += "-----------------------------------\n";

    if (currentEventData.category === 'class_summary') {
        text += "👥 *REKAP PRESENSI KELAS*\n";
        currentEventData.details.forEach(function(d, index) {
            text += `${index + 1}. *${d.student_name}* : ${d.status} (${d.time})\n`;
        });
    } else if (currentEventData.category === 'gate') {
        text += "🚪 *PRESENSI GERBANG*\n";
        text += `Status: *${currentEventData.status}*\n`;
        text += `Jam Masuk: ${currentEventData.arrival_time}\n`;
        text += `Jam Pulang: ${currentEventData.departure_time}\n`;
        if (currentEventData.notes) text += `Catatan: ${currentEventData.notes}\n`;
    } else if (currentEventData.category === 'subject') {
        text += "📚 *PRESENSI MATA PELAJARAN*\n";
        currentEventData.details.forEach(function(s, index) {
            var nameText = s.student_name ? ` (${s.student_name})` : '';
            text += `${index + 1}. *${s.subject_name}*${nameText} - ${s.status} (${s.check_in_time})\n`;
        });
    } else if (currentEventData.category === 'prayer') {
        text += "🕌 *PRESENSI SHOLAT*\n";
        currentEventData.details.forEach(function(p, index) {
            var nameText = p.student_name ? ` (${p.student_name})` : '';
            text += `${index + 1}. *${p.prayer_name.toUpperCase()}*${nameText} - ${p.status} (${p.check_in_time})\n`;
        });
    } else if (currentEventData.category === 'holiday') {
        text += "🔴 *HARI LIBUR SEKOLAH*\n";
        text += `*${currentEventData.title}*\n`;
        text += `${currentEventData.description}\n`;
    }

    text += "-----------------------------------\n";
    text += "_Sistem Informasi SMK Negeri 1 Bukittinggi_";

    // Format URL WhatsApp
    var waUrl = "https://api.whatsapp.com/send?text=" + encodeURIComponent(text);
    
    // Buka tab baru untuk WhatsApp
    window.open(waUrl, '_blank');
});

        // Event Handler saat Kelas Dipilih
        classroomFilter.addEventListener('change', function() {
            var classId = this.value;
            studentFilter.innerHTML = '<option value="">-- Memuat Siswa... --</option>';
            studentFilter.disabled = true;
            selectedStudentId = '';
            calendar.refetchEvents();

            if (classId) {
                // Gunakan route Laravel dengan meremut placeholder :classId
                var routePattern = "{{ route('admin.reports.student-calendar.students', ':classId') }}";
                var url = routePattern.replace(':classId', encodeURIComponent(classId));
                
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(students => {
                    studentFilter.innerHTML = '<option value="">-- Pilih Siswa --</option>';
                    if (students.length === 0) {
                        studentFilter.innerHTML = '<option value="">-- Tidak Ada Siswa Aktif --</option>';
                        return;
                    }
                    students.forEach(function(student) {
                        var option = document.createElement('option');
                        option.value = student.id;
                        option.textContent = `${student.name} (${student.nis || '-'})`;
                        studentFilter.appendChild(option);
                    });
                    studentFilter.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching students:', error);
                    studentFilter.innerHTML = '<option value="">-- Gagal Memuat Siswa --</option>';
                });
            } else {
                studentFilter.innerHTML = '<option value="">-- Pilih Kelas Terlebih Dahulu --</option>';
            }
        });

        // Event Handler saat Siswa Dipilih
        studentFilter.addEventListener('change', function() {
            selectedStudentId = this.value;
            calendar.refetchEvents();
        });

        // Event Handler Tombol Reset
        btnReset.addEventListener('click', function() {
            classroomFilter.value = '';
            studentFilter.innerHTML = '<option value="">-- Pilih Kelas Terlebih Dahulu --</option>';
            studentFilter.disabled = true;
            selectedStudentId = '';
            calendar.refetchEvents();
        });
    });
    $('#btnDownloadModal').on('click', function() {
        var modalBody = document.getElementById('detailModalBody');

        html2canvas(modalBody).then(function(canvas) {
            var link = document.createElement('a');
            link.download = 'Rekap-Presensi-' + new Date().toISOString().slice(0,10) + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    });
</script>
@endsection