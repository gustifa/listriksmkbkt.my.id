@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Forms</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Form Elements</li>
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
        <div class="col-xl-12 mx-auto">
            <h6 class="mb-0 text-uppercase">Add Category</h6>
            <hr/>
            <div class="card">
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date time:</label>
                            <input type="datetime-local" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email:</label>
                            <input type="email" class="form-control" placeholder="example@gmail.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password:</label>
                            <input type="password" class="form-control" value="........">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Input File:</label>
                            <input type="file" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Month:</label>
                            <input type="month" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Search:</label>
                            <input type="search" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tel:</label>
                            <input type="tel" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time:</label>
                            <input type="time" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Url:</label>
                            <input type="url" class="form-control" placeholder="https://example.com/users/">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Week:</label>
                            <input type="week" class="form-control">
                        </div>
                    </form>
                </div>
            </div>
            
            
        </div>
    </div>
    <!--end row-->
</div>
@endsection