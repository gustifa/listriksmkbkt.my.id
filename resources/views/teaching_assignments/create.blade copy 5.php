@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    
            <div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Forms</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Select2</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">
						<div class="btn-group">
							<button type="button" class="btn btn-primary">Settings</button>
							<button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">	<span class="visually-hidden">Toggle Dropdown</span>
							</button>
							<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">	<a class="dropdown-item" href="javascript:;">Action</a>
								<a class="dropdown-item" href="javascript:;">Another action</a>
								<a class="dropdown-item" href="javascript:;">Something else here</a>
								<div class="dropdown-divider"></div>	<a class="dropdown-item" href="javascript:;">Separated link</a>
							</div>
						</div>
					</div>
				</div>
				<!--end breadcrumb-->
				<div class="row">
					<div class="col-xl-9 mx-auto">

						<h6 class="mb-0 text-uppercase">Multiple select</h6>
						 <hr/>
						<div class="card">
						   <div class="card-body">
                               
							<div class="mb-4">
								<label for="multiple-select-field" class="form-label">Basic multiple select</label>
								<select class="form-select" id="multiple-select-field" data-placeholder="Choose anything" multiple>
									<option selected>Christmas Island</option>
									<option selected>South Sudan</option>
									<option selected>Jamaica</option>
									<option>Kenya</option>
									<option>French Guiana</option>
									<option>Mayotta</option>
									<option>Liechtenstein</option>
									<option>Denmark</option>
									<option>Eritrea</option>
									<option>Gibraltar</option>
									<option>Saint Helena, Ascension and Tristan da Cunha</option>
									<option>Haiti</option>
									<option>Namibia</option>
									<option>South Georgia and the South Sandwich Islands</option>
									<option>Vietnam</option>
									<option>Yemen</option>
									<option>Philippines</option>
									<option>Benin</option>
									<option>Czech Republic</option>
									<option>Russia</option>
								</select>
							</div>

							

						   </div>
						</div>



					</div>
				</div>
				<!--end row-->
			</div>

</x-app-layout>