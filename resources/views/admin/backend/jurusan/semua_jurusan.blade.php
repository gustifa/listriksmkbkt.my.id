@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Data Jurusan
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
        <div class="breadcrumb-title pe-3">Jurusan</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Jurusan</li>
                </ol>
            </nav>
        </div>
        
    </div>
    <!--end breadcrumb-->
    <div class="mb-3">
        <a href="{{route('tambah.jurusan')}}" class="btn btn-primary"><i class="bx bx-plus"></i>Tambah Jurusan</a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5px;">No</th>
                            <th style="width: 70px;">Logo Jurusan</th>
                            <th>Program Keahlian</th>
                            <th>Konsentrasi Keahlian</th>
                            <th>Status</th>
                            <th style="width: 20px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jurusan as $key=> $item)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>
                                <!-- <img src="{{asset($item->logo_sekolah)}}" alt="" style="height: 70px" width="70px" > -->
                                <img src="{{(!empty($item->logo_jurusan)) ? url($item->logo_jurusan): url('upload/no_image.jpg')}}" alt="logo_jurusan" width="50" class="p-1 rounded-circle bg-primary">
                            </td>
                            <td>{{$item['proka']['nama_proka']}}</td>
                            <td>{{$item->nama_jurusan}} ({{$item->kode_jurusan}})</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input large-chexbox status-toggle" type="checkbox" role="switch" id="flexSwitchCheckDefault1" data-jurusan="{{$item->id}}" {{$item->status ? 'checked' : ''}} >
                                    <label class="form-check-label" for="flexSwitchCheckDefault1"></label>
                                </div>
                            </td>
                            <td>
                                <a href="{{route('edit.jurusan',$item->id)}}" class="btn btn-info" title="Edit"><i class="bx bx-edit"></i></a>
                                <!-- <a href="{{route('delete.category',$item->id)}}" id="delete" class="btn btn-danger" id="delete" title="delete"><i class="lni lni-trash"></i></a> -->
                            </td>
                        </tr>
                        @endforeach


                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>

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
            var jurusanId = $(this).data('jurusan');
            var isChecked = $(this).is(':checked');

            // send an ajax request to update status

            $.ajax({
                url: "{{ route('update.jurusan.status') }}",
                method: "POST",
                data: {
                    jurusan : jurusanId,
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
