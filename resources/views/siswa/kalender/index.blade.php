@section('title', 'Dashboard Siswa')

<x-app-layout>
<div class="container-fluid py-3">
    <!-- Header Page -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calendar-week-fill text-primary me-2"></i>Kalender Presensi Saya</h4>
            <p class="text-muted small mb-0">Rekapitulasi kehadiran gerbang, mata pelajaran, dan ibadah harian.</p>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3 p-md-4">
            <div id="studentAttendanceCalendar"></div>
        </div>
    </div>
</div>

<!-- Modal Detail Absensi -->
<div class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h6 class="modal-title fw-bold" id="detailModalTitle"><i class="bi bi-info-circle me-2"></i>Detail Presensi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="detailModalBody">
                <!-- Konten dinamis dari JS -->
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar Script & Init -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('studentAttendanceCalendar');
    
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
        events: "{{ route('student.attendance-calendar.events') }}",
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
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
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
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="d-block">${p.prayer_name}</strong>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>${p.check_in_time}</small>
                            </div>
                            <span class="badge bg-info text-dark">${p.status}</span>
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
});
</script>
</x-app-layout>