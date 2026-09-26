@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Data Bengkel
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
        <div class="breadcrumb-title pe-3">Bengkel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Bengkel</li>
                </ol>
            </nav>
        </div>
        
    </div>
    <!--end breadcrumb-->
    {{-- <div class="mb-3">
        <a href="{{route('tambah.bengkel')}}" class="btn btn-primary">Tambah Bengkel</a>
    </div> --}}

    <div class="card-body">
        {{-- Awal --}}
        <div class="mb-3 form-group">
            <div class="col">
                <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                    <div class="btn-group" role="group">
                        {{-- <button type="button" class="btn btn-primary">Tambah</button> --}}
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Import</button>
                        <ul class="dropdown-menu" style="">
                            <li><a class="dropdown-item" href="{{route('tambah.bengkel')}}">Tambah</a>
                            </li>
                            <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModal" href="#">Import</a>
                            </li>
                            <li><a class="dropdown-item" href="{{route('cetak.bengkel')}}">Cetak</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            
            <div class="col">

                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Import Bengkel</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                        <form id="myForm" method="post" action="{{route('import.bengkel')}}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="mb-3 form-group">
                                                <label class="form-label">Input File: </label>
                                                <input type="file" class="form-control" name="file" id="file">

                                            </div>
                                            <div class="mb-3">
                                                <button type="submit" class="px-5 btn btn-primary">Import</button>
                                            </div>
                                            <div class="mb-3">
                                                <a href="{{route('download.template.bengkel')}}">Download Template</a>
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
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5px;">No</th>
                            <th>Nama Bengkel</th>
                            <th>Kode Bengkel</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th style="width: 30px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bengkel as $key=> $item)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$item->nama_bengkel}}</td>
                            <td>{{$item->kode_bengkel}}</td>
                            <td>{{$item->keterangan}}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input large-chexbox status-toggle" type="checkbox" role="switch" id="flexSwitchCheckDefault1" data-bengkel="{{$item->id}}" {{$item->status ? 'checked' : ''}} >
                                    <label class="form-check-label" for="flexSwitchCheckDefault1"></label>
                                </div>
                            </td>
                            <td>
                                <a href="{{route('cetak.per.bengkel',$item->id)}}" class="btn btn-info" title="Edit"><i class="lni lni-printer"></i></a>
                                <a href="{{route('edit.bengkel',$item->id)}}" class="btn btn-info" title="Edit"><i class="bx bx-edit"></i></a>
                                <a href="{{route('hapus.bengkel',$item->id)}}" id="delete" class="btn btn-danger" id="delete" title="delete"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach


                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function(){
        $('.status-toggle').on('change', function(){
            var bengkelId = $(this).data('bengkel');
            var isChecked = $(this).is(':checked');

            // send an ajax request to update status

            $.ajax({
                url: "{{ route('update.bengkel.status') }}",
                method: "POST",
                data: {
                    bengkel : bengkelId,
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
