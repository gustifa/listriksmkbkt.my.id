@extends('admin.admin_dashboard')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
@section('title')
   Tambah Permission
@endsection
<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Kelas</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('all.category')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Permission</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="row">
        <div class="mx-auto col-xl-12">

            {{-- <h6 class="mb-0 text-uppercase">Add Category</h6> --}}
            <hr/>
            <div class="card">
                <div class="card-body">
                    <form id="myForm" method="post" action="{{route('update.permission')}}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" value="{{$permission->id}}" name="id">
                        <div class="mb-3 form-group">
                            <label class="form-label">Nama Permission:</label>
                            <input type="text" class="form-control" name="name" value="{{$permission->name}}">
                        </div>

                        <div class="mb-3 form-group">
                            <label class="form-label">Nama Group:</label>
                            <select name="group_name" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Guru</option>
                               
                                <option data-select2-id="select2-data-77-kb3z" value="user"{{$permission->group_name == 'user' ? 'selected' : ''}}>User</option>
                                <option data-select2-id="select2-data-77-kb3z" value="maping"{{$permission->group_name == 'maping' ? 'selected' : ''}}>Maping</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="px-5 btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>


        </div>
    </div>
    <!--end row-->
</div>

@endsection
