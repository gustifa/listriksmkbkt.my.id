        <div class="page-content">

				<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                   <div class="col">
					 <div class="card radius-10 border-start border-0 border-4 border-info">
						<div class="card-body">
							<div class="d-flex align-items-center">
								<div>
									<p class="mb-0 text-secondary">Pengguna</p>
									<h4 class="my-1 text-info">{{$countUser ?? 0}}</h4>
								</div>
								<div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bx-user'></i>
								</div>
							</div>
						</div>
					 </div>
				   </div>
				   <div class="col">
					<div class="card radius-10 border-start border-0 border-4 border-danger">
					   <div class="card-body">
						   <div class="d-flex align-items-center">
							   <div>
								   <p class="mb-0 text-secondary">Guru</p>
								   <h4 class="my-1 text-danger">{{$countTeacher ?? 0}}</h4>
							   </div>
							   <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto"><i class='bx bx-user-circle'></i>
							   </div>
						   </div>
					   </div>
					</div>
				  </div>
				  
				  <div class="col">
					<div class="card radius-10 border-start border-0 border-4 border-warning">
					   <div class="card-body">
						   <div class="d-flex align-items-center">
							   <div>
								   <p class="mb-0 text-secondary">Siswa</p>
								   <h4 class="my-1 text-warning">{{$countStudent ?? 0}}</h4>
							   </div>
							   <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto"><i class='bx bxs-group'></i>
							   </div>
						   </div>
					   </div>
					</div>
				  </div>
				  
				  <div class="col">
					<div class="card radius-10 border-start border-0 border-4 border-success">
					   <div class="card-body">
						   <div class="d-flex align-items-center">
							   <div>
								   <p class="mb-0 text-secondary">Total Presensi</p>
								   <h4 class="my-1 text-success">{{$countPresensi ?? 0}}</h4>

							   </div>
							   <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bx-scan' ></i>
							   </div>
						   </div>
					   </div>
					</div>
				  </div>
				</div><!--end row-->

				<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
					<div class="col">
						<div class="card radius-10 bg-success">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-white">Revenue</p>
										<h4 class="my-1 text-white">$4805</h4>
										<!-- <p class="mb-0 font-13 text-white"><i class="bx bxs-up-arrow align-middle"></i>$34 from last week</p> -->
									</div>
									<div class="widgets-icons bg-white text-success ms-auto"><i class="bx bxs-wallet"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card radius-10 bg-info">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-dark">Total Customers</p>
										<h4 class="my-1 text-dark">8.4K</h4>
										<!-- <p class="mb-0 font-13 text-dark"><i class="bx bxs-up-arrow align-middle"></i>$24 from last week</p> -->
									</div>
									<div class="widgets-icons bg-white text-dark ms-auto"><i class="bx bxs-group"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card radius-10 bg-danger">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-white">Store Visitors</p>
										<h4 class="my-1 text-white">59K</h4>
										<!-- <p class="mb-0 font-13 text-white"><i class="bx bxs-down-arrow align-middle"></i>$34 from last week</p> -->
									</div>
									<div class="widgets-icons bg-white text-danger ms-auto"><i class="bx bxs-binoculars"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card radius-10 bg-warning">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-dark">Bounce Rate</p>
										<h4 class="my-1 text-dark">34.46%</h4>
										<!-- <p class="mb-0 font-13 text-dark"><i class="bx bxs-down-arrow align-middle"></i>12.2% from last week</p> -->
									</div>
									<div class="widgets-icons bg-white text-dark ms-auto"><i class="bx bx-line-chart-down"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
		</div>