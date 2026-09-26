@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Lihat User
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />	 --}}
<style>
   
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
   

    <div class="card">
        <div class="card-body">
             <form action="{{route('store.user.multi.select')}}" method="post" enctype="multipart/form-data" >
                 @csrf
                <div class="mb-12">
                 <label for="multiple-select-field" class="form-label">Basic multiple select</label>
                 <select class="tags form-select" 
                 id="tags" 
                 data-placeholder="Cari Nama Siswa"
                 name="tags[]" 
                 multiple="multiple">
                 </select>
                 </div>
                 <button type="submit">tes</button>
             </form>
         


        </div>
     </div>

    
</div>

<!-- Get User Baris Awal -->
<script type="text/javascript">
	$(document).ready(function(){
		$('.tags').select2({
            placeholder: 'Select',
            allowClear: true

        });

        $('#tags').select2({
            ajax:{
                url: "{{ route('get.user') }}",
                    type: "post",
                    dataType:'json',
                    delay:250,
                    data: function(params){
                        return{
                            name:params.term,
                            "_token":"{{ csrf_token() }}",
                        };
                    },

                    processResults:function(data){
                        return {
                            results: $.map(data, function(item){
                                return{
                                    id:item.id,
                                    text:item.name
                                }
                            })

                        };
                    },
            },
        });

	});

</script>
<!-- Get User Baris Akhir -->
@endsection