@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Data Semester
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
        <div class="breadcrumb-title pe-3">Semester</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Semester</li>
                </ol>
            </nav>
        </div>

    </div>
    <!--end breadcrumb-->
    <div class="mb-3">
        <a href="{{route('tambah.semester')}}" class="btn btn-primary"><i class="lni lni-plus"></i></a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" id="btn-create-post">TAMBAH</button>
        <!-- <a href="javascript:void(0)" class="btn btn-success mb-2" id="btn-create-post">TAMBAH</a> -->
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <!-- <th style="width: 5px;">No</th> -->
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th style="width: 20px;">Action</th>
                        </tr>
                    </thead>

                    <tbody id="table-semester">
                                @foreach($semester as $key=> $post)
                                <tr id="index_{{ $post->id }}">
                                    <!-- <td>{{$key+1}}</td> -->
                                    <td>{{ $post->nama }}</td>
                                    <td>{{ $post->keterangan }}</td>
                                    <td>{{ date('d-m-Y',strtotime($post->created_at)) }}</td>
                                    <td class="text-center">
                                        <a href="javascript:void(0)" id="btn-edit-post" data-id="{{ $post->id }}" class="btn btn-primary btn-sm">EDIT</a>
                                        <a href="{{route('delete.semester', $post->id)}}" id="btn-delete-post" data-id="{{ $post->id }}" class="btn btn-danger btn-sm">DELETE</a>
                                    </td>
                                    <td>{{ date('d-m-Y H:i:s',strtotime($post->created_at)) }}</td>
                                </tr>
                                @endforeach
                            </tbody>


                    

                </table>

                @include('admin.backend.semester.modal_tambah_semester')
            </div>
        </div>
    </div>

</div>



<script>
    $(document).ready(function(){
        $('.status-toggle').on('change', function(){
            var semesterId = $(this).data('semester');
            var isChecked = $(this).is(':checked');

            // send an ajax request to update status

            $.ajax({
                url: "{{ route('update.semester.status') }}",
                method: "POST",
                data: {
                    semester : semesterId,
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
