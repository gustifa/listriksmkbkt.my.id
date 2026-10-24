@php
$appLogo = \App\Models\Setting::value('app_logo');
$appName = \App\Models\Setting::value('app_name', 'E-Absensi');
$id = Auth::user()->id;
$guruId = App\Models\User::find($id);
$status = $guruId->status ?? '0';
@endphp

<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            @if($appLogo && file_exists(storage_path('app/public/'.$appLogo)))
                <img src="{{ asset('storage/'.$appLogo) }}"
                    alt="Logo"
                    width="40"
                    height="40"
                    class="align-text-top rounded d-inline-block me-2"
                    style="object-fit: cover;">
            @else
                <img src="{{ asset('upload/no_image.jpg')}}"
                    alt="logo title"
                    width="40"
                    height="40"
                    class="align-text-top rounded d-inline-block me-2"
                    style="object-fit: cover;">
            @endif
        </div>

        <div>
            <h4 class="logo-text">{{ $appName ?? 'GATECH' }}</h4> 
        </div>

        <div class="toggle-icon ms-auto">
            <i class='bx bx-arrow-back'></i>
        </div>
    </div>

    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li>
            <a href="{{route('dashboard')}}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        @if ($status === '1')
            {{-- ========================================== --}}
            {{-- 1. MENU PEMBELAJARAN & LMS (GURU)           --}}
            {{-- ========================================== --}}
            @role('guru')
                @if(Auth::user()->teacher)
                    <li class="menu-label">Pembelajaran & LMS</li>
                    <li>
                        <a class="has-arrow" href="javascript:;">
                            <div class="parent-icon"><i class='bx bx-book-open'></i></div>
                            <div class="menu-title">LMS / E-Learning</div>
                        </a>
                        <ul>
                            <li><a href="{{ route('materials.index') }}"><i class='bx bx-radio-circle'></i>Materi Pembelajaran</a></li>
                            <li><a href="{{ route('assignments.index') }}"><i class='bx bx-radio-circle'></i>Tugas Siswa</a></li>
                        </ul>
                    </li>

                    <li>
                        <a class="has-arrow" href="javascript:;">
                            <div class="parent-icon"><i class='bx bx-bookmark-heart'></i></div>
                            <div class="menu-title">Schedule</div>
                        </a>
                        <ul>
                            <li><a href="{{route('schedule.index')}}"><i class='bx bx-radio-circle'></i>PBM</a></li>
                            <li><a href="{{route('journal.index')}}"><i class='bx bx-radio-circle'></i>Jurnal</a></li>
                        </ul>
                    </li>

                    @if(Auth::user()->teacher->isAdvisor())
                    <li>
                        <a class="has-arrow" href="javascript:;">
                            <div class="parent-icon"><i class='bx bx-briefcase-alt-2'></i></div>
                            <div class="menu-title">PKL</div>
                        </a>
                        <ul>
                            <li><a href="{{ route('teacher.internships.index') }}"><i class='bx bx-radio-circle'></i>Bimbingan</a></li>
                        </ul>
                    </li>
                    @endif

                    <li class="menu-label">Uji Kompetensi Keahlian</li>
                    <li>
                        <a class="has-arrow" href="javascript:;">
                            <div class="parent-icon"><i class='bx bx-award'></i></div>
                            <div class="menu-title">UKK</div>
                        </a>
                        <ul>
                            <li><a href="{{ route('ukk.index') }}"><i class='bx bx-radio-circle'></i>Penilaian & Plotting</a></li>
                        </ul>
                    </li>
                @endif
            @endrole

            {{-- ========================================== --}}
            {{-- 2. MENU KETUA PROGRAM KEAHLIAN & KEPALA BENGKEL --}}
            {{-- ========================================== --}}
            @hasanyrole('kaprog|kabeng')
                <li class="menu-label">Manajemen Kejuruan</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-sitemap'></i></div>
                        <div class="menu-title">Data Kejuruan</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('students.index') }}"><i class='bx bx-radio-circle'></i>Siswa Keahlian</a></li>
                        <li><a href="{{ route('classrooms.index') }}"><i class='bx bx-radio-circle'></i>Rombel / Kelas</a></li>
                    </ul>
                </li>

                @role('kabeng')
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-wrench'></i></div>
                        <div class="menu-title">Bengkel & Inventaris</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('admin.inventory.index') }}"><i class='bx bx-radio-circle'></i>Ketersediaan Alat</a></li>
                        <li><a href="{{ route('loans.index') }}"><i class='bx bx-radio-circle'></i>Peminjaman Alat</a></li>
                    </ul>
                </li>
                @endrole

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bxs-report"></i></div>
                        <div class="menu-title">Laporan Kejuruan</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('daily_attendance.report') }}"><i class='bx bx-border-all'></i>Presensi Gerbang</a></li>
                        <li><a href="{{ route('transkrip.index') }}"><i class='bx bx-border-all'></i>Transkrip Absensi</a></li>
                        <li><a href="{{ route('recap.index') }}"><i class='bx bx-id-card'></i>Rekapitulasi</a></li>
                    </ul>
                </li>
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 3. MENU MANAJEMEN SEKOLAH (KEPSEK & WAKA)  --}}
            {{-- ========================================== --}}
            @hasanyrole('kepsek|wakakur|wakasis|wakahumas')
                <li class="menu-label">Manajemen Sekolah</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-bar-chart-alt-2"></i></div>
                        <div class="menu-title">Monitoring & Laporan</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('daily_attendance.report') }}"><i class='bx bx-radio-circle'></i>Presensi Gerbang</a></li>
                        <li><a href="{{ route('report.index') }}"><i class='bx bx-radio-circle'></i>Pembelajaran</a></li>
                        <li><a href="{{ route('transkrip.index') }}"><i class='bx bx-radio-circle'></i>Transkrip Absensi</a></li>
                        <li><a href="{{ route('recap.index') }}"><i class='bx bx-radio-circle'></i>Rekapitulasi Sekolah</a></li>
                        <li><a href="{{ route('admin.prayer_reports.index') }}"><i class='bx bx-radio-circle'></i>Presensi Sholat</a></li>
                        <li><a href="{{ route('reports.missing_attendance') }}"><i class='bx bx-radio-circle'></i>Laporan Alfa</a></li>
                    </ul>
                </li>
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 4. MENU WALI KELAS & GURU BK              --}}
            {{-- ========================================== --}}
            @hasanyrole('wali_kelas|guru_bk')
                <li class="menu-label">Wali Kelas & BK</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-file-find"></i></div>
                        <div class="menu-title">Cetak Transkrip</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('transkrip.index') }}"><i class='bx bx-border-all'></i>Transkrip Absensi</a></li>
                    </ul>
                </li>
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 5. MENU SUPER ADMIN & ADMIN UTAMA          --}}
            {{-- ========================================== --}}
            @hasanyrole('super_admin|admin')
                <li class="menu-label">Sistem & Pengaturan</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-cog"></i></div>
                        <div class="menu-title">Setting</div>
                    </a>
                    <ul>
                        <li><a href="{{route('settings.academic-years.index')}}"><i class='bx bx-home-smile'></i>Tahun Pelajaran</a></li>
                        <li><a href="{{route('settings.index')}}"><i class='bx bx-home-smile'></i>Sekolah</a></li>
                        <li><a href="{{route('all.user')}}"><i class='bx bx-user'></i>User</a></li>
                        <li><a href="{{route('settings.attendance')}}"><i class='bx bx-barcode-reader'></i>Presensi</a></li>
                        <li><a href="{{route('time_slots.index')}}"><i class='bx bx-barcode-reader'></i>Jam PBM</a></li>
                        <li><a href="{{route('admin.holidays.index')}}"><i class='bx bx-radio-circle'></i>Libur</a></li>
                        <li><a href="{{route('programs.index')}}"><i class='bx bx-minus-front'></i>Program Keahlian</a></li>
                        <li><a href="{{route('majors.index')}}"><i class='bx bx-minus-front'></i>Jurusan</a></li>
                        <li><a href="{{route('subjects.index')}}"><i class='bx bx-minus-front'></i>Mapel</a></li>
                        <li><a href="{{route('rooms.index')}}"><i class='bx bx-radio-circle'></i>Bengkel</a></li>
                        <li><a href="{{route('permissions.index')}}"><i class='bx bx-radio-circle'></i>Permission</a></li>
                        <li><a href="{{route('roles.index')}}"><i class='bx bx-radio-circle'></i>Role</a></li>
                        <li><a href="{{route('admin.inventory.index')}}"><i class='bx bx-radio-circle'></i>Inventaris</a></li>
                        <li><a href="{{route('prayer-location.index')}}"><i class='bx bx-radio-circle'></i>Sholat</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-group"></i></div>
                        <div class="menu-title">Maping</div>
                    </a>
                    <ul>
                        <li><a href="{{route('teachers.index')}}"><i class='bx bx-radio-circle'></i>Guru</a></li>
                        <li><a href="{{route('classrooms.index')}}"><i class='bx bxs-group'></i>Rombel</a></li>
                        <li><a href="{{route('students.index')}}"><i class='bx bxs-user-check'></i>Murid</a></li>
                        <li><a href="{{url('/face/register')}}"><i class='bx bxs-file-find'></i>Scan Wajah</a></li>
                        <li><a href="{{url('/teaching-assignments')}}"><i class='bx bx-home-smile'></i>PBM</a></li>
                        <li><a href="{{url('/schedule/all')}}"><i class='bx bx-home-smile'></i>Jadwal</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-chip"></i></div>
                        <div class="menu-title">System</div>
                    </a>
                    <ul>
                        <li><a href="{{route('admin.sync.index')}}"><i class='bx bx-sync'></i>Sinkronisasi Data</a></li>
                        <li><a href="{{route('admin.attendance.manage.index')}}"><i class='bx bx-id-card'></i>Manage</a></li>
                        <li><a href="{{route('admin.activity_logs.index')}}"><i class='bx bx-id-card'></i>Log</a></li>
                        <li><a href="{{route('admin.device-settings.index')}}"><i class='bx bx-id-card'></i>Reset</a></li>
                        <li><a href="{{route('system.update.index')}}"><i class='bx bx-id-card'></i>Update</a></li>
                        <li><a href="{{route('database.index')}}"><i class='bx bx-id-card'></i>Database</a></li>
                        <li><a href="{{route('pm2.index')}}"><i class='bx bx-id-card'></i>Server</a></li>
                        <li><a href="{{route('whatsapp.index')}}"><i class='bx bx-id-card'></i>WA</a></li>
                        <li><a href="{{route('license.index')}}"><i class='bx bx-key'></i>Lisensi</a></li>
                    </ul>
                </li>

                <li class="menu-label">LMS / E-Learning</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-book-open'></i></div>
                        <div class="menu-title">Kelola LMS</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('materials.index') }}"><i class='bx bx-radio-circle'></i>Materi Pembelajaran</a></li>
                        <li><a href="{{ route('assignments.index') }}"><i class='bx bx-radio-circle'></i>Tugas Siswa</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-building-house"></i></div>
                        <div class="menu-title">PKL</div>
                    </a>
                    <ul>
                        <li><a href="{{route('admin.timeline.index')}}"><i class='bx bx-radio-circle'></i>00.Timeline</a></li>
                        <li><a href="{{route('admin.pkl.mapping')}}"><i class='bx bx-radio-circle'></i>01.Maping</a></li>
                        <li><a href="{{route('industries.index')}}"><i class='bx bx-radio-circle'></i>02.DUDI</a></li>
                        <li><a href="{{route('admin.internships.index')}}"><i class='bx bx-radio-circle'></i>03.Penempatan</a></li>
                    </ul>
                </li>

                <li class="menu-label">Uji Kompetensi Keahlian</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-award"></i></div>
                        <div class="menu-title">UKK</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('ukk.index') }}"><i class='bx bx-radio-circle'></i>Penilaian & Plotting</a></li>
                    </ul>
                </li>

                <li class="menu-label">Absensi</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-printer'></i></div>
                        <div class="menu-title">Print</div>
                    </a>
                    <ul>
                        <li><a href="{{route('print.index')}}"><i class='bx bx-id-card'></i>Kartu</a></li>
                        <li><a href="{{route('report.surat_tugas_all')}}" target="_blank"><i class='bx bx-id-card'></i>Surat Tugas</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bxs-report"></i></div>
                        <div class="menu-title">Laporan</div>
                    </a>
                    <ul>
                        <li><a href="{{route('report.index')}}"><i class='bx bx-border-all'></i>Pembelajaran</a></li>
                        <li><a href="{{route('daily_attendance.report')}}"><i class='bx bx-border-all'></i>Gerbang</a></li>
                        <li><a href="{{route('transkrip.index')}}"><i class='bx bx-border-all'></i>Transkrip</a></li>
                        <li><a href="{{route('recap.index')}}"><i class='bx bx-id-card'></i>Rekap</a></li>
                        <li><a href="{{route('admin.permit.recap')}}"><i class='bx bx-id-card'></i>Izin</a></li>
                        <li><a href="{{route('admin.prayer_reports.index')}}"><i class='bx bx-id-card'></i>Sholat</a></li>
                        <li><a href="{{route('admin.reports.student-calendar.index')}}"><i class='bx bx-id-card'></i>Kalender</a></li>
                        <li><a href="{{route('reports.missing_attendance')}}"><i class='bx bx-id-card'></i>Alfa</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-award"></i></div>
                        <div class="menu-title">Sertifikat</div>
                    </a>
                    <ul>
                        <li><a href="{{route('certificates.index')}}"><i class='bx bx-border-all'></i>Print</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-message-alt"></i></div>
                        <div class="menu-title">WhatsApp</div>
                    </a>
                    <ul>
                        <li><a href="{{route('whatsapp.broadcast')}}"><i class='bx bx-broadcast'></i>Broadcast</a></li>
                    </ul>
                </li>
            @endrole

            {{-- ========================================== --}}
            {{-- 6. MENU OPERASIONAL (PIKET / GURU / ADMIN)  --}}
            {{-- ========================================== --}}
            @hasanyrole('piket|guru|admin')
                <li class="menu-label">CBT</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-barcode'></i></div>
                        <div class="menu-title">CBT</div>
                    </a>
                    <ul>
                        <li><a href="{{url('/exams')}}"><i class='bx bx-barcode'></i>Manage CBT</a></li>
                    </ul>
                </li>

                <li class="menu-label">Presensi & Laporan</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-barcode'></i></div>
                        <div class="menu-title">Presensi</div>
                    </a>
                    <ul>
                        <li><a href="{{url('/daily-attendance/monitor-kelas')}}"><i class='bx bx-barcode'></i>Realtime</a></li>
                        <li><a href="{{url('/daily-attendance/manual')}}"><i class='bx bx-border-all'></i>Manual</a></li>
                        <li><a href="{{url('/daily-attendance')}}"><i class='bx bx-barcode'></i>QR</a></li>
                        <li><a href="{{route('gate.monitor')}}"><i class='bx bx-face'></i>Face</a></li>
                        <li><a href="{{route('face.index')}}"><i class='bx bx-face'></i>Multi-Descriptor</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-user-voice"></i></div>
                        <div class="menu-title">Pembinaan</div>
                    </a>
                    <ul>
                        <li><a href="{{route('admin.guidance.index')}}"><i class='bx bx-radio-circle'></i>Pembinaan</a></li>
                        <li><a href="{{route('admin.guidance.create')}}"><i class='bx bx-radio-circle'></i>Tambah Pembinaan</a></li>
                        <li><a href="{{route('admin.violation-types.index')}}"><i class='bx bx-radio-circle'></i>Pelanggaran</a></li>
                        <li><a href="{{route('admin.violation-types.create')}}"><i class='bx bx-radio-circle'></i>Tambah Pelanggaran</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-door-open"></i></div>
                        <div class="menu-title">Izin Toilet</div>
                    </a>
                    <ul>
                        <li><a href="{{route('admin.permit.index')}}"><i class='bx bx-radio-circle'></i>Izin</a></li>
                    </ul>
                </li>

                @role('guru')
                    <li>
                        <a class="has-arrow" href="javascript:;">
                            <div class="parent-icon"><i class="bx bxs-report"></i></div>
                            <div class="menu-title">Laporan</div>
                        </a>
                        <ul>
                            <li><a href="{{route('daily_attendance.report')}}"><i class='bx bx-border-all'></i>Gerbang</a></li>
                        </ul>
                    </li>
                @endrole
            @endrole

            {{-- ========================================== --}}
            {{-- 7. MENU SISWA                              --}}
            {{-- ========================================== --}}
            @role('siswa')
                <li class="menu-label">Pembelajaran & LMS</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-book-reader'></i></div>
                        <div class="menu-title">Pembelajaran</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('materials.index') }}"><i class='bx bx-radio-circle'></i>Materi Pembelajaran</a></li>
                        <li><a href="{{ route('assignments.index') }}"><i class='bx bx-radio-circle'></i>Tugas Saya</a></li>
                    </ul>
                </li>

                <li class="menu-label">My Profile</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-user-detail'></i></div>
                        <div class="menu-title">My Profile</div>
                    </a>
                    <ul>
                        <li><a href="{{route('student.profile')}}"><i class='bx bx-radio-circle'></i>Profile</a></li>
                        <li><a href="{{route('student.history.subject')}}"><i class='bx bx-radio-circle'></i>Subject</a></li>
                        <li><a href="{{route('student.history.daily')}}"><i class='bx bx-radio-circle'></i>Daily</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-edit'></i></div>
                        <div class="menu-title">CBT</div>
                    </a>
                    <ul>
                        <li><a href="{{route('student.exam.index')}}"><i class='bx bx-radio-circle'></i>CBT</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-time'></i></div>
                        <div class="menu-title">Absensi</div>
                    </a>
                    <ul>
                        <li><a href="{{route('student.prayer-attendances.index')}}"><i class='bx bx-radio-circle'></i>Sholat</a></li>
                        <li><a href="{{route('student.scan')}}"><i class='bx bx-face'></i>Face</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-briefcase'></i></div>
                        <div class="menu-title">PKL</div>
                    </a>
                    <ul>
                        <li><a href="{{route('student.internships.index')}}"><i class='bx bx-radio-circle'></i>Tentukan</a></li>
                        <li><a href="{{route('student.internships.attendance.index')}}"><i class='bx bx-radio-circle'></i>Presensi</a></li>
                    </ul>
                </li>

                <li class="menu-label">Laporan</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-user-detail'></i></div>
                        <div class="menu-title">Rekap</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('student.attendance-calendar.index') }}" class="nav-link {{ request()->routeIs('student.attendance-calendar.*') ? 'active' : '' }}"><i class='bx bx-radio-circle'></i>Kehadiran</a></li>
                    </ul>
                </li>
            @endrole
        @endif
    </ul>
    <!--end navigation-->
</div>