@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Data Proka
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
        <div class="breadcrumb-title pe-3">Kelas</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Kelas</li>
                </ol>
            </nav>
        </div>
        
    </div>
    <!--end breadcrumb-->
    <div class="mb-3">
        <a href="{{route('tambah.proka')}}" class="btn btn-primary"><i class="bx bx-plus"></i> Tambah Proka</a>
        <button type="button" class="btn btn-primary bx bx-plus" data-bs-toggle="modal" data-bs-target="#exampleModal"></button>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 3px;">No</th>
                            <th>Logo </th>
                            <th>Nama Proka</th>
                            <th>Ka. Proka</th>
                            <th>Status</th>
                            <th style="width: 20px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($proka as $key=> $item)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>
                                <!-- <img src="{{asset($item->logo_sekolah)}}" alt="" style="height: 70px" width="70px" > -->
                                <img src="{{(!empty($item->logo_proka)) ? url($item->logo_proka): url('upload/no_image.jpg')}}" alt="logo_proka" width="90" class="p-1 rounded-circle bg-primary">
                            </td>
                            
                            <td>{{$item->nama_proka}} ({{$item->kode_proka}})</td>
                            <td>{{$item['ka_proka']['name']}}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input large-chexbox status-toggle" type="checkbox" role="switch" id="flexSwitchCheckDefault1" data-proka="{{$item->id}}" {{$item->status ? 'checked' : ''}} >
                                    <label class="form-check-label" for="flexSwitchCheckDefault1"></label>
                                </div>
                            </td>
                            <td>
                                <a href="{{route('edit.proka',$item->id)}}" class="btn btn-info" title="Edit"><i class="bx bx-edit"></i></a>
                                <a href="{{route('delete.proka',$item->id)}}" id="delete" class="btn btn-danger" id="delete" title="delete"><i class="lni lni-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach


                    </tbody>

                </table>

                {{-- Awal Modal --}}
                <div class="col">
                    <!-- Button trigger modal -->
                    {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Import User</button> --}}
                   
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Tambah Proka</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <form id="myForm" method="post" action="{{route('simpan.proka')}}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3 form-group">
                                            <label class="form-label">Nama Jurusan:</label>
                                            <input type="text" class="form-control" name="nama_proka">
                                        </div>
                
                                        <div class="mb-3 form-group">
                                            <label class="form-label">Kode Jurusan:</label>
                                            <input type="text" class="form-control" name="kode_proka">
                                        </div>
                
                                        <div class="mb-3 form-group">
                                            <label class="form-label">Ka. Proka:</label>
                                            <select name="ka_proka_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                                <option disabled data-select2-id="select2-data-2-747t">Pilih Ka. Proka</option>
                                                @foreach ($user as $item )
                                                <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{$item->name}}</option>
                                                @endforeach
                
                
                                            </select>
                                        </div>
                
                                        <div class="mb-3 form-group">
                                            <label class="form-label">Input File:</label>
                                            <input type="file" class="form-control" name="logo_proka" id="image">
                
                                        </div>
                                        <div class="mb-3">
                                            <img id ="showImage"src="{{(!empty($profileData->photo)) ? url('upload/admin_images/'.$profileData->photo): url('upload/no_image.jpg')}}" alt="Admin" class="p-1 rounded-circle bg-primary" width="150">
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
                {{-- Akhir Modal --}}
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function (){
        $('#myForm').validate({
            rules: {
                nama_proka: {
                    required : true,
                },
                kode_proka: {
                    required : true,
                },

                user_id: {
                    required : true,
                },

                logo_proka: {
                    required : true,
                },

            },
            messages :{
                nama_proka: {
                    required : 'Nama Proka Tidak Boleh Kosong',
                },
                kode_proka: {
                    required : 'Kode Proka Tidak Boleh Kosong',
                },

                user_id: {
                    required : 'Proka Tidak Boleh Kosong',
                },

                logo_proka: {
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

    $(document).ready(function(){
        $('.status-toggle').on('change', function(){
            var prokaId = $(this).data('proka');
            var isChecked = $(this).is(':checked');

            // send an ajax request to update status

            $.ajax({
                url: "{{ route('update.proka.status') }}",
                method: "POST",
                data: {
                    proka : prokaId,
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

@endsection
