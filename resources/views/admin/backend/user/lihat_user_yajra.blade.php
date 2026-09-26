@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Lihat User
@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<style>
    .large-chexbox{
        transform: scale(1.5);
        /* margin-left: 2em; */
    }
</style>
<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Pengguna</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Pengguna</li>
                </ol>
            </nav>
        </div>

    </div>
    <!--end breadcrumb-->
    <div class="mb-3">
        {{-- <a href="{{route('import.user')}}" class="btn btn-primary">Tambah User</a> --}}
        <a href="" class="btn btn-primary" title="Print Pengguna Siswa" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bx bx-import"></i></a>
        <a href="{{route('cetak.semua.user')}}" class="btn btn-danger" title="Print Pengguna Siswa" target="_blank"><i class="lni lni-printer"></i></a>
        <a href="{{route('cetak.guru.user')}}" class="btn btn-warning" title="Print Pengguna Guru" target="_blank"><i class="lni lni-printer"></i></a>
        <a href="{{route('cetak.wakil.user')}}" class="btn btn-primary" title="Print Pengguna Wakil" target="_blank"><i class="lni lni-printer"></i></a>

    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered data-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th width="100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

                
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function (){
        $('#myForm').validate({
            rules: {
                file: {
                    required : true,
                },

            },
            messages :{
                file: {
                    required : 'Belum Ada File',
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

<script type="text/javascript">
    $(function () {
    // var i = 1;    
      var table = $('.data-table').DataTable({
          processing: true,
          serverSide: true,
          ajax: "{{ route('lihat.user.yajra') }}",
          columns: [
            
              {data: 'id', name: 'id'},
              {data: 'name', name: 'name'},
              {data: 'email', name: 'email'},
              {data: 'action', name: 'action', orderable: false, searchable: false},
          ]
          
      });
          
    });
  </script>

@endsection
