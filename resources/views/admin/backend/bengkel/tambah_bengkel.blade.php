@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Tambah Bengkel
@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Bengkel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('all.category')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Bengkel</li>
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
                    <form id="myForm" method="post" action="{{route('simpan.bengkel')}}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group col-md-12">
                            <label for="input1" class="form-label">Program Keahlian </label>
                            <select name="proka_id" class="mb-3 form-select" aria-label="Default select example">
                                <option selected="" disabled>Pilih Program Keahlian</option>
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
                            <label class="form-label">Nama Bengkel:</label>
                            <input type="text" class="form-control" name="nama_bengkel">
                        </div>

                        <div class="mb-3 form-group">
                            <label class="form-label">Kode Bengkel:</label>
                            <input type="text" class="form-control" name="kode_bengkel">
                        </div>

                        <div class="mb-3 form-group">
                            <label class="form-label">Keterangan:</label>
                            <input type="text" class="form-control" name="keterangan">
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

<script type="text/javascript">
	$(document).ready(function(){
		$('#image').change(function(e){
			var reader = new FileReader();
			reader.onload = function(e){
				$('#showImage').attr('src',e.target.result);
			}
			reader.readAsDataURL(e.target.files['0']);
		})

	});

</script>
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
{{-- <script type="text/javascript">

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

</script> --}}
@endsection
