@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Data Rombongan Belajar
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Rombel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Rombel</li>
                </ol>
            </nav>
        </div>

    </div>
    <!--end breadcrumb-->
    <div class="mb-3">
        <a href="{{route('tambah.rombel')}}" class="btn btn-danger "><span class="bx bx-group"></span> Rombel </a>
        <a href="{{route('tambah.anggota.rombel')}}" class="btn btn-primary">Tambah anggota</a>
        <a href="{{route('all.anggota.rombel')}}" class="btn btn-primary">All anggota</a>
        <a href="" class="btn btn-warning">{{count($rombel)}}</a>
        <a href="" id="btn-create-post" class="btn btn-primary" title="Print Pengguna Siswa" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bx bx-layout"></i></a>
        {{-- <a href="{{route('cetak.semua.user')}}" class="btn btn-danger" title="Print Pengguna Siswa" target="_blank"><i class="lni lni-printer"></i></a> --}}
         <!-- Button trigger modal -->
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleFullScreenModal"><i class="bx bx-user"></i></button>
         <!-- Modal -->
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5px;">No</th>
                            <th>Nama Rombel</th>
                            <th>Nama Walas</th>
                            <th>Anggota Rombel</th>
                            <th style="width: 20px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rombel as $key=> $item)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$item->nama_rombel}}</td>
                            <td>{{$item['walas']['name']}}</td>
                            <td>
                                <a href="{{route('detail.rombel', $item->id)}}" class="btn btn-primary" title="Lihat Anggota Rombel"><i class="lni lni-eye"></i></a>
                                <a href="" id="delete" class="btn btn-warning" id="delete" title="delete"><i class="bx bx-user"></i></a>
                            </td>
                            <!-- <td><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleFullScreenModal"><i class="lni lni-user"></i></button></td> -->
                            <td>
                                <a href="{{route('edit.rombel',$item->id)}}" class="btn btn-info" title="Edit"><i class="bx bx-edit"></i></a>
                                {{-- <!-- <a href="{{route('delete.kelas',$item->id)}}" id="delete" class="btn btn-danger" id="delete" title="delete"><i class="lni lni-trash"></i></a> --> --}}
                            </td>
                        </tr>
                        @endforeach


                    </tbody>

                </table>

                        {{-- Awal Modal dasar --}}
                        <div class="col">
                            <!-- Button trigger modal -->
                            {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Import User</button> --}}

                            <!-- Modal -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Tambah Rombongan Belajar</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            <form id="myForm" method="post" action="{{route('simpan.rombel')}}" enctype="multipart/form-data">
                                                @csrf


                                                <div class="form-group col-md-12">
                                                    <label for="input1" class="form-label">Nama Program Keahlian </label>
                                                    <select name="proka_id" class="mb-3 form-select" aria-label="Default select example">
                                                        <option selected="" disabled>Nama Proka</option>
                                                        @foreach ($proka as $item )
                                                        <option value="{{$item->id}}">{{$item->nama_proka}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>

                                                <div class="form-group col-md-12">
                                                    <label for="input1" class="form-label">Nama Jurusan </label>
                                                    <select name="jurusan_id" class="mb-3 form-select" aria-label="Default select example">
                                                        <option> </option>

                                                    </select>
                                                </div>
                                                <div class="mb-3 form-group">
                                                    <label class="form-label">Nama Rombel:</label>
                                                    <input type="text" class="form-control" name="nama_rombel">
                                                </div>

                                                <div class="mb-3 form-group">
                                                    <label class="form-label">Wali Kelas:</label>
                                                    <select name="walas_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                                        <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Walas</option>
                                                        @foreach ($guru as $item )
                                                        <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{$item->name}}</option>
                                                        @endforeach


                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <button type="submit" class="px-5 btn btn-primary">Simpan</button>
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
                        {{-- Akhir Modal dasar --}}
                       @include('admin.backend.rombel.modal_full_tambah_anggota')
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function(){
        $('select[name="proka_id"]').on('change', function(){
            var proka_id = $(this).val();
            if (proka_id) {
                $.ajax({
                    url: "{{ url('/jurusan/ajax') }}/"+proka_id,
                    type: "GET",
                    dataType:"json",
                    success:function(data){
                        $('select[name="jurusan_id"]').html('');
                        var d =$('select[name="jurusan_id"]').empty();
                        $.each(data, function(key, value){
                            $('select[name="jurusan_id"]').append('<option value="'+ value.id + '">' + value.nama_jurusan + '</option>');
                        });
                    },
                });
            } else {
                alert('danger');
            }
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $('select[name="jurusan_id"]').on('change', function(){
            var jurusan_id = $(this).val();
            if (jurusan_id) {
                $.ajax({
                    url: "{{ url('/rombel/ajax') }}/"+jurusan_id,
                    type: "GET",
                    dataType:"json",
                    success:function(data){
                        $('select[name="rombel_id"]').html('');
                        var d =$('select[name="rombel_id"]').empty();
                        $.each(data, function(key, value){
                            $('select[name="rombel_id"]').append('<option value="'+ value.id + '">' + value.nama_rombel + '</option>');
                        });
                    },
                });
            } else {
                alert('danger');
            }
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function (){
        $('#myForm').validate({
            rules: {
                proka_id: {
                    required : true,
                },
                jurusan_id: {
                    required : true,
                },
                nama_rombel: {
                    required : true,
                },
                walas_id: {
                    required : true,
                },
                rombel_id: {
                    required : true,
                },
                siswa_id: {
                    required : true,
                },

                image: {
                    required : true,
                },

            },
            messages :{
                proka_id: {
                    required : 'Jurusan Tidak Boleh Kosong',
                },
                jurusan_id: {
                    required : 'Jurusan Tidak Boleh Kosong',
                },

                nama_rombel: {
                    required : 'Nama Rombel Tidak Boleh Kosong',
                },
                walas_id: {
                    required : 'Walas Tidak Boleh Kosong',
                },
                rombel_id: {
                    required : 'Walas Tidak Boleh Kosong',
                },
                siswa_id: {
                    required : 'Walas Tidak Boleh Kosong',
                },
                image: {
                    required : 'Please Upload Image',
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
