@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Data Jadwal Pelajaran
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
        <div class="breadcrumb-title pe-3">Jadwal Pelajaran</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Jadwal Pelajaran</li>
                </ol>
            </nav>
        </div>

    </div>
    <!--end breadcrumb-->
    <div class="mb-3">
        {{-- <a href="{{route('tambah.jadwal')}}" class="btn btn-primary">Tambah Jadwal</a> --}}
        <a href="" class="btn btn-primary" title="Print Pengguna Siswa" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bx bx-time"></i></a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5px;">No</th>
                            <th>Nama Guru</th>
                            <th>Hari</th>
                            <th>Mata Pelajaran</th>
                            <th>Rombel</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th style="width: 20px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwal as $key=> $item)
                        <tr>
                            <td>{{$key+1}}</td>

                            <td>{{$item['user']['name']}}</td>
                            <td>{{$item['hari']['nama_hari']}}</td>
                            <td>{{$item['mapel']['kode_mapel']}}</td>
                            <td>{{$item->rombel->nama_rombel}}</td>
                            <td>{{$item['waktu_mulai']['waktu_mulai']}}</td>
                            <td>{{$item['waktu_selesai']['waktu_selesai']}}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input large-chexbox status-toggle" type="checkbox" role="switch" id="flexSwitchCheckDefault1" data-jadwal="{{$item->id}}" {{$item->status ? 'checked' : ''}} >
                                    <label class="form-check-label" for="flexSwitchCheckDefault1"></label>
                                  </div>
                        </td>
                            <td>
                                <a href="{{route('edit.jadwal',$item->id)}}" class="btn btn-info" title="Edit"><i class="bx bx-edit"></i></a>
                               <a href="{{route('delete.jadwal',$item->id)}}" id="delete" class="btn btn-danger" id="delete" title="delete"><i class="bx bx-trash"></i></a>
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
                                    <h5 class="modal-title" id="exampleModalLabel">Tambah Jadwal Pelajaran</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <form id="myForm" method="post" action="{{route('simpan.jadwal')}}" enctype="multipart/form-data">
                                        @csrf
                                        {{-- <div class="mb-3 form-group">
                                            <label class="form-label">Nama Jurusan:</label>
                                            <input type="text" class="form-control" name="user_id">
                                        </div> --}}

                                        <div class="mb-3 form-group">
                                            <label class="form-label">Nama Guru:</label>
                                            <select name="user_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Guru</option>
                                                @foreach ($users as $item )
                                                <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{$item->name}}</option>
                                                @endforeach


                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label class="form-label">Nama Mapel:</label>
                                            <select name="mapel_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Mapel</option>
                                                @foreach ($mapel as $item )
                                                <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{$item->nama_mapel}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label class="form-label">Ruangan / Bengkel:</label>
                                            <select name="bengkel_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                                <option disabled data-select2-id="select2-data-2-747t">Pilih Bengkel</option>
                                                @foreach ($bengkel as $item )

                                                    <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{$item->nama_bengkel}}</option>

                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label class="form-label">Nama Rombel:</label>
                                            <select name="rombel_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Mapel</option>
                                                @foreach ($rombel as $rombels )

                                                    <option data-select2-id="select2-data-77-kb3z" value="{{$rombels->id}}">{{$rombels->nama_rombel}}</option>

                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label class="form-label">Nama Hari:</label>
                                            <select name="hari_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Hari</option>
                                                @foreach ($hari as $item )

                                                    <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{$item->nama_hari}}</option>

                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label class="form-label">Waktu Mulai:</label>
                                            <select name="mulai_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Mapel</option>
                                                @foreach ($waktu as $item )
                                                <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{' Jam ke- '.$item->nama. ' => ' .$item->waktu_mulai. ' s/d ' .$item->waktu_selesai}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label class="form-label">Waktu Selesai:</label>
                                            <select name="selesai_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Mapel</option>
                                                @foreach ($waktu as $item )
                                                <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{' Jam ke- '.$item->nama. ' => ' .$item->waktu_mulai. ' s/d ' .$item->waktu_selesai}}</option>
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
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function(){
        $('.status-toggle').on('change', function(){
            var jadwalId = $(this).data('jadwal');
            var isChecked = $(this).is(':checked');

            // send an ajax request to update status

            $.ajax({
                url: "{{ route('update.jadwal.status') }}",
                method: "POST",
                data: {
                    jadwal : jadwalId,
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
                category_name: {
                    required : true,
                },

                image: {
                    required : true,
                },

            },
            messages :{
                category_name: {
                    required : 'Please Enter Category Name',
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
