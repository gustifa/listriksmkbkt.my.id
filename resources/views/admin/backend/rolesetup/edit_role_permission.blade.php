@extends('admin.admin_dashboard')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
@section('title')
   Tambah Roles Permission
@endsection
<style>
    .form-check-label{
        text-transform: capitalize;
    }
</style>
<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Roles Permission</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('all.roles')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Roles Permission</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->
    <div class="row">
        <div class="mx-auto col-xl-12">
            <div class="card">
                <div class="card-body">
                    <form id="myForm" method="post" action="{{route('update.roles.permission', $role->id)}}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3 form-group">
                            {{-- <select name="role_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                <option data-select2-id="select2-data-77-kb3z" value="{{$roles->id}}">{{$roles->name}}</option>
                            </select> --}}
                            <h2 class="mb-3 form-group">{{$role->name}}</h2>
                        </div>
                        
                        <div class="mb-3 form-group">
                            
                            <input class="form-check-input" type="checkbox" value="" id="flexCheckMain">
                            <label class="form-check-label"> Permission All</label>
                        </div>

                        <hr>
                        
                        @foreach($permission_groups as $item)
                        <div class="row">
                            <div class="col-3">

                                @php
                                $permissions = App\Models\User::getPermissionGroupByName($item->group_name)
                                @endphp
                                <div class="mb-3 form-group">
                                   
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"{{App\Models\User::roleHasPermissions($role, $permissions) ? 'checked' : ''}}>
                                    <label class="form-check-label" for="flexCheckDefault"> {{$item->group_name}}</label>
                                   
                                </div>
                            </div>
                            <div class="col-9">
                                
                                @foreach($permissions as $permission)
                                <div class="col-3">
                                    <div class="mb-3 form-group">
                                       
                                        <input class="form-check-input" type="checkbox" name="permission[]" value="{{$permission->id}}" id="checkDefault{{$permission->id}}" {{ $role->hasPermissionTo($permission->name) ? 'checked' : ''}}>
                                        <label class="form-check-label" for="checkDefault{{$permission->id}}"> {{$permission->name}}</label>
                                       
                                    </div>
                                </div>
                                @endforeach
                            </div>

                        </div>
                         @endforeach
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

<script>
   $('#flexCheckMain').click(function(){
    if ($(this).is(':checked')){
        $('input[ type=checkbox').prop('checked', true)
    }else{
        $('input[ type=checkbox').prop('checked', false)
    }
   }); 
</script>

<script type="text/javascript">
    $(document).ready(function (){
        $('#myForm').validate({
            rules: {
                nama_kelas: {
                    required : true,
                },

            },
            messages :{
                nama_kelas: {
                    required : 'Nama Kelas Tidak Boleh Kosong',
                },


            },
            errorElement : 'span',
            errorPlacement: function (error,element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight : function(element, errorClass, validClass){
                $(element).addClass('is-invalid');
            },
            unhighlight : function(element, errorClass, validClass){
                $(element).removeClass('is-invalid');
            },
        });
    });

</script>
@endsection
