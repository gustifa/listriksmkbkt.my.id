@extends('admin.admin_dashboard')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
@section('title')
   Tambah Jadwal Pelajaran
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
                    <li class="breadcrumb-item active" aria-current="page">Tambah Kelas</li>
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
            </div>


        </div>
    </div>
    <!--end row-->
</div>

<script type="text/javascript">
    $(document).ready(function (){
        $('#myForm').validate({
            rules: {
                user_id: {
                    required : true,
                },
                mapel_id: {
                    required : true,
                },

                rombel_id: {
                    required : true,
                },

                hari_id: {
                    required : true,
                },

            },
            messages :{
                user_id: {
                    required : 'Nama Guru Tidak Boleh Kosong',
                },

                mapel_id: {
                    required : 'Mapel Tidak Boleh Kosong',
                },

                rombel_id: {
                    required : 'Rombel Tidak Boleh Kosong',
                },

                hari_id: {
                    required : 'Hari Tidak Boleh Kosong',
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
