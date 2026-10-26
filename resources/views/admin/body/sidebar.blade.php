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
            {{-- 1. SYSTEM & MANAGEMENT UTAMA (SUPER ADMIN & ADMIN) --}}
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
                        <li><a href="{{route('admin.user-roles.index')}}"><i class='bx bx-shield-quarter'></i>Role & Permission</a></li>
                        <li><a href="{{route('admin.inventory.index')}}"><i class='bx bx-box'></i>Inventaris</a></li>
                        <li><a href="{{route('prayer-location.index')}}"><i class='bx bx-map-pin'></i>Sholat</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-group"></i></div>
                        <div class="menu-title">Maping Data</div>
                    </a>
                    <ul>
                        <li><a href="{{route('teachers.index')}}"><i class='bx bx-user-voice'></i>Guru</a></li>
                        <li><a href="{{route('classrooms.index')}}"><i class='bx bxs-group'></i>Rombel</a></li>
                        <li><a href="{{route('students.index')}}"><i class='bx bxs-user-check'></i>Murid</a></li>
                        <li><a href="{{url('/face/register')}}"><i class='bx bxs-file-find'></i>Scan Wajah</a></li>
                        <li><a href="{{url('/teaching-assignments')}}"><i class='bx bx-book-bookmark'></i>PBM</a></li>
                        <li><a href="{{url('/schedule/all')}}"><i class='bx bx-calendar'></i>Jadwal</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-chip"></i></div>
                        <div class="menu-title">Administrator</div>
                    </a>
                    <ul>
                        <li><a href="{{route('admin.sync.index')}}"><i class='bx bx-sync'></i>Sinkronisasi Data</a></li>
                        <li><a href="{{route('admin.attendance.manage.index')}}"><i class='bx bx-slider-alt'></i>Manage Presensi</a></li>
                        <li><a href="{{route('admin.activity_logs.index')}}"><i class='bx bx-history'></i>Log Aktivitas</a></li>
                        <li><a href="{{route('admin.device-settings.index')}}"><i class='bx bx-mobile-vibration'></i>Reset HP Siswa</a></li>
                        <li><a href="{{route('system.update.index')}}"><i class='bx bx-cloud-download'></i>Update Sistem</a></li>
                        <li><a href="{{route('database.index')}}"><i class='bx bx-data'></i>Database</a></li>
                        <li><a href="{{route('pm2.index')}}"><i class='bx bx-server'></i>Server PM2</a></li>
                        <li><a href="{{route('whatsapp.index')}}"><i class='bx bxl-whatsapp'></i>WA Gateway</a></li>
                        <li><a href="{{route('license.index')}}"><i class='bx bx-key'></i>Lisensi</a></li>
                    </ul>
                </li>
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 2. TATA USAHA & PEGAWAI (KA TU, PEGAWAI, BENDAHARA) --}}
            {{-- ========================================== --}}
            @hasanyrole('ka_tu|pegawai|bendahara')
                <li class="menu-label">Tata Usaha & Keuangan</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-file"></i></div>
                        <div class="menu-title">Administrasi Sekolah</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('students.index') }}"><i class='bx bx-radio-circle'></i>Data Siswa</a></li>
                        <li><a href="{{ route('teachers.index') }}"><i class='bx bx-radio-circle'></i>Data Guru & Pegawai</a></li>
                        <li><a href="{{ route('documents.index') }}"><i class='bx bx-radio-circle'></i>Dokumen Sekolah</a></li>
                        <li><a href="{{ route('print.index') }}"><i class='bx bx-id-card'></i>Cetak Kartu Siswa</a></li>
                        <li><a href="{{ route('report.surat_tugas_all') }}" target="_blank"><i class='bx bx-file'></i>Surat Tugas Guru</a></li>
                    </ul>
                </li>
                @role('bendahara')
                <li>
                    <a href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-money"></i></div>
                        <div class="menu-title">Keuangan Sekolah</div>
                    </a>
                </li>
                @endrole
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 3. MANAJEMEN SEKOLAH (KEPSEK & PARA WAKA) --}}
            {{-- ========================================== --}}
            @hasanyrole('kepsek|wakakur|wakasis|wakahumas')
                <li class="menu-label">Pimpinan & Manajemen</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-bar-chart-alt-2"></i></div>
                        <div class="menu-title">Monitoring & Laporan</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('daily_attendance.report') }}"><i class='bx bx-radio-circle'></i>Presensi Gerbang</a></li>
                        <li><a href="{{ route('report.index') }}"><i class='bx bx-radio-circle'></i>Pembelajaran PBM</a></li>
                        <li><a href="{{ route('transkrip.index') }}"><i class='bx bx-radio-circle'></i>Transkrip Absensi</a></li>
                        <li><a href="{{ route('recap.index') }}"><i class='bx bx-radio-circle'></i>Rekapitulasi Sekolah</a></li>
                        <li><a href="{{ route('admin.prayer_reports.index') }}"><i class='bx bx-radio-circle'></i>Presensi Sholat</a></li>
                        <li><a href="{{ route('reports.missing_attendance') }}"><i class='bx bx-radio-circle'></i>Laporan Alfa/Abolisi</a></li>
                        <li><a href="{{ route('laporan.daya_tampung') }}"><i class='bx bx-radio-circle'></i>Usulan Daya Tampung</a></li>
                    </ul>
                </li>
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 4. KETUA PROGRAM & KEPALA BENGKEL (KAPROG & KABENG) --}}
            {{-- ========================================== --}}
            @hasanyrole('kaprog|kabeng')
                <li class="menu-label">Manajemen Kejuruan</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-sitemap'></i></div>
                        <div class="menu-title">Program Keahlian</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('students.index') }}"><i class='bx bx-radio-circle'></i>Siswa Kejuruan</a></li>
                        <li><a href="{{ route('classrooms.index') }}"><i class='bx bx-radio-circle'></i>Rombel Kejuruan</a></li>
                    </ul>
                </li>

                @role('kabeng')
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-wrench'></i></div>
                        <div class="menu-title">Bengkel & Workshop</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('admin.inventory.index') }}"><i class='bx bx-radio-circle'></i>Stok Alat & Bahan</a></li>
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
                        <li><a href="{{ route('recap.index') }}"><i class='bx bx-id-card'></i>Rekapitulasi Scope</a></li>
                    </ul>
                </li>
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 5. HUMAS & POKJA PKL (WAKAHUMAS & POKJA PKL) --}}
            {{-- ========================================== --}}
            @hasanyrole('super_admin|admin|wakahumas|pokja_pkl')
                <li class="menu-label">Humas & Praktik Kerja Lapangan</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-building-house"></i></div>
                        <div class="menu-title">Manajemen PKL</div>
                    </a>
                    <ul>
                        <li><a href="{{route('admin.timeline.index')}}"><i class='bx bx-radio-circle'></i>00. Timeline PKL</a></li>
                        <li><a href="{{route('admin.pkl.mapping')}}"><i class='bx bx-radio-circle'></i>01. Mapping Siswa</a></li>
                        <li><a href="{{route('industries.index')}}"><i class='bx bx-radio-circle'></i>02. Perusahaan DUDI</a></li>
                        <li><a href="{{route('admin.internships.index')}}"><i class='bx bx-radio-circle'></i>03. Penempatan PKL</a></li>
                    </ul>
                </li>
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 6. WALI KELAS & GURU BK                    --}}
            {{-- ========================================== --}}
            @hasanyrole('wali_kelas|guru_bk')
                <li class="menu-label">Wali Kelas & Bimbingan Konseling</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-user-voice"></i></div>
                        <div class="menu-title">Pembinaan & Konseling</div>
                    </a>
                    <ul>
                        <li><a href="{{route('admin.guidance.index')}}"><i class='bx bx-radio-circle'></i>Daftar Bimbingan</a></li>
                        <li><a href="{{route('admin.guidance.create')}}"><i class='bx bx-radio-circle'></i>Tambah Pembinaan</a></li>
                        <li><a href="{{route('admin.violation-types.index')}}"><i class='bx bx-radio-circle'></i>Master Pelanggaran</a></li>
                    </ul>
                </li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-file-find"></i></div>
                        <div class="menu-title">Laporan Binaan</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('transkrip.index') }}"><i class='bx bx-border-all'></i>Transkrip Kelas</a></li>
                        <li><a href="{{ route('admin.permit.recap') }}"><i class='bx bx-border-all'></i>Rekap Izin Siswa</a></li>
                    </ul>
                </li>
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 7. PEMBINA EKSTRAKURIKULER                 --}}
            {{-- ========================================== --}}
            @role('pembina_ekskul')
                <li class="menu-label">Ekstrakurikuler</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-football'></i></div>
                        <div class="menu-title">Kegiatan Ekskul</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('kegiatan.index') }}"><i class='bx bx-radio-circle'></i>Presensi Kegiatan</a></li>
                    </ul>
                </li>
            @endrole

            {{-- ========================================== --}}
            {{-- 8. PEMBELAJARAN, LMS & CBT (GURU)          --}}
            {{-- ========================================== --}}
            @role('guru')
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
                        <div class="parent-icon"><i class='bx bx-calendar-event'></i></div>
                        <div class="menu-title">PBM & Schedule</div>
                    </a>
                    <ul>
                        <li><a href="{{route('schedule.index')}}"><i class='bx bx-radio-circle'></i>Jadwal Mengajar</a></li>
                        <li><a href="{{route('journal.index')}}"><i class='bx bx-radio-circle'></i>Jurnal Mengajar</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-edit'></i></div>
                        <div class="menu-title">Ujian CBT</div>
                    </a>
                    <ul>
                        <li><a href="{{url('/exams')}}"><i class='bx bx-radio-circle'></i>Kelola Bank Soal & Ujian</a></li>
                    </ul>
                </li>

                @if(Auth::user()->teacher && Auth::user()->teacher->isAdvisor())
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-briefcase-alt-2'></i></div>
                        <div class="menu-title">Pembimbing PKL</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('teacher.internships.index') }}"><i class='bx bx-radio-circle'></i>Bimbingan & Penilaian</a></li>
                    </ul>
                </li>
                @endif
            @endrole

            {{-- ========================================== --}}
            {{-- 9. OPERASIONAL PRESENSI, PIKET & SECURITY --}}
            {{-- ========================================== --}}
            @hasanyrole('super_admin|admin|piket|security|guru')
                <li class="menu-label">Operasional & Keamanan</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-qr-scan'></i></div>
                        <div class="menu-title">Presensi</div>
                    </a>
                    <ul>
                        <li><a href="{{url('/daily-attendance/monitor-kelas')}}"><i class='bx bx-tv'></i>Realtime Kelas</a></li>
                        <li><a href="{{route('gate.monitor')}}"><i class='bx bx-face'></i>Monitor Wajah Gerbang</a></li>
                        <li><a href="{{url('/daily-attendance/manual')}}"><i class='bx bx-edit'></i>Presensi Manual</a></li>
                        <li><a href="{{url('/daily-attendance')}}"><i class='bx bx-qr'></i>Scanner QR Code</a></li>
                        <li><a href="{{route('face.index')}}"><i class='bx bx-user-voice'></i>Multi-Descriptor Wajah</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class="bx bx-door-open"></i></div>
                        <div class="menu-title">Izin Dispensasi</div>
                    </a>
                    <ul>
                        <li><a href="{{route('admin.permit.index')}}"><i class='bx bx-radio-circle'></i>Scan Izin</a></li>
                    </ul>
                </li>
            @endhasanyrole

            {{-- ========================================== --}}
            {{-- 10. SISWA                                  --}}
            {{-- ========================================== --}}
            @role('siswa')
                <li class="menu-label">Area Siswa</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bx-book-reader'></i></div>
                        <div class="menu-title">Pembelajaran LMS</div>
                    </a>
                    <ul>
                        <li><a href="{{ route('materials.index') }}"><i class='bx bx-radio-circle'></i>Materi Pembelajaran</a></li>
                        <li><a href="{{ route('assignments.index') }}"><i class='bx bx-radio-circle'></i>Tugas Saya</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-edit'></i></div>
                        <div class="menu-title">Ujian Online (CBT)</div>
                    </a>
                    <ul>
                        <li><a href="{{route('student.exam.index')}}"><i class='bx bx-radio-circle'></i>Daftar Ujian</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-time'></i></div>
                        <div class="menu-title">Presensi Mandiri</div>
                    </a>
                    <ul>
                        <li><a href="{{route('student.scan')}}"><i class='bx bx-face'></i>Scan Masuk Wajah</a></li>
                        <li><a href="{{route('student.prayer-attendances.index')}}"><i class='bx bx-radio-circle'></i>Presensi Sholat</a></li>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-briefcase'></i></div>
                        <div class="menu-title">Praktik Kerja Lapangan</div>
                    </a>
                    <ul>
                        <li><a href="{{route('student.internships.index')}}"><i class='bx bx-radio-circle'></i>Pengajuan DUDI</a></li>
                        <li><a href="{{route('student.internships.attendance.index')}}"><i class='bx bx-radio-circle'></i>Jurnal & Presensi PKL</a></li>
                    </ul>
                </li>

                <li class="menu-label">Profil & Rekap</li>
                <li>
                    <a class="has-arrow" href="javascript:;">
                        <div class="parent-icon"><i class='bx bxs-user-detail'></i></div>
                        <div class="menu-title">Akun Saya</div>
                    </a>
                    <ul>
                        <li><a href="{{route('student.profile')}}"><i class='bx bx-radio-circle'></i>Profil Siswa</a></li>
                        <li><a href="{{route('student.history.subject')}}"><i class='bx bx-radio-circle'></i>Riwayat PBM</a></li>
                        <li><a href="{{route('student.history.daily')}}"><i class='bx bx-radio-circle'></i>Riwayat Gerbang</a></li>
                        <li><a href="{{ route('student.attendance-calendar.index') }}"><i class='bx bx-radio-circle'></i>Kalender Kehadiran</a></li>
                    </ul>
                </li>
            @endrole

        @endif
    </ul>
    <!--end navigation-->
</div>