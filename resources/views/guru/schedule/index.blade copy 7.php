@section('title', 'Jadwal Mengajar')

<x-app-layout>
    <div class="page-content">
        <div class="container py-4">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-calendar-alt me-2"></i> Jadwal Mengajar Saya</h4>
                    <p class="text-muted small mb-0">Kelola jadwal pelajaran dan pantau absensi harian.</p>
                </div>
                <a href="{{ route('schedule.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Buat Jadwal Baru
                </a>
            </div>

            <!-- Alert Success -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- TAB NAVIGASI -->
            <ul class="nav nav-tabs mb-4" id="scheduleTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-view" type="button" role="tab">
                        <i class="fas fa-list me-1"></i> Tampilan List
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button" role="tab">
                        <i class="fas fa-calendar-week me-1"></i> Tampilan Kalender
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="scheduleTabsContent">
                
                <!-- 1. LIST VIEW (KARTU) -->
                <div class="tab-pane fade show active" id="list-view" role="tabpanel">
                    <div class="row">
                        @forelse($schedules as $schedule)
                            @php
                                $now = \Carbon\Carbon::now();
                                $isToday = $schedule->day == $now->translatedFormat('l');
                                
                                $startTime = \Carbon\Carbon::parse($schedule->start_time)->setDate($now->year, $now->month, $now->day);
                                $endTime = \Carbon\Carbon::parse($schedule->end_time)->setDate($now->year, $now->month, $now->day);
                                
                                $isActive = $isToday && $now->between($startTime, $endTime);

                                if ($isActive) {
                                    $cardBorder = 'border-warning';
                                    $badgeBg = 'bg-warning text-dark';
                                    $activeClass = 'card-active'; 
                                } elseif ($isToday) {
                                    $cardBorder = 'border-success';
                                    $badgeBg = 'bg-success';
                                    $activeClass = '';
                                } else {
                                    $cardBorder = 'border-primary';
                                    $badgeBg = 'bg-primary';
                                    $activeClass = '';
                                }
                            @endphp

                            <div class="col-md-6 col-lg-4 mb-4 card-column position-relative">
                                <div class="card shadow-sm h-100 border-start border-4 {{ $cardBorder }} {{ $activeClass }} hover-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge {{ $badgeBg }} rounded-pill px-3">
                                                {{ $schedule->day }}
                                                @if($isActive) 
                                                    <i class="fas fa-circle ms-1 text-danger small animate-pulse"></i> LIVE 
                                                @endif
                                            </span>
                                            <span class="fw-bold text-dark font-monospace">
                                                {{ $startTime->format('H:i') }} - {{ $endTime->format('H:i') }}
                                            </span>
                                        </div>
                                        
                                        <h5 class="card-title fw-bold text-dark mb-1 text-truncate" title="{{ $schedule->subject->name ?? 'Mapel Dihapus' }}">
                                            {{ $schedule->subject->name ?? 'Mapel Dihapus' }}
                                        </h5>
                                        <p class="card-text text-muted mb-3">
                                            <i class="fas fa-door-open me-1"></i> {{ $schedule->classroom->name ?? 'Kelas Dihapus' }}
                                        </p>

                                        <hr class="my-2 border-light">

                                        <div class="d-flex justify-content-between align-items-end mt-3">
                                            <div class="small text-muted mb-1" title="Jumlah siswa yang sudah absen hari ini">
                                                <i class="fas fa-user-check text-success"></i> Hadir: 
                                                <span class="fw-bold text-dark">{{ $schedule->attendances_count ?? 0 }}</span>
                                            </div>
                                            
                                            <div class="d-flex gap-1">
                                                <!-- MENU ABSENSI -->
                                                <div class="btn-group dropdown-container">
                                                    <button type="button" class="btn btn-sm {{ $isActive ? 'btn-warning text-dark fw-bold' : 'btn-success' }} dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport">
                                                        <i class="fas fa-camera me-1"></i> Absen
                                                    </button>
                                                    <ul class="dropdown-menu shadow">
                                                                                                                <li><h6 class="dropdown-header">Metode Absensi</h6></li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ url('/schedule/manual', ['schedule_id' => $schedule->id]) }}">
                                                                <i class="fas fa-clipboard-list me-2 text-secondary"></i> Input Manual
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('scan.index', ['schedule_id' => $schedule->id]) }}">
                                                                <i class="fas fa-qrcode me-2 text-dark"></i> Scan QR Code
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('scan.face', ['schedule_id' => $schedule->id]) }}">
                                                                <i class="fas fa-user-circle me-2 text-primary"></i> Face ID
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="btn-group">
                                                    <!-- TOMBOL EDIT -->
                                                    <a href="{{ route('schedule.edit', $schedule->id) }}" class="btn btn-sm btn-outline-warning" title="Edit Jadwal">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <a href="{{ route('schedule.show', $schedule->id) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form action="{{ route('schedule.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jadwal ini? Data absensi terkait mungkin ikut terhapus.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(this.form)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-light border text-center py-5">
                                    <img src="https://img.icons8.com/ios/100/cccccc/calendar.png" width="60" class="mb-3 opacity-50">
                                    <h5 class="text-muted fw-bold">Belum Ada Jadwal</h5>
                                    <p class="text-muted mb-3">Silakan buat jadwal untuk mulai mengabsen.</p>
                                    <a href="{{ route('schedule.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Buat Jadwal Sekarang
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 2. CALENDAR VIEW -->
                <div class="tab-pane fade" id="calendar-view" role="tabpanel">
                    <div class="card shadow border-0">
                        <div class="card-body p-0">
                            <div class="alert alert-info m-3 mb-0 small">
                                <i class="fas fa-info-circle me-1"></i> <strong>Tips:</strong> Klik pada area kosong di kalender untuk menambahkan jadwal baru di jam tersebut. Klik jadwal untuk mengedit.
                            </div>
                            <!-- Tempat Kalender Dirender -->
                            <div id="calendar" class="p-3"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- PREPARE DATA UNTUK FULLCALENDAR -->
    @php
        $events = [];
        $dayMap = [
            'minggu' => 0, 'senin' => 1, 'selasa' => 2, 'rabu' => 3, 
            'kamis' => 4, 'jumat' => 5, 'sabtu' => 6
        ];

        foreach($schedules as $s) {
            $dayKey = strtolower(trim($s->day));
            if (!isset($dayMap[$dayKey])) continue;

            $subjectName = $s->subject->name ?? 'Mapel';
            $classroomName = $s->classroom->name ?? 'Kelas';

            $events[] = [
                'id' => $s->id,
                // PERBAIKAN: Masukkan nama kelas ke dalam title sebagai fallback
                'title' => $subjectName . " (" . $classroomName . ")",
                'startTime' => \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                'endTime' => \Carbon\Carbon::parse($s->end_time)->format('H:i'),
                'daysOfWeek' => [$dayMap[$dayKey]], 
                'color' => '#4e73df', 
                'url' => route('schedule.edit', $s->id),
                'allDay' => false,
                // Data untuk custom render
                'extendedProps' => [
                    'subject' => $subjectName,
                    'classroom' => $classroomName
                ]
            ];
        }
    @endphp

    <!-- STYLES -->
    <style>
        .hover-card { 
            transition: box-shadow 0.2s, border-color 0.2s; 
        }
        
        .hover-card:hover { 
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15)!important; 
            z-index: 10; 
            position: relative;
        }

        .card-active {
            box-shadow: 0 0.25rem 0.75rem rgba(255, 193, 7, 0.4) !important;
            background-color: #fff;
            border-width: 0 0 0 5px !important;
        }

        .z-index-high {
            z-index: 1050 !important; 
        }
        
        @keyframes pulse-red {
            0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; }
        }
        .animate-pulse { animation: pulse-red 1.5s infinite; }

        /* FullCalendar Customization */
        #calendar { min-height: 600px; font-family: inherit; }
        .fc-event { cursor: pointer; border: none; padding: 2px 4px; border-radius: 4px; transition: transform 0.1s; }
        .fc-event:hover { transform: scale(1.02); }
        .fc-toolbar-title { font-size: 1.25rem !important; font-weight: bold; color: #4e73df; }
        .fc-col-header-cell { background-color: #f8f9fa; padding: 10px 0 !important; }
        
        /* Style Konten Event di Kalender */
        .fc-custom-content {
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }
    </style>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

    <script>
        function confirmDelete(form) {
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
                    form.submit();
                }
            })
        }

        document.addEventListener('show.bs.dropdown', function (e) {
            const dropdown = e.target;
            const cardColumn = dropdown.closest('.card-column');
            if (cardColumn) {
                cardColumn.classList.add('z-index-high');
            }
        });

        document.addEventListener('hide.bs.dropdown', function (e) {
            const dropdown = e.target;
            const cardColumn = dropdown.closest('.card-column');
            if (cardColumn) {
                cardColumn.classList.remove('z-index-high');
            }
        });

        // --- FULLCALENDAR INIT ---
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek', 
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay'
                },
                locale: 'id', 
                slotMinTime: '06:00:00', 
                slotMaxTime: '17:00:00', 
                allDaySlot: false,
                contentHeight: 'auto',
                selectable: true, 
                events: @json($events),

                // PERBAIKAN: Custom Event Content untuk Menampilkan Mapel & Kelas
                eventContent: function(arg) {
                    // Ambil data dari extendedProps jika ada, jika tidak fallback ke title
                    let props = arg.event.extendedProps || {};
                    let subject = props.subject || arg.event.title;
                    let classroom = props.classroom || '';
                    let timeText = arg.timeText;

                    let content = document.createElement('div');
                    content.className = 'fc-custom-content';
                    content.innerHTML = `
                        <div class="fc-event-time" style="font-size:0.75rem; opacity:0.9;">${timeText}</div>
                        <div class="fc-event-title" style="font-weight:bold; font-size:0.85rem; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${subject}</div>
                        <div style="font-size:0.75rem; display:flex; align-items:center; gap:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            ${classroom ? '<i class="fas fa-door-open"></i> ' + classroom : ''}
                        </div>
                    `;
                    return { domNodes: [content] };
                },

                // EVENT: Klik Jadwal
                eventClick: function(info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault(); 
                        window.location.href = info.event.url;
                    }
                },

                // EVENT: Klik Slot Kosong
                dateClick: function(info) {
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const date = info.date;
                    const dayName = days[date.getDay()];
                    
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    const time = `${hours}:${minutes}`;

                    const url = `{{ route('schedule.create') }}?day=${dayName}&start_time=${time}`;
                    window.location.href = url;
                }
            });

            var calendarTab = document.getElementById('calendar-tab');
            if (calendarTab) {
                calendarTab.addEventListener('shown.bs.tab', function (e) {
                    calendar.render();
                });
                calendarTab.addEventListener('click', function (e) {
                    setTimeout(() => { calendar.render(); }, 200);
                });
            }
        });
    </script>
</x-app-layout>