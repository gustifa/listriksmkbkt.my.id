@extends('admin.admin_dashboard')
@section('admin')
@section('title')
   Profile Sekolah
@endsection
<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Sekolah</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Sekolah</li>
                </ol>
            </nav>
        </div>

    </div>
    <!--end breadcrumb-->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5px;">No</th>
                            <th>Nama Sekolah</th>
                            <th>NPSN</th>
                            <th style="width: 70px;">Logo Sekolah</th>
                            <th style="width: 70px;">Logo Provinsi</th>
                            <th style="width: 20px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($profileSekolah as $key=> $item)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$item->nama}}</td>
                            <td>{{$item->npsn}}</td>
                            <td>
                                {{-- <!-- <img src="{{asset($item->logo_sekolah)}}" alt="" style="height: 70px" width="70px" > --> --}}
                                <img src="{{(!empty($item->logo_sekolah)) ? url($item->logo_sekolah): url('upload/no_image.jpg')}}" alt="logo_sekolah" width="110">
                              
                            </td>
                            <td>
                                
                                <img src="{{(!empty($item->logo_provinsi)) ? url($item->logo_provinsi): url('upload/no_image.jpg')}}" alt="logo_provinsi" width="110">
                            </td>
                            <td>
                                <a href="{{route('edit.profile.sekolah',$item->id)}}" class="btn btn-warning" title="Edit"><i class="bx bx-edit"></i></a>
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

@endsection
