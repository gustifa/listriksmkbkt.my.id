@extends('admin.admin_dashboard')
@section('admin')

@section('title')
   Edit Rombongan Belajar
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Rombel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('all.category')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Rombel</li>
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
                    <form id="myForm" method="post" action="{{route('update.rombel')}}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3 form-group">
                            <label class="form-label">Wali Kelas:</label>
                            <select name="walas_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Walas</option>
                                @foreach ($guru as $item )
                                <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 form-group">
                            <label class="form-label">Nama Jurusan:</label>
                            <select name="walas_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Walas</option>
                                @foreach ($jurusan as $item )
                                <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}" {{ $item->id == $rombel->id ? 'selected' : '' }}>{{$item->nama_jurusan}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 form-group">
                            <label class="form-label">Nama Rombel:</label>
                            <input type="text" name="kode_jurusan" class="form-control" id="input1" value="{{$rombel->nama_rombel}}">
                        </div>

                        <div class="mb-3 form-group">
                            <label class="form-label">Wali Kelas:</label>
                            <select name="walas_id" class="form-select select2-hidden-accessible" id="single-select-field" data-placeholder="Choose one thing" data-select2-id="select2-data-single-select-field" tabindex="-1" aria-hidden="true">
                                <option disabled data-select2-id="select2-data-2-747t">Pilih Nama Walas</option>
                                @foreach ($guru as $item )
                                <option data-select2-id="select2-data-77-kb3z" value="{{$item->id}}">{{$item->name}}</option>
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

@endsection