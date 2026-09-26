@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Anggota Rombongan Belajar
@endsection

<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Anggota Rombel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Rombongan Belajar </li>
                </ol>
            </nav>
        </div>

    </div>
    <!--end breadcrumb-->
    <div class="mb-3">
        <a href="{{route('semua.rombel')}}" class="btn btn-danger">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            
            <p class="btn btn-secondary">Wali Kelas: {{$wali_kelas->name}}</p>
            <div class="table-responsive">
                <table class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 5px;">No</th>
                            <th>Nama Peserta didik</th>
                            <th>Email</th>
                            <th>Aksi</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                    @if(count($anggota_rombel) > 0)
                        @foreach ($anggota_rombel as $key=> $item)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$item->peserta_didik->name}}</td>
                            <td>{{$item->peserta_didik->email}}</td>
                            <td>
                               <a href="{{route('hapus.anggota.rombel',$item->id)}}" id="delete" id="delete" title="delete"><i class="lni lni-trash"></i></a>
                            </td>  
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="6" class="text-center">Data tidak ada...</td>
                        </tr>
                    @endif

                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>

@endsection
