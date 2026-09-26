@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Dashboard Admin
@endsection

@php
$all_user = App\Models\User::all();
$user_siswa = App\Models\User::where('jenis_user', 'siswa')->get();
$user_guru = App\Models\User::where('jenis_user', 'guru')->get();
$user_wakil = App\Models\User::where('jenis_user', 'wakil')->get();
$user_admin = App\Models\User::where('jenis_user', 'admin')->get();
$sekolah = App\Models\Sekolah::find(1);
$proka = App\Models\Proka::all();
$rombel = App\Models\Rombel::all();
$jadwal = App\Models\JadwalPelajaran::all();
@endphp
<div class="page-content">
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
       <div class="col">
         <div class="border-0 border-4 card radius-10 border-start border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Jumlah Peserta Didik</p>

                        <h4 class="my-1 text-info">{{count($user_siswa)}}</h4>

                    </div>
                    <div class="text-white widgets-icons-2 rounded-circle bg-gradient-blues ms-auto"><i class='bx bxs-group'></i>
                    </div>
                </div>
            </div>
         </div>
       </div>
       <div class="col">
        <div class="border-0 border-4 card radius-10 border-start border-danger">
           <div class="card-body">
               <div class="d-flex align-items-center">
                   <div>
                       <p class="mb-0 text-secondary">Jumlah Pendidik</p>
                       <h4 class="my-1 text-danger">{{count($user_guru)}}</h4>
                       <!-- <p class="mb-0 font-13">+5.4% from last week</p> -->
                   </div>
                   <div class="text-white widgets-icons-2 rounded-circle bg-gradient-burning ms-auto"><i class='bx bxs-user'></i>
                   </div>
               </div>
           </div>
        </div>
      </div>
      <div class="col">
        <div class="border-0 border-4 card radius-10 border-start border-success">
           <div class="card-body">
               <div class="d-flex align-items-center">
                   <div>
                       <p class="mb-0 text-secondary">Jumlah Staff</p>
                       <h4 class="my-1 text-success">{{count($user_wakil)}}</h4>
                       <!-- <p class="mb-0 font-13">-4.5% from last week</p> -->
                   </div>
                   <div class="text-white widgets-icons-2 rounded-circle bg-gradient-ohhappiness ms-auto"><i class='bx bxs-bar-chart-alt-2' ></i>
                   </div>
               </div>
           </div>
        </div>
      </div>
      <div class="col">
        <div class="border-0 border-4 card radius-10 border-start border-warning">
           <div class="card-body">
               <div class="d-flex align-items-center">
                   <div>
                       <p class="mb-0 text-secondary">Jumlah Admin</p>
                       <h4 class="my-1 text-warning">{{count($user_admin)}}</h4>
                       <!-- <p class="mb-0 font-13">+8.4% from last week</p> -->
                   </div>
                   <div class="text-white widgets-icons-2 rounded-circle bg-gradient-orange ms-auto"><i class='bx bxs-group'></i>
                   </div>
               </div>
           </div>
        </div>
      </div>
                    <div class="col">
						<div class="card radius-10 bg-primary bg-gradient">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-white">Jumlah Proka</p>
										<h4 class="my-1 text-white">{{count($proka)}}</h4>
									</div>
									<div class="text-white ms-auto font-35"><i class='bx bx-windows'></i>
									</div>
								</div>
							</div>
						</div>
					</div>
                    <div class="col">
						<div class="card radius-10 bg-danger bg-gradient">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-white">Jumlah Rombel</p>
										<h4 class="my-1 text-white">{{count($rombel)}}</h4>
									</div>
									<div class="text-white ms-auto font-35"><i class='bx bx-group'></i>
									</div>
								</div>
							</div>
						</div>
					</div>
                    <div class="col">
						<div class="card radius-10 bg-warning bg-gradient">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-dark">Total Pengguna</p>
										<h4 class="my-1 text-dark">{{count($all_user)}}</h4>
									</div>
									<div class="text-dark ms-auto font-35"><i class='bx bx-user'></i>
									</div>
								</div>
							</div>
						</div>
					</div>
                    <div class="col">
						<div class="card radius-10 bg-success bg-gradient">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-white">Total Jadwal</p>
										<h4 class="my-1 text-white">{{count($jadwal)}}</h4>
									</div>
									<div class="text-white ms-auto font-35"><i class='bx bx-calendar-event'></i>
									</div>
								</div>
							</div>
						</div>
					</div>

    </div><!--end row-->
    <div class="row">
       <div class="col-12 col-lg-8 d-flex">
            <div class="card radius-10 w-100">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0 box-title">{{$sekolah->nama}}</h6>
                        </div>
                    <div class="dropdown ms-auto">
                        <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="javascript:;">Action</a>
                            </li>
                            <li><a class="dropdown-item" href="javascript:;">Another action</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="javascript:;">Something else here</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
                <div class="card-body">
                <div class="col-lg-12 col-xs-12">
					<div class="box-header with-border">

						<!-- <h5 class="box-title"><strong>Identitas Sekolah</strong></h5> -->
					</div>
			<table class="table table-condensed">
				<tbody>
                        <!-- <tr>
							<td width="30%">Nama Sekolah</td>
							<td width="70%">: {{$sekolah->nama}}</td>
						</tr> -->
					<tr>
						<td>NPSN</td>
						<td>: {{$sekolah->npsn}}</td>
					</tr>
					<tr>
						<td>Alamat</td>
						<td>: {{$sekolah->alamat}}</td>
					</tr>
					<tr>
						<td>Kodepos</td>
						<td>: {{$sekolah->kode_pos}}</td>
					</tr>
					<tr>
						<td>Desa/Kelurahan</td>
						<td>: {{$sekolah->desa_kelurahan}}</td>
					</tr>
					<tr>
						<td>Kecamatan</td>
						<td>: {{$sekolah->kecamatan}}</td>
					</tr>
					<tr>
						<td>Kabupaten/Kota</td>
						<td>: {{$sekolah->kabupaten}}</td>
					</tr>
					<tr>
						<td>Provinsi</td>
						<td>: {{$sekolah->provinsi}}</td>
					</tr>
					<tr>
						<td>Email</td>
						<td>: {{$sekolah->email}}</td>
					</tr>
					<tr>
						<td>Website</td>
						<td>: {{$sekolah->website}}</td>
					</tr>
					<tr>
						<td>Kepala Sekolah</td>
						<td>: {{$sekolah->kepsek->name}}</td>
					</tr>
				</tbody></table>
			</div>

                </div>
          </div>
       </div>
       <div class="col-12 col-lg-4 d-flex">
           <div class="card radius-10 w-100">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">Informasi</h6>
                    </div>
                    <div class="dropdown ms-auto">
                        <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="javascript:;">Action</a>
                            </li>
                            <li><a class="dropdown-item" href="javascript:;">Another action</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="javascript:;">Something else here</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
               <div class="card-body">
                <div class="chart-container-2">
                    <canvas id="chart2"></canvas>
                  </div>
               </div>

           </div>
       </div>
    </div><!--end row-->

</div>

@endsection
