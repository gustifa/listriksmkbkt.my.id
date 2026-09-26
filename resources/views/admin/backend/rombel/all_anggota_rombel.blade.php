@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Anggota Rombongan Belajar
@endsection

<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Kehadiran</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Kehadiran</li>
                </ol>
            </nav>
        </div>

    </div>
    <!--end breadcrumb-->
    <div class="mb-3">
        <a href="{{route('tambah.kehadiran')}}" class="btn btn-primary"><i class="lni lni-plus"></i></a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5px;">No</th>
                            <th>Jurusan</th>
                            <th>Nama Rombel</th>
                            <th>Nama Peserta didik</th>
                            <th>Nama Wali Kelas</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($anggota_rombel as $key=> $item)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$item->rombel->jurusan->nama_jurusan}}</td>  
                            <td>{{$item->rombel->nama_rombel}}</td>  
                            <td>{{$item->peserta_didik->name}}</td>  
                            <td>{{$item->rombel->walas->name}}</td>  
                        </tr>
                        @endforeach


                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>

@endsection
