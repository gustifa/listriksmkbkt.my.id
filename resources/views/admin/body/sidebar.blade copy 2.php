@php
$appLogo = \App\Models\Setting::value('app_logo');
$appName = \App\Models\Setting::value('app_name', 'E-Absensi');
$id = Auth::user()->id;
$guruId = App\Models\User::find($id);
$status = $guruId->status;
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
                        alt="logo titl"
                        width="40"
                        height="40"
                        class="align-text-top rounded d-inline-block me-2"
                        style="object-fit: cover;">
                @endif
            </div>

        <div>
            <h4 class="logo-text">{{ $appName ?? 'GATECH' }}</h4> </div>

        <div class="toggle-icon ms-auto">
            <i class='bx bx-arrow-back'></i>
        </div>
    </div>
			<!--navigation-->
		<li>
			<ul class="metismenu" id="menu">
                <li>
					<a href="{{route('dashboard')}}">
						<div class="parent-icon"><i class='bx bx-home-alt'></i>
						</div>
						<div class="menu-title">Dashboard</div>
					</a>
				</li>
				@if ($status === '1' ?? '0')
                @role('guru')

				<!-- Menu Guru -->
					@if(Auth::user()->hasRole('guru') && Auth::user()->teacher)

						<li class="menu-label">Pembelajaran</li>
						<li>
							<a class="has-arrow" href="javascript:;">
								<div class="parent-icon"><i class='bx bx-bookmark-heart'></i>
								</div>
								<div class="menu-title">Schedule</div>
							</a>
							<ul>
								<li> <a href="{{route('schedule.index')}}"><i class='bx bx-radio-circle'></i>PBM</a></li>
								<li> <a href="{{route('journal.index')}}"><i class='bx bx-radio-circle'></i>Jurnal</a></li>

							</ul>
						</li>

						<!-- Menu Bimbingan PKL (Hanya Jika Guru Adalah Pembimbing) -->
						@if(Auth::user()->teacher->isAdvisor())
						<li>
							<a class="has-arrow" href="javascript:;">
								<div class="parent-icon"><i class='bx bx-bookmark-heart'></i>
								</div>
								<div class="menu-title">PKL</div>
							</a>
							<ul>
								<li> <a href="{{ route('teacher.internships.index') }}"><i class='bx bx-radio-circle'></i>Bimbingan</a></li>

							</ul>
						</li>
						@endif
					@endif

                @endrole

				<!-- MENU PENGATURAN (Hanya muncul untuk Super Admin & Admin) -->
				@hasanyrole('super_admin|admin')

				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-cog"></i>
						</div>
						<div class="menu-title">Setting</div>
					</a>
					<ul>
						<li> <a href="{{route('settings.index')}}"><i class='bx bx-home-smile'></i>Sekolah</a></li>
						<li> <a href="{{route('all.user')}}"><i class='bx bx-user'></i>User</a></li>
                        <li> <a href="{{route('settings.attendance')}}"><i class='bx bx-barcode-reader'></i>Presensi</a></li>
                        <li> <a href="{{route('majors.index')}}"><i class='bx bx-minus-front'></i>Jurusan</a></li>
                        <li> <a href="{{route('subjects.index')}}"><i class='bx bx-minus-front'></i>Mapel</a></li>
						<li> <a href="{{route('rooms.index')}}"><i class='bx bx-radio-circle'></i>Bengkel</a>
						<li> <a href="{{route('permissions.index')}}"><i class='bx bx-radio-circle'></i>Permission</a>
						</li>
                        <li> <a href="{{route('roles.index')}}"><i class='bx bx-radio-circle'></i>Role</a>
                        <li> <a href="{{route('inventory.index')}}"><i class='bx bx-radio-circle'></i>Inventaris</a>
					</li>

					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-cog"></i>
						</div>
						<div class="menu-title">Maping</div>
					</a>
					<ul>
						<li> <a href="{{route('teachers.index')}}"><i class='bx bx-radio-circle'></i>Guru</a></li>
						<li> <a href="{{route('classrooms.index')}}"><i class='bx bxs-group'></i>Rombel</a></li>
						<li> <a href="{{route('students.index')}}"><i class='bx bxs-user-check'></i>Murid</a></li>
						<!-- <li> <a href="{{url('/face/register')}}"><i class='bx bxs-file-find'></i>Scan Wajah</a></li> -->
						<li> <a href="{{url('/teaching-assignments')}}"><i class='bx bx-home-smile'></i>PBM</a></li>
						<li> <a href="{{url('/schedule/all')}}"><i class='bx bx-home-smile'></i>Jadwal</a></li>

					</li>

					</ul>
				</li>

                <li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-cog"></i>
						</div>
						<div class="menu-title">PKL</div>
					</a>
					<ul>
						<li> <a href="{{route('admin.timeline.index')}}"><i class='bx bx-radio-circle'></i>00.Timeline</a></li>
						<li> <a href="{{route('admin.pkl.mapping')}}"><i class='bx bx-radio-circle'></i>01.Maping</a></li>
						<li> <a href="{{route('industries.index')}}"><i class='bx bx-radio-circle'></i>02.DUDI</a></li>
						<li> <a href="{{route('admin.internships.index')}}"><i class='bx bx-radio-circle'></i>03.Penempatan</a></li>
					</ul>
				</li>

                <li class="menu-label">Forms & Tables</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-printer'></i>
						</div>
						<div class="menu-title">Print</div>
					</a>
					<ul>
						<li> <a href="{{route('print.index')}}"><i class='bx bx-id-card'></i>Kartu</a></li>
						<li> <a href="{{route('report.surat_tugas_all')}}" target="_blank"><i class='bx bx-id-card'></i>Surat Tugas</a></li>


					</ul>
				</li>


				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-grid-alt"></i>
					</div>
					<div class="menu-title">GTK</div>
				</a>
				<ul>
					<li> <a href="{{route('users.import')}}"><i class='bx bx-radio-circle'></i>Impor Guru</a>
				</li>
			</ul>
		</li> 

		<li>
			<a class="has-arrow" href="javascript:;">
				<div class="parent-icon"> <i class="bx bxs-report"></i></div>
				<div class="menu-title">Laporan</div>
			</a>
		<ul>
			<li> <a href="{{route('report.index')}}"><i class='bx bx-border-all'></i>Pembelajaran</a>
			<li> <a href="{{route('daily_attendance.report')}}"><i class='bx bx-border-all'></i>Gerbang</a>
			<li> <a href="{{url('/transkrip')}}"><i class='bx bx-border-all'></i>Transkip</a>
			<li> <a href="{{route('recap.index')}}"><i class='bx bx-id-card'></i>Rekap</a>
		</li>

					</ul>
				</li>

                <li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"> <i class="bx bxs-report"></i>
						</div>
						<div class="menu-title">Sertifkat</div>
					</a>
					<ul>
						<li> <a href="{{route('certificates.index')}}"><i class='bx bx-border-all'></i>Print</a>
						</li>

					</ul>
				</li>

				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"> <i class="bx bx-message-alt"></i>
						</div>
						<div class="menu-title">WhatAPP</div>
					</a>
					<ul>
						 <li> <a href="{{route('whatsapp.broadcast')}}"><i class='bx bx-broadcast'></i>Broadcast</a>
						</li>

					</ul>
				</li>

				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-credit-card'></i>
						</div>
						<div class="menu-title">Aplikasi</div>
					</a>
					<ul>
						<li> <a href="{{route('system.update.index')}}"><i class='bx bx-id-card'></i>Update</a></li>
						<li> <a href="{{route('database.index')}}"><i class='bx bx-id-card'></i>Database</a></li>
						<li> <a href="{{route('pm2.index')}}"><i class='bx bx-id-card'></i>server</a></li>
						<li> <a href="{{route('whatsapp.index')}}"><i class='bx bx-id-card'></i>WA</a></li>



					</ul>
				</li>
<!--
				<li>
					<a href="{{route('system.update.index')}}">
						<div class="parent-icon"><i class='bx bxl-microsoft'></i>
						</div>
						<div class="menu-title">Update Aplikasi</div>
					</a>
				</li> -->

                @endrole

				<!-- MENU PENGATURAN (Hanya muncul untuk Super Selain Admin & Admin) -->
				@hasanyrole('piket|guru|admin')
                <li class="menu-label">Rekap Datang & Pulang</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bxs-barcode'></i>
						</div>
						<div class="menu-title">Presensi</div>
					</a>
					<ul>
						<li> <a href="{{url('/daily-attendance/monitor-kelas')}}"><i class='bx bx-barcode'></i>Realtime</a>
						<li> <a href="{{url('/daily-attendance/manual')}}"><i class='bx bx-border-all'></i>Manual</a>
						<li> <a href="{{url('/daily-attendance')}}"><i class='bx bx-barcode'></i>QR</a>
						<li> <a href="{{route('daily.face.scan')}}"><i class='bx bx-face'></i>Face</a>
						</li>

					</ul>
				</li>
                @endrole

                @role('siswa')
                <li class="menu-label">My Profile</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bxs-barcode'></i>
						</div>
						<div class="menu-title">My Profile</div>
					</a>
					<ul>
						<li> <a href="{{route('student.profile')}}"><i class='bx bx-radio-circle'></i>Profile</a></li>
						<li> <a href="{{route('student.history.subject')}}"><i class='bx bx-radio-circle'></i>Subject</a></li>
						<li> <a href="{{route('student.history.daily')}}"><i class='bx bx-radio-circle'></i>Daily</a></li>

					</ul>
				</li>

                <li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bxs-barcode'></i>
						</div>
						<div class="menu-title">Absensi</div>
					</a>
					<ul>
						<li> <a href="{{route('student.prayer.index')}}"><i class='bx bx-radio-circle'></i>Sholat</a></li>


					</ul>
				</li>

                <li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bxs-barcode'></i>
						</div>
						<div class="menu-title">PKL</div>
					</a>
					<ul>
						<li> <a href="{{route('student.internships.index')}}"><i class='bx bx-radio-circle'></i>Tentukan</a></li>
						<li> <a href="{{route('student.internships.attendance.index')}}"><i class='bx bx-radio-circle'></i>Presensi</a></li>


					</ul>
				</li>
                @endrole
				@endif

			</ul>
			<!--end navigation-->
		</div>
		<!--end sidebar wrapper -->
