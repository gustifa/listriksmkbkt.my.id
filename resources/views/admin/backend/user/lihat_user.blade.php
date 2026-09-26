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
        <a href="" class="btn btn-primary" title="Import User" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bx bx-import"></i> User</a>
        <a href="{{route('cetak.semua.user')}}" class="btn btn-danger" title="Print Pengguna Siswa" target="_blank"><i class="lni lni-printer"></i> All</a>
        <a href="{{route('cetak.guru.user')}}" class="btn btn-warning" title="Print Pengguna Guru" target="_blank"><i class="lni lni-printer"></i> Guru</a>
        <a href="{{route('cetak.wakil.user')}}" class="btn btn-primary" title="Print Pengguna Wakil" target="_blank"><i class="lni lni-printer"></i> Wakil</a>

    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5px;">No</th>
                            <th>Nama Pengguna</th>
                            <th>Email</th>
                            <th>Hak Akses</th>
                            <th>Barcode</th>
                            <th>Status</th>
                            <th style="width: 40px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $key=> $item)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$item->name}}</td>
                            <td>{{$item->email}}</td>
                            <td>
                                @if ($item->jenis_user == 'siswa')
                                    <span class="badge bg-success">Siswa</span>
                                @elseif($item->jenis_user == 'guru')
                                    <span class="badge bg-danger">Guru</span>
                                @elseif($item->jenis_user == 'wakil')
                                    <span class="badge bg-danger">Wakil</span>
                                @elseif($item->jenis_user == 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @endif
                            </td>
                            <!-- <td>{!! DNS2D::getBarcodeHTML('$ '. $item->name, 'QRCODE') !!}</td> -->
                            <td>{!! DNS2D::getBarcodeHTML($item->name, 'QRCODE', 2,2) !!}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input large-chexbox status-toggle" type="checkbox" role="switch" id="flexSwitchCheckDefault1" data-user="{{$item->id}}" {{$item->status ? 'checked' : ''}} >
                                    <label class="form-check-label" for="flexSwitchCheckDefault1"></label>
                                  </div>
                        </td>
                            <td>
                                <a href="{{route('cetak.per.user', $item->id)}}" class="btn btn-warning" title="Detail User" target="_blank"><i class="bx bx-detail"></i></a>
                                <a href="{{route('cetak.per.user', $item->id)}}" class="btn btn-info" title="Print" target="_blank"><i class="lni lni-printer"></i></a>
                                <a href="" class="btn btn-info" title="Edit"><i class="bx bx-edit"></i></a>
                                <a href="" id="delete" class="btn btn-danger" id="delete" title="delete"><i class="lni lni-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach


                    </tbody>

                </table>

                {{-- Awal Modal --}}
        <div class="col">
            <!-- Button trigger modal -->
            {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Import User</button> --}}
            {{-- <a href="" class="btn btn-primary" title="Print Pengguna Siswa" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bx bx-import"></i></a>
            <a href="{{route('cetak.semua.user')}}" class="btn btn-danger" title="Print Pengguna Siswa" target="_blank"><i class="lni lni-printer"></i></a> --}}
            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Import User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                                    <form id="myForm" method="post" action="{{route('users.import')}}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3 form-group">
                                            <label class="form-label">Input File: </label>
                                            <input type="file" class="form-control" name="file" id="file">

                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" class="px-5 btn btn-primary">Import</button>
                                        </div>
                                        <div class="mb-3">
                                            <a href="{{route('download.template.user')}}">Download Template</a>
                                        </div>

                                    </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            {{-- <button type="submit" class="btn btn-primary">Save changes</button> --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
        {{-- Akhir Modal --}}
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function(){
        $('.status-toggle').on('change', function(){
            var userId = $(this).data('user');
            var isChecked = $(this).is(':checked');

            // send an ajax request to update status

            $.ajax({
                url: "{{ route('update.status.user') }}",
                method: "POST",
                data: {
                    user : userId,
                    is_checked: isChecked ? 1 : 0,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response){
                    toastr.success(response.message);
                },
                error: function(){

                }
            });

        });
    });
</script>

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

@endsection
