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
                <img src="{{ asset('storage/'.$appLogo) }}" alt="Logo" width="40" height="40" class="align-text-top rounded d-inline-block me-2" style="object-fit: cover;">
            @else
                <img src="{{ asset('upload/no_image.jpg')}}" alt="logo title" width="40" height="40" class="align-text-top rounded d-inline-block me-2" style="object-fit: cover;">
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
        @unlessrole('alumni')
        <li>
            <a href="{{route('dashboard')}}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>
        @endunlessrole

        @if ($status === '1')

            {{-- 1. SYSTEM & MANAGEMENT UTAMA (SUPER ADMIN & ADMIN) --}}
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

            {{-- 5. HUMAS, PKL & BKK ALUMNI --}}
            @hasanyrole('super_admin|admin|wakahumas|pokja_pkl')
                <li class="menu-label">Humas, PKL & BKK</li>
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
                @hasanyrole('super_admin|admin|wakahumas')
                <li>
                    <a href="{{ route('alumni.index') }}" class="{{ request()->routeIs('alumni.*') ? 'mm-active' : '' }}">
                        <div class="parent-icon"><i class="bx bx-user-check"></i></div>
                        <div class="menu-title">Data Alumni (BKK)</div>
                    </a>
                </li>
                @endhasanyrole
            @endhasanyrole

            {{-- 10. SISWA AKTIF --}}
            @role('siswa')
                <li class="menu-label">Area Siswa Aktif</li>
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
            @endrole

            {{-- 11. AREA KHUSUS ALUMNI (TRACER STUDY & BKK) --}}
            @role('alumni')
                <li class="menu-label">Portal Alumni & BKK</li>
                <li>
                    <a href="{{ route('alumni.portal.dashboard') }}" class="{{ request()->routeIs('alumni.portal.dashboard') ? 'mm-active' : '' }}">
                        <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                        <div class="menu-title">Dashboard Alumni</div>
                    </a>
                </li>
                <li>
                    <a href="" class="">
                        <div class="parent-icon"><i class='bx bx-id-card'></i></div>
                        <div class="menu-title">Profil & CV Saya</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('alumni.portal.jobs') }}" class="{{ request()->routeIs('alumni.portal.jobs') ? 'mm-active' : '' }}">
                        <div class="parent-icon"><i class='bx bx-briefcase'></i></div>
                        <div class="menu-title">Bursa Kerja (BKK)</div>
                    </a>
                </li>
            @endrole

        @endif
    </ul>
</div>